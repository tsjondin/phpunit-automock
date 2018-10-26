<?php

declare(strict_types=1);

namespace Test\Automock;

use Automock\UnitUnderTest;
use Test\Demo\UseCase\Foobar;
use Test\Demo\ValueObject\Foo;
use Test\Demo\ValueObject\Bar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Error\Notice;

/**
 * @unit Test\Demo\UseCase\Foobar
 */
class ProxyingTest extends UnitUnderTest
{
    /**
     * Automock will proxy all properties on the Unit that are dependencies of the Unit.
     */
    public function testPropertyProxying()
    {
        $this->assertInstanceOf(Foo::class, $this->foo);
        $this->assertInstanceOf(Bar::class, $this->bar);
    }

    /**
     * All proxied properties are MockObject's
     *
     * @depends testPropertyProxying
     */
    public function testPropertyProxysAreMocks()
    {
        $this->assertInstanceOf(MockObject::class, $this->foo);
        $this->assertInstanceOf(MockObject::class, $this->bar);
    }

    /**
     * The Unit has a property named someRandomNameForNonDependency, but it does not reflect an injected dependency and
     * is therefor not proxied into the Unit test.
     *
     * @depends testPropertyProxying
     */
    public function testPropertyProxyingNonDependencyPropertiesAreNotProxied()
    {
        $this->expectException(Notice::class);
        $this->expectExceptionMessage("Undefined property: Test\Automock\ProxyingTest::\$someRandomNameForNonDependency");
        $this->someRandomNameForNonDependency;
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
        $this->assertEquals($this->foo, $this->getFoo());
        $this->assertEquals($this->bar, $this->getBar());
    }
}
