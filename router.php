<?php

namespace App;

class Router {
    private $routes;
    private $verbs;

    function __construct() {
        $this->verbs = [
            'get',
            'post',
            'put',
            'delete',
            'patch',
        ];
        
        foreach ($this->verbs as $verb) {
            $this->routes[$verb] = [];
        }
    }

    function addRoute($method, $handler) {
        if (!in_array($method, $this->verbs)) {
            throw new \Exception('Invalid HTTP verb');
        }

        $this->routes[$method][] = $handler;
    }

    function __call($method, $handler) {
        $this->addRoute($method, $handler);
    }

    // More info at https://stackoverflow.com/questions/30130913/how-to-do-url-matching-regex-for-routing-framework
    private function createRegex($pattern){
        if (preg_match('/[^-:\/_{}()a-zA-Z\d]/', $pattern))
            return false; // Invalid pattern
    
        // Turn "(/)" into "/?"
        $pattern = preg_replace('#\(/\)#', '/?', $pattern);
    
        // Create capture group for ":parameter"
        $allowedParamChars = '[a-zA-Z0-9\_\-]+';
        $pattern = preg_replace(
            '/:(' . $allowedParamChars . ')/',   # Replace ":parameter"
            '(?<$1>' . $allowedParamChars . ')', # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
            $pattern
        );
    
        // Create capture group for '{parameter}'
        $pattern = preg_replace(
            '/{('. $allowedParamChars .')}/',    # Replace "{parameter}"
            '(?<$1>' . $allowedParamChars . ')', # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
            $pattern
        );
    
        // Add start and end matching
        $patternAsRegex = "@^" . $pattern . "$@D";
    
        return $patternAsRegex;
    }

    function run() {
        $path = $_SERVER['PATH_INFO'] ?? '/';
        
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        
        if (!in_array($method, $this->verbs)) {
            throw new \Exception('HTTP verb not supported');
        }

        foreach ($this->routes[$method] as $route) {
            $routePath = $route[0];
            $controller = $route[1];
            $handler = $route[2];
            
            $routeRegex = $this->createRegex($routePath);
            $match = preg_match($routeRegex, $path, $matches);
            $params = array_intersect_key(
                $matches,
                array_flip(array_filter(array_keys($matches), 'is_string'))
            );

            if ($match === 1) {
                $controller = "\\App\\Controllers\\$controller";
                $controller = new $controller();
                $controller->$handler($params);
                return;
            }
        }

        throw new RouteNotFoundException();
    }
}

class RouteNotFoundException extends \Exception {}
