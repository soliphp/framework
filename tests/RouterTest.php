<?php

namespace Soli\Tests;

use Soli\Router;
use Soli\Http\Request;

class RouterTest extends TestCase
{
    protected $router;

    public function setUp()
    {
        static::$container->clear();
    }

    protected function setRequestStub($method = 'GET')
    {
        // 为 Request 类创建桩件
        $stub = $this->createMock(Request::class);

        // 配置桩件
        $stub->method('getMethod')
            ->willReturn($method);

        static::$container->set('request', $stub);
    }

    protected function initRouter()
    {
        $this->router = static::$container->get(Router::class);

        $this->router->map('/hello/{name}', $this->routeHandler(), 'GET');
        $this->router->map('/index/{page}', 'Soli\Tests\Handlers\Index::index', 'GET');
    }

    protected function routeHandler()
    {
        return [
            'namespace' => "Soli\\Tests\\Handlers\\",
            'controller' => "index",
            'action' => "hello",
        ];
    }

    public function testStringHandler()
    {
        $_GET['_uri'] = '/index/99';

        $this->setRequestStub();

        $this->initRouter();
        $this->router->handle();

        $handler = $this->routeHandler();

        $this->assertNull($this->router->getNamespaceName());
        $this->assertEquals("Soli\\Tests\\Handlers\\Index", $this->router->getControllerName());
        $this->assertEquals('index', $this->router->getActionName());

        $this->assertEquals('99', $this->router->getParams()['page']);
    }

    public function testGetters()
    {
        $_GET['_uri'] = '/hello/soliphp';

        $this->setRequestStub();

        $this->initRouter();
        $this->router->handle();

        $handler = $this->routeHandler();

        $this->assertEquals($handler['namespace'], $this->router->getNamespaceName());
        $this->assertEquals($handler['controller'], $this->router->getControllerName());
        $this->assertEquals($handler['action'], $this->router->getActionName());

        $this->assertEquals('soliphp', $this->router->getParams()['name']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Not found handler
     */
    public function testNotFoundException()
    {
        $this->setRequestStub();

        $this->initRouter();

        $this->router->handle('/notfoundxxxxxxx');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Method Not Allowed, allowed:.+/
     */
    public function testMethodNotAllowedException()
    {
        $this->setRequestStub('POST');

        $this->initRouter();

        $this->router->handle('/hello/soliphp');
    }
}
