<?php

declare(strict_types=1);

namespace Automock;

use Exception;

class AutomockException extends Exception
{
    private $hint;

    public function __construct (string $message, string $hint = "")
    {
        parent::__construct($message);
        $this->hint = $hint;
    }

    public function getHint(): string
    {
        if (strlen($this->hint) > 0) {
            return "Hint: " . $this->hint;
        }
        return '';
    }
}
