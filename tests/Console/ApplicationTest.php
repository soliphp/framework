<?php

namespace Soli\Tests\Console;

use PHPUnit\Framework\TestCase;

use Soli\Console\Application;
use Soli\Console\Dispatcher;
use Soli\Di\Container;
use Soli\Events\EventManager;
use Soli\Events\Event;

class ApplicationTest extends TestCase
{
    protected function createApplication()
    {
        $container = new Container();
        $container->remove('dispatcher');

        $container->set('dispatcher', function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setNamespaceName("Soli\\Tests\\Handlers\\");
            return $dispatcher;
        });

        $app = new Application($container);

        return $app;
    }

    public function testSimple()
    {
        $app = $this->createApplication();
        $_SERVER['argv'] = ['console', 'main', 'main', 'some params'];
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

        $output = $app->handle(['main', 'main', 'some params']);

        $this->assertEquals('Hello, Soli.', $output);
    }
}
