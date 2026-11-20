<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var list<array{method:string,path:string,handler:mixed,middleware:array}> */
    private array $routes = [];

    public function get(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    public function any(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('ANY', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, mixed $handler, array $middleware): self
    {
        $path = '/' . ltrim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $method) {
                continue;
            }
            $params = $this->match($route['path'], $path);
            if ($params === null) {
                continue;
            }
            return Kernel::runMiddleware($route['middleware'], $request, function () use ($route, $request, $params) {
                return $this->invoke($route['handler'], $request, $params);
            });
        }

        throw new HttpException(404, 'Page not found');
    }

    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (!preg_match($regex, $path, $m)) {
            return null;
        }
        $params = [];
        foreach ($m as $k => $v) {
            if (is_string($k)) {
                $params[$k] = $v;
            }
        }
        return $params;
    }

    private function invoke(mixed $handler, Request $request, array $params): Response
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            if (!str_starts_with($class, 'App\\')) {
                $class = 'App\\Controllers\\' . $class;
            }
            if (!class_exists($class)) {
                throw new HttpException(500, 'Controller missing');
            }
            $controller = new $class();
            if (!method_exists($controller, $method)) {
                throw new HttpException(500, 'Action missing');
            }
            $result = $controller->{$method}($request, $params);
        } elseif (is_callable($handler)) {
            $result = $handler($request, $params);
        } else {
            throw new HttpException(500, 'Invalid route handler');
        }

        if ($result instanceof Response) {
            return $result;
        }
        if (is_string($result)) {
            return Response::html($result);
        }
        if (is_array($result)) {
            return Response::json($result);
        }
        return Response::empty();
    }
}
