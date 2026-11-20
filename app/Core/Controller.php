<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], int $status = 200): Response
    {
        $file = project_root() . '/views/' . ltrim($template, '/') . '.php';
        if (!is_file($file)) {
            throw new HttpException(500, 'View not found: ' . $template);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $html = (string)ob_get_clean();
        return Response::html($html, $status);
    }

    protected function page(string $template, array $data = [], string $layout = 'layouts/site', int $status = 200): Response
    {
        $data['contentView'] = $template;
        return $this->view($layout, $data, $status);
    }

    protected function adminPage(string $template, array $data = [], int $status = 200): Response
    {
        $data['contentView'] = $template;
        return $this->view('layouts/admin', $data, $status);
    }

    protected function redirect(string $path, int $status = 302): Response
    {
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            $path = url($path);
        }
        return Response::redirect($path, $status);
    }

    protected function back(string $fallback = '/'): Response
    {
        $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
        if ($ref !== '') {
            return Response::redirect($ref);
        }
        return $this->redirect($fallback);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }
}
