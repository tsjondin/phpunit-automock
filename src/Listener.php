<?php

namespace Automock;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use Automock\MockInjector;

class Listener implements TestListener
{
    use TestListenerDefaultImplementation;

    private $injector;

    public function startTest(Test $test): void
    {
        $this->injector = new MockInjector($test);
    }

    public function endTest(Test $test, float $time): void
    {
        $this->injector->resolve($test);
    }
}
