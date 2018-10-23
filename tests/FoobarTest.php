<?php

declare(strict_types=1);

namespace Test;

use Test\Demo\Foo;
use Test\Demo\Bar;
use Automock\AutomockTestCase;

/**
 * @unit Test\Demo\Foobar
 */
class FoobarTest extends AutomockTestCase
{
    public function testThatGetFooReturnsFoo()
    {
        $this->foo->method('getValue')->willReturn(42);
        $this->assertEquals(84, $this->getFooTimesTwo());
    }

    public function testThatGetBarReturnsBar()
    {
        $this->assertInstanceOf(Bar::class, $this->getBar());
    }
}
