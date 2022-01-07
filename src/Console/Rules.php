<?php

namespace Uniqoders\Game\Console;

class Rules
{

    public $rules;

    public function __contruct($rules = []): void
    {
        $this->rules = $rules;
    }

    public function setRules($rules): void
    {
        $this->rules = $rules;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}