<?php

declare(strict_types=1);

namespace Automock;

class ProxyMethod
{
    private $isCalled = false;
    private $instance;
    private $method;

    public function __construct($instance, $method)
    {
        $this->instance = $instance;
        $this->method = $method;
    }

    public function call($parameters)
    {
        $this->isCalled = true;
        return call_user_func_array(array($this->instance, $this->method), $parameters);
    }

    public function hasBeenCalled(): bool
    {
        return $this->isCalled;
    }
}
