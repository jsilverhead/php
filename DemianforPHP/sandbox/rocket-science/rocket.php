<?php

namespace rockets\rocket;

class Rocket
{
	protected string $engine;
	protected int $mass;
	protected int $fuelMass;

	protected int $impulse;

	public function __construct($countdown, $type)
	{
		$this->countdown = $countdown;
		$this->type = $type;
	}
}

// nuclear 70kn = 7000n = 713801.347kg

// chemical

// electrical