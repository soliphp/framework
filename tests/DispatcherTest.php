<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Dispatcher;

class DispatcherTest extends TestCase
{
    /** @var \Soli\Dispatcher */
    protected $dispatcher;

    public function setUp()
    {
        $container = new Container;
        $container->remove('dispatcher');

        // 把 dispatcher 扔进容器，供 TestController 使用
        $container->set('dispatcher', function () use ($container) {
            $dispatcher = new Dispatcher();
            $dispatcher->setNamespaceName("\\Soli\\Tests\\Handlers\\");
            return $dispatcher;
        });

        $this->dispatcher = $container->getShared('dispatcher');
    }

    public function testDispatch()
    {
        $args = [
            'test',
            'hello',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertEquals('Hello, Soli.', $returnedResponse);
    }

    public function testForward()
    {
        // forward to test/index
        $args = [
            'test',
            'forward',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertEquals('test/index page', $returnedResponse);
    }

    protected function prepare(array $args)
    {
        // 设置控制器、方法及参数
        if (isset($args[0])) {
            $this->dispatcher->setHandlerName($args[0]);
        }
        if (isset($args[1])) {
            $this->dispatcher->setActionName($args[1]);
        }
        if (isset($args[2])) {
            $this->dispatcher->setParams(array_slice($args, 2));
        }
    }
}
