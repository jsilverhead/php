<?php

class Sandbox
{
	private int $id = 42;

	public function __get($name)
	{
		echo "Getting $name: $this->id \n";
	}
	public function __set($name, $value)
	{
		echo "Setting $name: ";
	}
}

$example = new Sandbox;

echo $example->id;

echo $example->id = 0;