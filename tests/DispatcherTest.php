<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Dispatcher;

use Soli\Di\Container;
use Soli\DispatcherInterface;
use Soli\Events\EventManager;
use Soli\Events\Event;

class DispatcherTest extends TestCase
{
    /** @var \Soli\Dispatcher */
    protected $dispatcher;

    /** @var \Soli\Di\ContainerInterface */
    protected static $container;

    public static function setUpBeforeClass()
    {
        static::$container = new Container();
    }

    public static function tearDownAfterClass()
    {
        static::$container = null;
    }

    public function setUp()
    {
        static::$container->set('events', EventManager::class);
        static::$container->set('dispatcher', Dispatcher::class);
        static::$container->alias(DispatcherInterface::class, 'dispatcher');

        $this->dispatcher = static::$container->get('dispatcher');
        $this->dispatcher->setNamespaceName("\\Soli\\Tests\\Handlers\\");
    }

    protected function prepare(array $argv)
    {
        if (isset($argv['namespace'])) {
            $this->dispatcher->setNamespaceName($argv['namespace']);
        }

        if (isset($argv['handler'])) {
            $this->dispatcher->setHandlerName($argv['handler']);
        }

        if (isset($argv['action'])) {
            $this->dispatcher->setActionName($argv['action']);
        }

        if (isset($argv['params'])) {
            $this->dispatcher->setParams($argv['params']);
        }
    }

    public function testDispatch()
    {
        $argv = [
            'handler' => 'test',
            'action' => 'hello',
            'params' => ['Soli'],
        ];

        $this->prepare($argv);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertEquals("\\Soli\\Tests\\Handlers\\", $this->dispatcher->getNamespaceName());
        $this->assertEquals('test', $this->dispatcher->getHandlerName());
        $this->assertEquals('hello', $this->dispatcher->getActionName());
        $this->assertEquals('Soli', $this->dispatcher->getParams()[0]);

        $this->assertEquals('Hello, Soli.', $returnedResponse);
    }

    public function testForward()
    {
        $argv = [
            'handler' => 'test',
            'action' => 'forwardToHello',
        ];

        $this->prepare($argv);

        $returnedResponse = $this->dispatcher->dispatch();

        $this->assertEquals('Hello, Soli.', $returnedResponse);
    }

    public function testEventBeforeDispatchCallForward()
    {
        $events = static::$container->get('events');

        $events->attach(
            Dispatcher::ON_BEFORE_DISPATCH,
            function (Event $event, Dispatcher $dispatcher) {
                $logged = $dispatcher->getParams()[0] ?? null;
                if ($logged == 'Not Logged') {
                    $dispatcher->forward([
                        'action' => 'hello',
                        'params' => ['Soli'],
                    ]);
                }
            }
        );

        $argv = [
            'handler' => 'test',
            'action' => 'hello',
            'params' => ['Not Logged'],
        ];

        $this->prepare($argv);

        $returnedResponse = $this->dispatcher->dispatch();

        // forward to hello
        $this->assertEquals('Hello, Soli.', $returnedResponse);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dispatcher has detected a cyclic routing causing stability problems
     */
    public function testCyclicRouting()
    {
        $events = static::$container->get('events');

        $events->attach(
            Dispatcher::ON_BEFORE_DISPATCH,
            function (Event $event, Dispatcher $dispatcher) {
                // 始终返回当前Action
                return $dispatcher->forward([
                    'action' => $dispatcher->getActionName(),
                ]);
            }
        );

        $argv = [
            'handler' => 'test',
            'action' => 'index',
        ];

        $this->prepare($argv);

        $this->dispatcher->dispatch();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Handler not found: .+/
     */
    public function testHandlerNotFound()
    {
        $argv = [
            'handler' => 'HandlerNotFound_xxx',
        ];

        $this->prepare($argv);

        $this->dispatcher->dispatch();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp /Action is not callable: .+/
     */
    public function testActionIsNotCallable()
    {
        $argv = [
            'handler' => 'test',
            'action' => 'ActionIsNotCallable_xxx'
        ];

        $this->prepare($argv);

        $this->dispatcher->dispatch();
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage must be of the type array, string given
     */
    public function testActionParametersMustBeAnArray()
    {
        $this->dispatcher->forward([
            'namespace' => "\\Soli\\Tests\\Handlers\\",
            'handler' => 'test',
            'action' => 'index',
            'params' => 'must-be-array',
        ]);

        $this->dispatcher->dispatch();
    }
}
