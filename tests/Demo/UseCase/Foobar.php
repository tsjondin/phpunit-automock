<?php

declare(strict_types=1);

namespace Test\Demo\UseCase;

use Test\Demo\ValueObject\Foo;
use Test\Demo\ValueObject\Bar;

class Foobar
{
    private $foo;
    private $bar;
    private $someRandomNameForNonDependency;

    public function __construct(
        Foo $foo,
        Bar $bar
    ) {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->someRandomNameForNonDependency = 1;
    }

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }
}
