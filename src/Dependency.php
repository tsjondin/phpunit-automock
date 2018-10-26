<?php

declare(strict_types=1);

namespace Automock;

use PHPUnit\Framework\MockObject\MockObject;

class Dependency
{
    private $name;
    private $type;
    private $mock;

    public function __construct(string $name, string $type, MockObject $mock)
    {
        $this->name = $name;
        $this->type = $type;
        $this->mock = $mock;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMock(): MockObject
    {
        return $this->mock;
    }
}
