<?php
trait noNegative
{
	public function checkNehative($array)
	{
		return array_filter($array, function ($item) {
			return $item >= 0;
		});
	}
}

trait notEven
{
	public function checkEven($array)
	{
		return array_filter($array, function ($item) {
			return $item % 2 != 0;
		});
	}
}

class checkArray
{
	use noNegative, notEven;
}

$array = [0, 22, -1, 1, 31, 42, 99, 69, 77, -100];
$checkArray = new checkArray;

$array = $checkArray->checkNehative($array);
$array = $checkArray->checkEven($array);

print_r($array);