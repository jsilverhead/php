<?php

function gen_one_to_three()
{
    for ($i = 1; $i <= 3; $i++) {
        // Обратите внимание, что $i сохраняет своё значение между вызовами.
        yield $i;
    }
}

$generator = gen_one_to_three();

foreach ($generator as $value) {
    echo $value;
}