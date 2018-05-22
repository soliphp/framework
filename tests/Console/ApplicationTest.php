<?php

namespace Soli\Tests\Console;

use Soli\Tests\TestCase;

use Soli\Console\Application;
use Soli\Console\Dispatcher;
use Soli\Events\EventManager;
use Soli\Events\Event;

class ApplicationTest extends TestCase
{
    protected function setUp()
    {
        static::$container->set('dispatcher', function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setNamespaceName("Soli\\Tests\\Handlers\\");
            return $dispatcher;
        });
    }

    protected function createApplication()
    {
        return new Application();
    }

    public function testSimple()
    {
        $app = $this->createApplication();
        $_SERVER['argv'] = ['console', 'task', 'some params'];

        //var_dump($app);
        //var_dump($app->dispatcher);
        $output = $app->handle();

        $this->assertEquals('Hello, Soli.', $output);
    }

    public function testConsoleBootEvent()
    {
        $app = $this->createApplication();

        $eventManager = new EventManager();
        $eventManager->attach('console.boot', function (Event $event) {
            return 'booted';
        });

        $app->setEventManager($eventManager);

        $output = $app->handle(['task', 'some params']);

        $this->assertEquals('Hello, Soli.', $output);
    }
}
