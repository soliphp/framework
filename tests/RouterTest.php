<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;
use Soli\Di\Container;
use Soli\Router;
use Soli\Http\Request;

class RouterTest extends TestCase
{
    /** @var \Soli\Di\ContainerInterface */
    protected $container;

    protected $router;

    public function setUp()
    {
        $container = new Container();
        $container->clear();

        $this->container = $container;
    }

    protected function setRequestStub($method = 'GET')
    {
        // 为 Request 类创建桩件
        $stub = $this->createMock(Request::class);

        // 配置桩件
        $stub->method('getMethod')
            ->willReturn($method);

        $this->container->set('request', $stub);
    }

    protected function initRouter()
    {
        $this->router = $this->container->get(Router::class);

        $this->router->map('/hello/{name}', $this->routeHandler(), 'GET');
    }

    protected function routeHandler()
    {
        return [
            'namespace' => "Soli\\Tests\\Handlers\\",
            'controller' => "index",
            'action' => "hello",
        ];
    }

    //public function testDefaults()
    //{
    //}

    public function testGetters()
    {
        $_GET['_uri'] = '/hello/soliphp';

        $this->setRequestStub();

        $this->initRouter();
        $this->router->handle();

        $this->assertEquals($this->routeHandler()['namespace'], $this->router->getNamespaceName());
        $this->assertEquals($this->routeHandler()['controller'], $this->router->getControllerName());
        $this->assertEquals($this->routeHandler()['action'], $this->router->getActionName());

        $this->assertEquals('soliphp', $this->router->getParams()['name']);
    }

    /**
     * @expectedException \Exception
     */
    public function testNotFoundException()
    {
        $this->setRequestStub();

        $this->initRouter();

        $this->router->handle('/notfoundxxxxxxx');
    }

    /**
     * @expectedException \Exception
     */
    public function testMethodNotAllowedException()
    {
        $this->setRequestStub('POST');

        $this->initRouter();

        $this->router->handle('/hello/soliphp');
    }
}
