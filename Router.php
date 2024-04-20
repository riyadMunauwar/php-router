<?php

class Router
{
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];
    private $middleware = [];

    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    public function put($uri, $controller)
    {
        $this->routes['PUT'][$uri] = $controller;
    }

    public function patch($uri, $controller)
    {
        $this->routes['PATCH'][$uri] = $controller;
    }

    public function delete($uri, $controller)
    {
        $this->routes['DELETE'][$uri] = $controller;
    }

    public function middleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch($method, $uri)
    {
        $uri = $this->parseUri($uri);
        $controller = null;

        if (array_key_exists($uri, $this->routes[$method])) {
            $controller = $this->routes[$method][$uri];
        } elseif ($route = $this->matchRouteWithParams($method, $uri)) {
            $controller = $route['controller'];
            $this->parseRouteParams($uri, $route['params']);
        }

        if ($controller !== null) {
            $this->callMiddleware($this->middleware);
            $this->callController($controller);
        } else {
            http_response_code(404);
            echo "Route not found";
        }
    }

    private function parseUri($uri)
    {
        return trim($uri, '/');
    }

    private function matchRouteWithParams($method, $uri)
    {
        foreach ($this->routes[$method] as $route => $controller) {
            $routeParams = [];
            $routeRegex = preg_replace('/\/{(\w+)}/', '/([\w-]+)', $route);
            $routeRegex = '/^' . $routeRegex . '$/';

            if (preg_match($routeRegex, $uri, $matches)) {
                unset($matches[0]);
                $routeParams = $matches;
                break;
            }
        }

        return isset($controller) ? ['controller' => $controller, 'params' => $routeParams] : null;
    }

    private function parseRouteParams($uri, $params)
    {
        $uriParts = explode('/', $uri);

        foreach ($params as $key => $value) {
            $_GET['params'][$key] = $value;
        }
    }

    private function callMiddleware($middleware)
    {
        foreach ($middleware as $mid) {
            $mid->handle();
        }
    }

    private function callController($controller)
    {
        if (is_callable($controller)) {
            call_user_func($controller);
        } elseif (is_string($controller)) {
            [$class, $method] = explode('@', $controller);
            $obj = new $class;
            $obj->$method();
        }
    }
}