<?php

declare(strict_types=1);

namespace Automock;

use Exception;

class AutomockPatternException extends AutomockException
{
    public function __construct($message, string $hint = "")
    {
        $message = "Pattern-enforcement - " . $message;
        parent::__construct($message, $hint);
    }
}
