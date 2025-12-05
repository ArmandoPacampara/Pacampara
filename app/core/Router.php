<?php
class Router
{
    private $routes = [];

    public function add($path, $callback)
    {
        $this->routes[trim($path, "/")] = $callback;
    }

    public function handleRequest()
    {
        $url = isset($_GET['url']) ? trim($_GET['url'], "/") : "";

        if (isset($this->routes[$url])) {
            call_user_func($this->routes[$url]);
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}

?>