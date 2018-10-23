<?php

declare(strict_types=1);

namespace Demo\UseCase;

use Demo\ValueObject\Foo;
use Demo\ValueObject\Bar;

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

    public function getFoo()
    {
        return $this->foo;
    }

    public function getBar()
    {
        return $this->bar;
    }
}
