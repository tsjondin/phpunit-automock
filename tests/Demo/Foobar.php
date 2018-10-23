<?php

declare(strict_types=1);

namespace Test\Demo;

class Foobar
{
    private $foo;
    private $bar;

    public function __construct(
        Foo $foo,
        Bar $bar
    ) {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function getFooTimesTwo()
    {
        return $this->foo->getValue() * 2;
    }

    public function getBar()
    {
        return $this->bar;
    }
}
