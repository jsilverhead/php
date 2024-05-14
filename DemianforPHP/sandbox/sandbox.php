<?php

class Sandbox
{
    public string $name = 'John';
    private string $fullname = 'Week';
    protected int $age = 40;

    public function __debugInfo()
    {
        echo "what do you wanna know? It's protected.";
    }
}

$sandbox = new Sandbox;
var_dump($sandbox);