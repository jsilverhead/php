<?php

class Sandbox
{


	public function __invoke($data)
	{
		return $data + 1;
	}
}

$sandbox = new Sandbox;

echo $sandbox(5);