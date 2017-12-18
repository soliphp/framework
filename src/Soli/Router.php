<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

class Router extends Component
{
    protected $defaultNamespaceName = null;
    protected $defaultControllerName = 'index';
    protected $defaultActionName = 'index';
    protected $defaultParams = [];

    protected $namespaceName = null;

    protected $controllerName = null;

    protected $actionName = null;

    protected $params = [];

    protected $routes = [];

    /**
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * Add route.
     *
     * @param string|string[] $methods 'GET' or ['GET', 'POST']
     * @param string $pattern
     * @param array $handler
     */
    public function map($methods, $pattern, array $handler)
    {
        $this->routes[] = [
            'methods' => $methods,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function get($pattern, $handler)
    {
        $this->map('GET', $pattern, $handler);
    }

    public function post($pattern, $handler)
    {
        $this->map('POST', $pattern, $handler);
    }

    public function put($pattern, $handler)
    {
        $this->map('PUT', $pattern, $handler);
    }

    public function delete($pattern, $handler)
    {
        $this->map('DELETE', $pattern, $handler);
    }

    public function head($pattern, $handler)
    {
        $this->map('HEAD', $pattern, $handler);
    }

    public function trace($pattern, $handler)
    {
        $this->map('TRACE', $pattern, $handler);
    }

    public function options($pattern, $handler)
    {
        $this->map('OPTIONS', $pattern, $handler);
    }

    public function setDefaults(array $defaults)
    {
        if (isset($defaults['namespace'])) {
            $this->defaultNamespaceName = $defaults['namespace'];
        }
        if (isset($defaults['controller'])) {
            $this->defaultControllerName = $defaults['controller'];
        }
        if (isset($defaults['action'])) {
            $this->defaultActionName = $defaults['action'];
        }
        if (isset($defaults['params'])) {
            $this->defaultParams = $defaults['params'];
        }

        return $this;
    }

    public function getNamespaceName()
    {
        return $this->namespaceName ?? $this->defaultNamespaceName;
    }

    public function getControllerName()
    {
        return $this->controllerName ?? $this->defaultControllerName;
    }

    public function getActionName()
    {
        return $this->actionName ?? $this->defaultActionName;
    }

    public function getParams()
    {
        return $this->params ?? $this->defaultParams;
    }

    /**
     * @param string $uri
     */
    public function handle($uri = null)
    {
        return $this->handleDispatcherResponse(
            $this->createDispatcher()->dispatch(
                $this->request->getMethod(),
                $uri ?: $this->getRewriteUri()
            )
        );
    }

    /**
     * Create a FastRoute dispatcher instance for the application.
     *
     * @return \FastRoute\Dispatcher
     */
    protected function createDispatcher()
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['methods'], $route['pattern'], $route['handler']);
            }
        });
    }

    /**
     * Handle the response from the FastRoute dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleDispatcherResponse($routeInfo)
    {
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                throw new Exception('Not found handler');
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowed = (array)$routeInfo[1];
                throw new Exception('Method Not Allowed, allowed: ' . implode(',', $allowed));
            case \FastRoute\Dispatcher::FOUND:
                return $this->handleFoundRoute($routeInfo);
        }
    }

    /**
     * Handle a route found by the dispatcher.
     *
     * @param  array  $routeInfo
     * @return mixed
     */
    protected function handleFoundRoute($routeInfo)
    {
        $handler = $routeInfo[1];
        $params  = $routeInfo[2];

        $handler = array_merge($params, $handler);

        // 存储控制器、方法及参数
        if (isset($handler['namespace'])) {
            $this->namespaceName = $handler['namespace'];
        }

        if (isset($handler['controller'])) {
            $this->controllerName = $handler['controller'];
        }

        if (isset($handler['action'])) {
            $this->actionName = $handler['action'];
        }

        $this->params = $params;
    }

    protected function getRewriteUri()
    {
        $uri = $_GET['_uri'] ?? rawurldecode($_SERVER['REQUEST_URI']);
        // 去除 query string
        list($uri) = explode('?', $uri);

        return $uri ?: '/';
    }
}
