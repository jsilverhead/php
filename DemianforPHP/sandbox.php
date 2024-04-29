<?php

function evenNoEven(array $array, int $value)
{
	$result = array_filter($array, function ($item) use ($value) {
		return $item % $value != 0;
	});

	return array_values($result);
}

print_r(evenNoEven([22, 9, 11, 3, 44, 2], 2));