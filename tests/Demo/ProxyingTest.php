<?php

declare(strict_types=1);

namespace Test;

use Demo\ValueObject\Foo;
use Demo\ValueObject\Bar;
use Automock\AutomockTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @unit Demo\UseCase\Foobar
 */
class ProxyingTest extends AutomockTestCase
{
    public function testPropertyProxying()
    {
        $this->assertInstanceOf(Foo::class, $this->foo);
        $this->assertInstanceOf(Bar::class, $this->bar);
    }

    public function testPropertyProxysAreMocked()
    {
        $this->assertInstanceOf(MockObject::class, $this->foo);
        $this->assertInstanceOf(MockObject::class, $this->bar);
    }

    public function testMethodProxying()
    {
        $this->assertInstanceOf(Foo::class, $this->getFoo());
        $this->assertInstanceOf(Bar::class, $this->getBar());
    }
}
