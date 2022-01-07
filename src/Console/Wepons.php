<?php

namespace Uniqoders\Game\Console;

class Wepons
{

    public $wepons;

    public function __contruct($wepons = []): void
    {
        $this->wepons = $wepons;
    }

    public function setWepons($wepons): array
    {
        return $this->wepons = $wepons;
    }

    public function getWepons(): array
    {
        return $this->wepons;
    }

    public function getWeponNameById($w): string
    {
        return $this->wepons[$w];
    }
}