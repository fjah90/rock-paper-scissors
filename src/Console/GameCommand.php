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

        $question_theBigBangTheory = $this->customConfrimQuestion('Te gustaria jugar con las reglas "The Big Bang Theory"? y/n ', $input, $output);

        if ($question_theBigBangTheory) {
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

        $question_upMaxRound = $this->customConfrimQuestion('Te gustaria cambiar la cantidad maxima de rondas? y/n ', $input, $output);

        if ($question_upMaxRound)
            $this->max_round = 10;

        do {
            // User selection
            $user_weapon = $this->selectUserWepond($input, $output);

            // Computer selection
            $computer_weapon = $this->selectComputerWepond($output);

            $output->writeln('Round: ' .$this->round);

            if(!$this->theBigBangTheory){
                $this->setResult($user_weapon, $computer_weapon, $output);
            }else{
                $this->setTBBTResult($user_weapon, $computer_weapon, $output);
            }

            if($this->players['player']['stats']['victory'] === $this->maxWins || $this->players['computer']['stats']['victory'] === $this->maxWins)
                break;

            $this->round++;

        } while ($this->round <= $this->max_round);

        // Display stats
        $this->displayStats($output)->render();

        return 0;
    }

    /**
     * Set Results with the normal rules.
     * @param integer $user_weapon The selected user weapon
     * @param integer $computer_weapon The selected computer weapon
     * @param object  $output The OutputInterface
     * @return void
     */

    protected function setResult($user_weapon, $computer_weapon, $output): void
    {

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

    protected function setTBBTResult($user_weapon, $computer_weapon, $output): void
    {
        $draw;
        foreach ($this->rules as $key => $val) {
            $draw = false;
            if (isset($val[$user_weapon]) && $val[$user_weapon] === $computer_weapon){
                    $this->players['player']['stats']['victory']++;
                    $this->players['computer']['stats']['defeat']++;

                    $output->writeln($this->player_name . ' win!');
                    break;
            }else if(isset($val[$computer_weapon]) && $val[$computer_weapon] === $user_weapon){

                $this->players['player']['stats']['defeat']++;
                $this->players['computer']['stats']['victory']++;

                $output->writeln('Computer win!');
                break;
            }else{
                $draw = true;
            }
        }
        if($draw){
            $this->players['player']['stats']['draw']++;
            $this->players['computer']['stats']['draw']++;

            $output->writeln('Draw!');
        }
    }

    /**
     * Prompt to answer a closed answer.
     * @param string $question The selected computer weapon
     * @param InputInterface $input InputInterface
     * @param OutputInterface $output OutputInterface
     * @return bool
     */

    public function customConfrimQuestion($question, $input, $output): bool
    {
        $h = $this->getHelper('question');
        $q = new ConfirmationQuestion($question. PHP_EOL, false, '/^(y|j)/i');

        return $h->ask($input, $output, $q);
    }

    /**
     * Prompt to select a weapon.
     * @param InputInterface $input InputInterface
     * @param OutputInterface $output OutputInterface
     * @return integer The selected weapon
     */

    public function selectUserWepond($input, $output): int
    {
        $ask = $this->getHelper('question');
        $question = new ChoiceQuestion('Please select your weapon', array_values($this->weapons), 1);
        $question->setErrorMessage('Weapon %s is invalid.');

        $w = $ask->ask($input, $output, $question);
        $output->writeln('Genial! Has seleccionado: ' . $w);

        $uw = array_search($w, $this->weapons);

        return (int)$uw;
    }

    /**
     * Randon Selected Wepond.
     * @param OutputInterface $output OutputInterface
     * @return integer The selected weapon
     */

    public function selectComputerWepond($output): int
    {

        $w = array_rand($this->weapons);

        $output->writeln('Tu oponente a selecionado: ' . $this->weapons[$w]);

        return (int)$w;
    }

    /**
     * Display Final result stats.
     * @param OutputInterface $output OutputInterface
     * @return object Table to Render
     */

    public function displayStats($output): object
    {
        $s = $this->players;

        $s = array_map(function ($p) {
            return [$p['name'], $p['stats']['victory'], $p['stats']['draw'], $p['stats']['defeat']];
        }, $s);

        $t = new Table($output);
        $t
            ->setHeaders(['Jugador', 'Rondas ganadas', 'Rondas empatadas', 'Rondas perdidas'])
            ->setRows($s);

        return $t;
    }

}
