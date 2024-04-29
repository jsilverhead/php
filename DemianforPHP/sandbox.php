<?php

abstract class Sandbox
{
	public int $id;

	abstract function getId();
}

class box extends Sandbox
{
	public function getId()
	{
		return $this->id;
	}
}

$box1 = new box;
$box1->id = 22;
echo $box1->getId();