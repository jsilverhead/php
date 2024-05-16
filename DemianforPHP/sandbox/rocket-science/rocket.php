<?php

namespace rockets\rocket;

class Rocket
{
	protected string $engine;
	protected int $mass;
	protected int $fuelMass;

	protected int $impulse;

	public function __construct(string $engine, int $mass, int $fuelMass)
	{
		$this->engine = $engine;
		$this->mass = $mass;
		$this->fuelMass = $fuelMass;
	}
}

// nuclear 70kn = 7000n = 713801.347kg

// chemical

// electrical