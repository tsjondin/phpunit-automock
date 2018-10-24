<?php

declare(strict_types=1);

namespace Automock;

use PHPUnit\Framework\MockObject\MockObject;

class Dependency
{
    private $type;
    private $mock;

    public function __construct(string $type, MockObject $mock)
    {
        $this->type = $type;
        $this->mock = $mock;
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
