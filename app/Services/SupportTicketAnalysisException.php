<?php

namespace App\Services;

use RuntimeException;

class SupportTicketAnalysisException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $reason = 'invalid',
    ) {
        parent::__construct($message);
    }
}
