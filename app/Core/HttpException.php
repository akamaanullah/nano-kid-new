<?php

declare(strict_types=1);

namespace App\Core;

final class HttpException extends \RuntimeException
{
    public function __construct(
        private int $status,
        string $message = '',
        private ?string $redirect = null
    ) {
        parent::__construct($message, $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function redirect(): ?string
    {
        return $this->redirect;
    }
}
