<?php

declare(strict_types=1);

namespace App\Domain\Orders\Exceptions;

use RuntimeException;

final class IdempotencyConflict extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Idempotency key đã được dùng với dữ liệu checkout khác.');
    }
}
