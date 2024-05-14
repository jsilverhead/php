<?php

namespace rockets;

class Rocket
{
    protected int $countdown;
    protected string $type;

    public function __construct($countdown, $type)
    {
        $this->countdown = $countdown;
        $this->type = $type;
    }

    public function launch()
    {
        if ($this->type === 'nuclear') {

        } elseif ($this->type === 'solar') {

        } elseif ($this->type === 'gas') {

        } else {
            echo "We don't know how to launch this rocket.";
        }
    }
}