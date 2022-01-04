<?php

namespace Uniqoders\Game\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class GameCommand extends Command
{
    private $players = [];
    private $player_name;
    private $theBigBangTheory = false;
    // Weapons available
    private $weapons = [
            0 => 'Tijeras',
            1 => 'Piedra',
            2 => 'Papel'
    ];
    // Rules to win
    private $rules = [
        0 => 2,
        1 => 0,
        2 => 1
    ];
    //Config
    private $round = 1;
    private $max_round = 5;
    private $maxWins = 3;

    /**
     * Configure the command options.
     *
     * @return void
     */

    protected function configure(): void
    {
        $this->setName('game')
            ->setDescription('New game: you vs computer')
            ->addArgument('name', InputArgument::OPTIONAL, 'what is your name?', 'Player 1');
    }

    /**
     * Execute the console game.
     *
     * @return integer
     */

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write(PHP_EOL . 'Made with ♥ by Uniqoders.' . PHP_EOL . PHP_EOL);
        $output->write(PHP_EOL . 'Modified by Fernando Aponte 04/01/2022.' . PHP_EOL . PHP_EOL . PHP_EOL);

        $this->player_name = $input->getArgument('name');

        $this->players = [
            'player' => [
                'name' => $this->player_name,
                'stats' => [
                    'draw' => 0,
                    'victory' => 0,
                    'defeat' => 0,
                ]
            ],
            'computer' => [
                'name' => 'Computer',
                'stats' => [
                    'draw' => 0,
                    'victory' => 0,
                    'defeat' => 0,
                ]
            ]
        ];

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Te gustaria jugar con las reglas "The Big Bang Theory"? y/n '. PHP_EOL, false, '/^(y|j)/i');
        
        if ($helper->ask($input, $output, $question)) {
            $this->theBigBangTheory = true;
            array_push($this->weapons, 'Lagarto', 'Spock');
            $this->rules = [
                0 => [ 0 => 2 ],
                1 => [ 0 => 3 ],
                2 => [ 1 => 0 ],
                3 => [ 1 => 3 ],
                4 => [ 2 => 1 ],
                5 => [ 2 => 4 ],
                6 => [ 3 => 2 ],
                7 => [ 3 => 4 ],
                8 => [ 4 => 0 ],
                9 => [ 4 => 1 ],
            ];
        }

        $question = new ConfirmationQuestion('Te gustaria cambiar la cantidad maxima de rondas? y/n '. PHP_EOL, false, '/^(y|j)/i');
        $ask = $this->getHelper('question');

        do {
            // User selection
            $question = new ChoiceQuestion('Please select your weapon', array_values($this->weapons), 1);
            $question->setErrorMessage('Weapon %s is invalid.');

            $user_weapon = $ask->ask($input, $output, $question);
            $output->writeln('Genial! Has seleccionado: ' . $user_weapon);
            $user_weapon = array_search($user_weapon, $this->weapons);

            // Computer selection
            $computer_weapon = array_rand($this->weapons);

            $output->writeln('Tu oponente a selecionado: ' . $this->weapons[$computer_weapon]);
            $output->writeln('Round: ' .$this->round);
            if(!$this->theBigBangTheory){
                // Result with default rules
                $this->setResult($user_weapon, $computer_weapon, $output);
            }else{
                // Result with theBigBangTheory rules
                $this->setTBBTResult($user_weapon, $computer_weapon, $output);
            }

            if($this->players['player']['stats']['victory'] == $this->maxWins || $this->players['computer']['stats']['victory'] == $this->maxWins){
                break;
            }else{
                $this->round++;
            }

        } while ($this->round <= $this->max_round);

        // Display stats
        $stats = $this->players;

        $stats = array_map(function ($player) {
            return [$player['name'], $player['stats']['victory'], $player['stats']['draw'], $player['stats']['defeat']];
        }, $stats);

        $table = new Table($output);
        $table
            ->setHeaders(['Jugador', 'Rondas ganadas', 'Rondas empatadas', 'Rondas perdidas'])
            ->setRows($stats);

        $table->render();

        return 0;
    }

    /**
     * Set Results with the normal rules.
     * @param integer $user_weapon The selected user weapon
     * @param integer $computer_weapon The selected computer weapon
     * @param object  $output The OutputInterface
     * @return void
     */

    protected function setResult($user_weapon, $computer_weapon, $output){

        if ($this->rules[$user_weapon] === $computer_weapon) {
            $this->players['player']['stats']['victory']++;
            $this->players['computer']['stats']['defeat']++;

            $output->writeln($this->player_name . ' win!');
        } else if ($this->rules[$computer_weapon] === $user_weapon) {
            $this->players['player']['stats']['defeat']++;
            $this->players['computer']['stats']['victory']++;

            $output->writeln('Computer win!');
        } else {
            $this->players['player']['stats']['draw']++;
            $this->players['computer']['stats']['draw']++;

            $output->writeln('Draw!');
        }
    }

    /**
     * Set Results with the big bang theory rules.
     * @param integer $user_weapon The selected user weapon
     * @param integer $computer_weapon The selected computer weapon
     * @param object  $output The OutputInterface
     * @return void
     */

    protected function setTBBTResult($user_weapon, $computer_weapon, $output){
        $empate;
        foreach ($this->rules as $key => $val) {
            $empate = false;
            if (isset($val[$user_weapon]) && $val[$user_weapon] === $computer_weapon) {
                $this->players['player']['stats']['victory']++;
                $this->players['computer']['stats']['defeat']++;

                $output->writeln($this->player_name . ' win!');
                break;
            } else if (isset($val[$computer_weapon]) && $val[$computer_weapon] === $user_weapon) {
                $this->players['player']['stats']['defeat']++;
                $this->players['computer']['stats']['victory']++;

                $output->writeln('Computer win!');
                break;
            } else {
                $empate = true;
            }
        }
        if($empate){
            $this->players['player']['stats']['draw']++;
            $this->players['computer']['stats']['draw']++;

            $output->writeln('Draw!');
        }
    }

}
