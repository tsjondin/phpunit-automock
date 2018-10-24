<?php

declare(strict_types=1);

namespace Demo\UseCase;

use Demo\ValueObject\Foo;
use Demo\ValueObject\Bar;

class Foobar
{
    private $someRandomNameForFoo;
    private $someRandomNameForBar;
    private $someRandomNameForNonDependency;

    public function __construct(
        Foo $foo,
        Bar $bar
    ) {
        $this->someRandomNameForFoo = $foo;
        $this->someRandomNameForBar = $bar;
        $this->someRandomNameForNonDependency = 1;
    }

    public function getFoo()
    {
        return $this->someRandomNameForFoo;
    }

    public function getBar()
    {
        return $this->someRandomNameForBar;
    }
}
