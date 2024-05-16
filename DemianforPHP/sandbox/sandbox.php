<?php

enum Suit: string
{
    case Hearts = 'Hearts';
    case Diamonds = 'Diamons';
    case Clubs = 'Clubs';
    case Spades = 'Spades';
}

function Sandbox($arg)
{
    return match ($arg) {
        Suit::Diamonds->value => 'Брильянт',
        Suit::Spades->value => 'Пика точёная',
        Suit::Clubs->value => 'Дубинка же',
        Suit::Hearts->value => 'Лайк'
    };
}

echo Sandbox('Spades');