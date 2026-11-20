<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf', $token);
        }
        return $token;
    }

    public static function assert(?Request $request = null): void
    {
        $request = $request ?? new Request();
        $provided = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if ($provided === '') {
            $json = $request->json();
            $provided = (string)($_POST['_csrf'] ?? $json['_csrf'] ?? '');
        }
        $expected = Session::get('_csrf');
        if (!is_string($expected) || $expected === '' || !hash_equals($expected, $provided)) {
            throw new HttpException(419, 'CSRF token mismatch');
        }
    }
}
