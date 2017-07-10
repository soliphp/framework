<?php

namespace Soli\Tests;

use Soli\Tests\TestCase;
use Soli\Di\Container;
use Soli\Dispatcher;
use Soli\Events\EventManager;
use Soli\Events\Event;

class DispatcherTest extends TestCase
{
    protected $dispatcher;

    public function setUp()
    {
        $container = new Container;
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

        $this->assertEquals('hello, Soli', $returnedResponse);
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

    /**
     * 处理异常事件
     */
    public function testExceptionEvent()
    {
        $this->setEventManager();

        $args = [
            'test',
            'notfoundxxxxxxx',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertStringStartsWith('Handled Exception', $returnedResponse);
    }

    protected function setEventManager()
    {
        $eventManager = new EventManager;
        // Dispatch Events
        $eventManager->on(
            'dispatch:beforeException',
            function (Event $event, Dispatcher $dispatcher, $exception) {
                if ($exception instanceof Exception) {
                    switch ($exception->getCode()) {
                        case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                        case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                            // exception handling
                            $dispatcher->forward([
                                'controller' => 'test',
                                'action'     => 'handleException',
                                'params'     => [$exception->getMessage()]
                            ]);
                            return false;
                    }
                }

                // exception handling
                $dispatcher->forward([
                    'controller' => 'test',
                    'action'     => 'handleException',
                    'params'     => [$exception->getMessage()]
                ]);
                return false;
            }
        );

        $this->dispatcher->setEventManager($eventManager);
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
