<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\RouterTrait;

class RouterTraitTest extends TestCase
{
    /** @var \Soli\RouterInterface */
    protected static $router;

    public function getRouter()
    {
        if (static::$router === null) {
            static::$router = $this->getMockForTrait(RouterTrait::class);
        }

        return static::$router;
    }

    public static function tearDownAfterClass()
    {
        static::$router = null;
    }

    public function testRouterTraitSetDefaults()
    {
        $namespace = "Soli\\Tests\\Handlers\\";
        $handler = "test";
        $action = "hello";
        $params = ['name' => 'world'];

        /** @var RouterTrait $router */
        $router = $this->getRouter();
        $router->setDefaults([
            'namespace' => $namespace,
            'handler' => $handler,
            'action' => $action,
            'params' => $params,
        ]);

        $this->assertEquals($namespace, $router->getNamespaceName());
        $this->assertEquals($handler, $router->getHandlerName());
        $this->assertEquals($action, $router->getActionName());
        $this->assertEquals($params, $router->getParams());
    }
}
