<?php

namespace Uniqoders\Tests\Integration\Console;

use Symfony\Component\Console\Tester\CommandTester;
use Uniqoders\Game\Console\GameCommand;
use Uniqoders\Tests\Integration\IntegrationTestCase;

class GameCommandTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->application->add(new GameCommand());
    }

    public function test_game_command(): void
    {
        /**
         *******************
         * TODO Make tests *
         *******************
         */
        $command = $this->application->find('game');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName("@TEST1"),
            //Init the test with The Big Bang Theory rules
            //(can modifid change the first position in the array and set "y" to Yes and "n" to No, the secod "y" is to hitting ENTER)
            $commandTester->setInputs(array('y','y')),
        ]);

        $output = $commandTester->getDisplay();
        $this->assertSame([], $output);
    }
}
