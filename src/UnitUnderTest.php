<?php

declare(strict_types=1);

namespace Automock;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Test;

class UnitUnderTest extends TestCase
{
    private $__automockProxyMethods = [];
    private static $__automockProxyCalls = [];
    private static $__automockUnitTestCase = null;
    private static $__automockUnitTest = null;
    private static $__automockUnit = null;

    public function __setAutomockUnitTest(Test $test)
    {
        self::$__automockUnitTestCase = $this;
        self::$__automockUnitTest = $test;
    }

    public function __defineAutomockProxyMethod($instance, $method)
    {
        self::$__automockUnit = $instance;
       $this->__automockProxyMethods[$method] = new ProxyMethod($instance, $method);
       self::$__automockProxyCalls[$method] = 0;
    }

    public function __call($method, $parameters) {
        if (isset($this->__automockProxyMethods[$method])) {
            $proxy = $this->__automockProxyMethods[$method];
            self::$__automockProxyCalls[$method] += 1;
            return $proxy->call($parameters);
        }
    }

    public function isCovered(): bool
    {
        return array_reduce($this->__automockProxyMethods, function ($covered, $proxy) {
            return $covered || $proxy->hasBeenCalled();
        }, false);
    }

    public static function setUpBeforeClass()
    {

    }

    public static function tearDownAfterClass()
    {
        $nonCovered = [];
        foreach(self::$__automockProxyCalls as $method => $call) {
            if ($call === 0) {
                $nonCovered[] = $method;
            }
        }
        if (count($nonCovered) > 0) {
            self::$__automockUnitTest->fail(
                sprintf(
                    "\r\nAutomock: The Test '%s' for Unit '%s' does not cover method(s): " . implode(', ', $nonCovered) . "\r\n",
                    get_class(self::$__automockUnitTest),
                    get_class(self::$__automockUnit)
                )
            );
        }
    }
}
