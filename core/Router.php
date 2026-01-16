<?php
/**
 * Router Class
 * Handles URL routing and request dispatching
 */

class Router {
    private $routes = [];
    private $notFoundCallback;
    
    /**
     * Add a GET route
     */
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }
    
    /**
     * Add a POST route
     */
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }
    
    /**
     * Add a route
     */
    private function addRoute($method, $path, $callback) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    /**
     * Set 404 handler
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }
    
    /**
     * Dispatch the current request
     */
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        // Many clients (and tools like `curl -I`) use HEAD. Treat it like GET.
        if ($requestMethod === 'HEAD') {
            $requestMethod = 'GET';
        }
        $requestPath = $this->getPath();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod) {
                $pattern = $this->convertPathToRegex($route['path']);
                
                if (preg_match($pattern, $requestPath, $matches)) {
                    array_shift($matches); // Remove full match
                    return $this->executeCallback($route['callback'], $matches);
                }
            }
        }
        
        // No route matched - 404
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
    
    /**
     * Get the current path
     */
    private function getPath() {
        $path = $_SERVER['REQUEST_URI'];
        
        // Remove base path if needed
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // Remove query string
        $path = strtok($path, '?');

        // Ensure path starts with /
        $path = '/' . ltrim($path, '/');

        // Normalize trailing slash (except root)
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
    
    /**
     * Convert route path to regex pattern
     */
    private function convertPathToRegex($path) {
        // Convert :param to named capture group
        $pattern = preg_replace('/\/:([a-zA-Z0-9_]+)/', '/(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Execute the route callback
     */
    private function executeCallback($callback, $params = []) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }
        
        if (is_string($callback)) {
            $parts = explode('@', $callback);
            if (count($parts) === 2) {
                list($controller, $method) = $parts;
                
                $controllerFile = APP_PATH . '/controllers/' . $controller . '.php';
                
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    $controllerInstance = new $controller();
                    
                    if (method_exists($controllerInstance, $method)) {
                        return call_user_func_array([$controllerInstance, $method], $params);
                    }
                }
            }
        }
        
        throw new Exception("Invalid route callback");
    }
}
