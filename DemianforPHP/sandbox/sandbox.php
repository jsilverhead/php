<?php

function getNumbers()
{
    yield 1;
    yield from [2, 3];
    yield from fourfive();
    yield from new ArrayIterator([6, 7]);
    yield 8;
    return yield from nineten();
}

function fourfive()
{
    yield 4;
    yield from five();
}

function five()
{
    yield 5;
}

function nineten()
{
    yield 9;
    return 10;
}

$generator = getNumbers();
foreach ($generator as $num) {
    echo "$num "; // выдаст 1 2 3 4 5 6 7 8 9
}
echo $generator->getReturn(); // вернёт последний return 10;