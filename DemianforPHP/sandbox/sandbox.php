<?php

class Sandbox
{
    public function example($arg = 5)
    {
        return "$arg sample";
    }

}

class box extends Sandbox
{
    public function example($arg)
    {
        return 'no sample';
    }
}