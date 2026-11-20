<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function __construct(
        private string $body = '',
        private int $status = 200,
        private array $headers = []
    ) {
    }

    public static function html(string $body, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = $headers['Content-Type'] ?? 'text/html; charset=UTF-8';
        return new self($body, $status, $headers);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=UTF-8';
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return new self($json === false ? '{"ok":false}' : $json, $status, $headers);
    }

    public static function redirect(string $to, int $status = 302): self
    {
        return new self('', $status, ['Location' => $to]);
    }

    public static function empty(int $status = 204): self
    {
        return new self('', $status);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
