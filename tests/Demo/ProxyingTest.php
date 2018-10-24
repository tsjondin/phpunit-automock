<?php

declare(strict_types=1);

namespace Test;

use Automock\UnitUnderTest;
use Demo\UseCase\Foobar;
use Demo\ValueObject\Foo;
use Demo\ValueObject\Bar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Error\Notice;

/**
 * @unit Demo\UseCase\Foobar
 */
class ProxyingTest extends UnitUnderTest
{
    public function testPropertyProxying()
    {
        $this->assertInstanceOf(Foo::class, $this->someRandomNameForFoo);
        $this->assertInstanceOf(Bar::class, $this->someRandomNameForBar);
    }

    /**
     * @depends testPropertyProxying
     */
    public function testPropertyProxyingNonDependencyPropertiesAreNotProxied()
    {
        $this->expectException(Notice::class);
        $this->expectExceptionMessage("Undefined property: Test\ProxyingTest::\$someRandomNameForNonDependency");
        $this->assertEquals(1, $this->someRandomNameForNonDependency);
    }

    /**
     * @depends testPropertyProxying
     */
    public function testPropertyProxysAreMocks()
    {
        $this->assertInstanceOf(MockObject::class, $this->someRandomNameForFoo);
        $this->assertInstanceOf(MockObject::class, $this->someRandomNameForBar);
    }

    public function testMethodProxying()
    {
        $this->assertInstanceOf(Foo::class, $this->getFoo());
        $this->assertInstanceOf(Bar::class, $this->getBar());
    }

    /**
     * @depends testPropertyProxying
     * @depends testMethodProxying
     */
    public function testPropertiesProxiedReferenceSameObjectAsUnitUses()
    {
        $this->assertEquals($this->someRandomNameForFoo, $this->getFoo());
        $this->assertEquals($this->someRandomNameForBar, $this->getBar());
    }
}
