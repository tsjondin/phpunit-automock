<?php

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use Automock\AutomockListener;

class MyAutomockListener implements TestListener
{
    use TestListenerDefaultImplementation;

    public function startTest(Test $test): void
    {
        new AutomockListener($test);
    }
}
