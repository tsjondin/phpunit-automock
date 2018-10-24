<?php

declare(strict_types=1);

namespace Automock;

use PHPUnit\Framework\TestCase;

class UnitUnderTest extends TestCase
{

    private $amMethods = [];

    public function __defineAMMethod($classUnderTest, $method)
    {
       $this->amMethods[$method] = $classUnderTest;
    }

    public function __call($name, $parameters) {
        if (isset($this->amMethods[$name])) {
            return call_user_func_array(array($this->amMethods[$name], $name), $parameters);
        }
    }
}
