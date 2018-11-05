<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\App;
use Soli\Di\Container;

use Soli\Events\EventManager;
use Soli\Events\Event;
use Soli\RouterInterface;
use Soli\RouterTrait;

class AppTest extends TestCase
{
    /** @var \Soli\App */
    protected $app;

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
        static::$container->set('router', new class implements RouterInterface {
            use RouterTrait;
            public function dispatch($argv = null)
            {
                if (isset($argv['namespace']) && is_string($argv['namespace'])) {
                    $this->namespaceName = $argv['namespace'];
                }
                if (isset($argv['handler']) && is_string($argv['handler'])) {
                    $this->handlerName = $argv['handler'];
                }
                if (isset($argv['action']) && is_string($argv['action'])) {
                    $this->actionName = $argv['action'];
                }
                if (isset($argv['params']) && is_array($argv['params'])) {
                    $this->params = $argv['params'];
                }
            }
        });

        $router = static::$container->get('router');
        $router->setDefaults([
            'namespace' => "Soli\\Tests\\Handlers\\",
            'handler' => "test",
            'action' => "index",
            'params' => []
        ]);

        $this->app = new App();
    }

    public function testHandleDefault()
    {
        $response = $this->app->handle();
        $this->assertEquals('index page', $response);
    }

    public function testActionWithParams()
    {
        $argv = [
            'handler' => 'test',
            'action' => 'hello',
            'params' => ['Soli'],
        ];
        $response = $this->app->handle($argv);
        $this->assertEquals('Hello, Soli.', $response);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage must be of the type integer, string given
     */
    public function testThrowable()
    {
        $argv = [
            'handler' => 'test',
            'action' => 'typeError',
            'params' => ['should-be-int'],
        ];

        $this->app->handle($argv);
    }

    public function testTerminate()
    {
        $expected = App::ON_TERMINATE;
        $this->expectOutputString($expected);

        static::$container->set('events', function () {
            $events = new EventManager();
            $events->attach(App::ON_TERMINATE, function (Event $event) {
                echo $event->getName();
            });
            return $events;
        });

        $this->app->terminate();
    }
}
