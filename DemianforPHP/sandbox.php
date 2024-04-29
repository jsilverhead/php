<?php

$arr = [2, 3, 7, 9];

function addFive($item)
{
	return $item + 5;
}

$result = array_map('addFive', $arr);

print_r($result);