<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Dispatcher;
use Soli\Events\EventManager;
use Soli\Events\Event;

class DispatcherTest extends TestCase
{
    /** @var \Soli\Dispatcher */
    protected $dispatcher;

    public function setUp()
    {
        $container = new Container;
        $container->clear();

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
            'index',
            'hello',
            'Soli',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertEquals("\\Soli\\Tests\\Handlers\\", $this->dispatcher->getNamespaceName());
        $this->assertEquals('index', $this->dispatcher->getControllerName());
        $this->assertEquals('hello', $this->dispatcher->getActionName());
        $this->assertEquals('Soli', $this->dispatcher->getParams()[0]);

        $this->assertEquals('Hello, Soli.', $returnedResponse);
    }

    public function testForward()
    {
        // forward to test/index
        $args = [
            'index',
            'forwardToHello',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertEquals('Hello, Soli.', $returnedResponse);
    }

    protected function prepare(array $args)
    {
        // 设置控制器、方法及参数
        if (isset($args[0])) {
            $this->dispatcher->setControllerName($args[0]);
        }
        if (isset($args[1])) {
            $this->dispatcher->setActionName($args[1]);
        }
        if (isset($args[2])) {
            $this->dispatcher->setParams(array_slice($args, 2));
        }
    }

    public function testBeforeDispatchLoopEvent()
    {
        $eventManager = new EventManager();

        $eventManager->attach(
            'dispatcher.beforeDispatchLoop',
            function (Event $event, Dispatcher $dispatcher) {
                // 返回 false 拦截调度器继续执行
                return false;
            }
        );

        $this->dispatcher->setEventManager($eventManager);

        $args = [
            'index',
            'hello',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertFalse($returnedResponse);
    }

    public function testBeforeDispatchEvent()
    {
        $eventManager = new EventManager();

        $eventManager->attach(
            'dispatcher.beforeDispatch',
            function (Event $event, Dispatcher $dispatcher) {
                // 返回 false 拦截调度器本次执行
                return false;
            }
        );

        $this->dispatcher->setEventManager($eventManager);

        $args = [
            'index',
            'hello',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        // 未调度任何Action，被beforeDispatch拦截
        $this->assertNull($returnedResponse);
    }

    public function testCallForwardInBeforeDispatchEvent()
    {
        $eventManager = new EventManager();

        $eventManager->attach(
            'dispatcher.beforeDispatch',
            function (Event $event, Dispatcher $dispatcher) {
                $logged = $dispatcher->getParams()[0] ?? null;
                if ($logged == 'Not Logged') {
                    return $this->dispatcher->forward([
                        'action' => 'hello',
                        'params' => ['Soli'],
                    ]);
                }
            }
        );

        $this->dispatcher->setEventManager($eventManager);

        $args = [
            'index',
            'hello',
            'Not Logged',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertEquals('Hello, Soli.', $returnedResponse);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dispatcher has detected a cyclic routing causing stability problems
     */
    public function testCyclicRouting()
    {
        $eventManager = new EventManager();

        $eventManager->attach(
            'dispatcher.beforeDispatch',
            function (Event $event, Dispatcher $dispatcher) {
                // 始终返回当前Action
                return $this->dispatcher->forward([
                    'action' => $dispatcher->getActionName(),
                ]);
            }
        );

        $this->dispatcher->setEventManager($eventManager);

        $args = [
            'index',
            'index',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Not found handler: .+/
     */
    public function testNotFoundHandler()
    {
        $args = [
            'notfoundhandlerxxxxxxx',
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Not found action: .+/
     */
    public function testNotFoundAction()
    {
        $args = [
            'index',
            'notfoundactionxxxxxxx'
        ];

        $this->prepare($args);

        $returnedResponse = $this->dispatcher->dispatch();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Action parameters must be an array
     */
    public function testActionParametersMustBeAnArray()
    {
        $this->dispatcher->forward([
            'controller' => 'index',
            'action' => 'index',
            'params' => 'should-be-array',
        ]);

        $returnedResponse = $this->dispatcher->dispatch();
    }
}
