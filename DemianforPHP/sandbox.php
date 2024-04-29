<?php

$arr = [2, 3, 7, 9];

$result = array_map(function ($item) {
	return $item + 5;
}, $arr);

print_r($result);