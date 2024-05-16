<?php

namespace rockets\launch;

use rockets\rocket\Rocket;

require __DIR__ . 'rocket.php';

final class Launch
{
    public const GRAVITY = 9.80665;

    private int $countDown = 10;

    private int $finalSpeed;

    public function launch(Rocket $rocket)
    {
        $this->finalSpeed = $rocket->impulse * log($rocket->mass / ($rocket->mass - $rocket->fuelMass));
    }
}