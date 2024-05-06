<?php

function countNums($a, $b)
{
    return $a + $b;
}

echo countNums(...[2, 5, 9]) . "\n"; // Пересчитает 2, 5

$getSum = [8, 8];
echo countNums(...$getSum) . "\n"; // Пересчитает 8 и 8