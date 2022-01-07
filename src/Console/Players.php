<?php

namespace Uniqoders\Game\Console;

class Players
{
    public $players;

    public function __contruct($players  = []): void
    {
        $playerlayers;
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function setPlayers($players):array
    {
        return $this->players = $players;
    }

    public function getVictory($id): int
    {
        return $this->players[$id]['stats']['victory'];
    }

    public function setVictory($id): void
    {
        $this->players[$id]['stats']['victory']++;
    }

    public function getDefeat($id): int
    {
        return $this->players[$id]['stats']['defeat'];
    }

    public function setDefeat($id): void
    {
        $this->players[$id]['stats']['defeat']++;
    }

    public function setDraw(): void
    {
        foreach ($this->players as $id => $player ) {
            $this->players[$id]['stats']['draw']++;
        }
    }

    public function setDrawById($id): void
    {
        $this->players[$id]['stats']['draw']++;
    }

    public function getDraw($id): int
    {
        return $this->players[$id]['stats']['draw'];
    }

}