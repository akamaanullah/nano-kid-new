<?php

declare(strict_types=1);

namespace App\Core;

final class Kernel
{
    private static ?string $basePath = null;

    public static function basePath(): string
    {
        if (self::$basePath !== null) {
            return self::$basePath;
        }
        $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $dir = rtrim(dirname($script), '/');
        if (str_ends_with($dir, '/public')) {
            $dir = substr($dir, 0, -7);
        }
        if ($dir === '/' || $dir === '.' || $dir === '\\' || $dir === '') {
            self::$basePath = '';
        } else {
            self::$basePath = $dir;
        }
        return self::$basePath;
    }

    public static function handle(): Response
    {
        if (config('app.force_https') && !nk_request_is_https() && PHP_SAPI !== 'cli') {
            $host = (string)($_SERVER['HTTP_HOST'] ?? '');
            $uri = (string)($_SERVER['REQUEST_URI'] ?? '/');
            if ($host !== '') {
                return Response::redirect('https://' . $host . $uri, 301);
            }
        }

        Session::start();
        Csrf::token();
        self::loadAuthUser();

        $router = new Router();
        $routes = project_root() . '/app/routes.php';
        if (is_file($routes)) {
            require $routes;
        }

        $request = new Request();
        try {
            return $router->dispatch($request);
        } catch (HttpException $e) {
            return self::httpException($e, $request);
        } catch (\Throwable $e) {
            app_log('unhandled', ['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
            if ($request->wantsJson()) {
                $payload = ['ok' => false, 'error' => 'Server error'];
                if (nk_is_local()) {
                    $payload['detail'] = $e->getMessage();
                }
                return Response::json($payload, nk_is_database_error($e) ? 503 : 500);
            }
            $status = nk_is_database_error($e) ? 503 : 500;
            $message = nk_is_database_error($e)
                ? 'The catalogue database is not available yet.'
                : (nk_is_local() ? $e->getMessage() : 'Something went wrong.');
            return self::errorPage($status, $message, $request, $e);
        }
    }

    public static function loadAuthUser(): void
    {
        $GLOBALS['nk_auth_user'] = null;
        $id = Session::get('user_id');
        if (!$id) {
            return;
        }
        try {
            $user = \App\Models\User::find((int)$id);
        } catch (\Throwable $e) {
            $user = null;
        }
        if (!$user || empty($user['is_active'])) {
            Session::forget('user_id');
            return;
        }
        $GLOBALS['nk_auth_user'] = $user;
    }

    /**
     * @param list<string> $middleware
     * @param callable():Response $next
     */
    public static function runMiddleware(array $middleware, Request $request, callable $next): Response
    {
        $runner = array_reduce(
            array_reverse($middleware),
            function (callable $next, string $name) use ($request): callable {
                return function () use ($next, $name, $request) {
                    return self::applyMiddleware($name, $request, $next);
                };
            },
            $next
        );
        return $runner();
    }

    /**
     * @param callable():Response $next
     */
    private static function applyMiddleware(string $name, Request $request, callable $next): Response
    {
        if ($name === 'csrf') {
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                Csrf::assert($request);
            }
            return $next();
        }

        if ($name === 'auth') {
            if (!current_user()) {
                if ($request->wantsJson()) {
                    return Response::json(['ok' => false, 'error' => 'Please sign in.'], 401);
                }
                Session::flash('error', 'Please sign in to continue.');
                Session::set('_intended', $request->path());
                return Response::redirect(url('login'));
            }
            return $next();
        }

        if ($name === 'guest') {
            $user = current_user();
            if ($user) {
                $to = ($user['role'] ?? '') === 'admin' ? 'admin' : 'dashboard';
                return Response::redirect(url($to));
            }
            return $next();
        }

        if (str_starts_with($name, 'role:')) {
            $role = substr($name, 5);
            $user = current_user();
            if (!$user) {
                if (str_starts_with($request->path(), '/admin')) {
                    return Response::redirect(url('admin/login'));
                }
                Session::flash('error', 'Please sign in to continue.');
                return Response::redirect(url('login'));
            }
            if (($user['role'] ?? '') !== $role) {
                if ($request->wantsJson()) {
                    return Response::json(['ok' => false, 'error' => 'Forbidden'], 403);
                }
                throw new HttpException(403, 'You do not have access to this page.');
            }
            return $next();
        }

        return $next();
    }

    private static function httpException(HttpException $e, Request $request): Response
    {
        $status = $e->status();
        if (in_array($status, [301, 302, 303, 307, 308], true) && $e->redirect()) {
            return Response::redirect($e->redirect(), $status);
        }
        if ($status === 419) {
            if ($request->wantsJson()) {
                return Response::json(['ok' => false, 'error' => 'Your session expired. Refresh and try again.'], 419);
            }
            Session::flash('error', 'Your session expired. Please try again.');
            $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
            return Response::redirect($ref !== '' ? $ref : url('/'));
        }
        if ($request->wantsJson()) {
            return Response::json(['ok' => false, 'error' => $e->getMessage() ?: 'Request failed'], $status);
        }
        if ($status === 404) {
            return self::errorPage(404, 'We could not find that page.', $request);
        }
        if ($status === 403) {
            return self::errorPage(403, $e->getMessage() ?: 'You do not have access to this page.', $request);
        }
        return self::errorPage($status, $e->getMessage() ?: 'Something went wrong.', $request);
    }

    private static function errorPage(int $status, string $message, Request $request, ?\Throwable $error = null): Response
    {
        if ($error && nk_is_database_error($error)) {
            return Response::html(nk_fatal_html($status, $message, $error), $status);
        }
        $file = project_root() . '/views/errors/generic.php';
        if (is_file($file)) {
            try {
                $title = $status === 404 ? 'Page not found' : 'Error';
                $contentView = 'errors/generic';
                $errorStatus = $status;
                $errorMessage = $message;
                ob_start();
                $isAdminPath = str_starts_with($request->path(), '/admin');
                if ($isAdminPath && current_user() && (current_user()['role'] ?? '') === 'admin') {
                    require project_root() . '/views/layouts/admin.php';
                } else {
                    require project_root() . '/views/layouts/site.php';
                }
                return Response::html((string)ob_get_clean(), $status);
            } catch (\Throwable $layoutError) {
                return Response::html(nk_fatal_html($status, $message, $error ?? $layoutError), $status);
            }
        }
        return Response::html(nk_fatal_html($status, $message, $error), $status);
    }
}
