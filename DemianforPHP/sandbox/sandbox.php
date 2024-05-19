<?php

trait Sandbox
{
    public function expressYourself()
    {
        return "I'm a card";
    }
}
enum Suit
{
    use Sandbox;

    case Hearts;
    case Diamonds;
    case Clubs;
    case Spades;
}

$val = Suit::Hearts;

echo $val->expressYourself();