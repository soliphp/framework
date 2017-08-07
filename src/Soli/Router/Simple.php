<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Router;

use Soli\RouterInterface;

class Simple implements RouterInterface
{
    protected $namespaceName = null;

    protected $controllerName = null;

    protected $actionName = null;

    protected $params = [];

    /**
     * @param string $uri
     */
    public function handle($uri = null)
    {
        if (empty($uri)) {
            $uri = $this->getRewriteUri();
        }

        // 去除左右斜杠，并以斜杠切分为数组
        $uri = trim($uri, '/');
        $args = $uri ? explode('/', $uri) : [];

        // 存储控制器、方法及参数
        if (isset($args[0])) {
            $this->controllerName = $args[0];
        }
        if (isset($args[1])) {
            $this->actionName = $args[1];
        }
        if (isset($args[2])) {
            $this->params = array_slice($args, 2);
        }
    }

    protected function getRewriteUri()
    {
        $uri = isset($_GET['_uri']) ? $_GET['_uri'] : $_SERVER['REQUEST_URI'];
        $uri = filter_var($uri, FILTER_SANITIZE_URL);

        // 去除 query string
        list($uri) = explode('?', $uri);

        return $uri ?: '/';
    }

    /**
     * Not implemented
     */
    public function getNamespaceName()
    {
        return $this->namespaceName;
    }

    public function getControllerName()
    {
        return $this->controllerName;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function getParams()
    {
        return $this->params;
    }
}
