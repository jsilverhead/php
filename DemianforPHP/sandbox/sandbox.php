<?php

$array = [1, 3, 22, 99, 6, 42, 0, 70];

$getInfo = new Fiber(function () use ($array) {
    foreach ($array as $item) {
        if ($item == 42) {
            Fiber::suspend();
        } else {
            echo $item . PHP_EOL;
        }
    }
});

$getInfo->start();

echo 'Resuming Fiber:' . PHP_EOL;

$getInfo->resume();