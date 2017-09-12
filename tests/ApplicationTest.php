<?php

namespace Soli\Tests;

use Soli\Application;
use Soli\Di\Container;
use Soli\Dispatcher;
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
        $response = $app->handle('test/hello');

        $this->assertEquals('Hello, Soli.', $response->getContent());
    }

    /**
     * 处理异常事件
     */
    public function testExceptionEvent()
    {
        $app = $this->createApplication();

        $this->setEventManager($app);

        $response = $app->handle('test/notfoundxxxxxxx');

        $this->assertStringStartsWith('Handled Exception', $response->getContent());
    }

    protected function setEventManager(Application $app)
    {
        $eventManager = new EventManager();

        $eventManager->attach(
            'application.exception',
            function (Event $event, Application $app, \Exception $exception) {
                // exception handling
                $app->dispatcher->forward([
                    'namespace'  => "Soli\\Tests\\Handlers\\",
                    'controller' => 'test',
                    'action'     => 'handleException',
                    'params'     => [$exception->getMessage()]
                ]);
                return $app->dispatcher->dispatch();
            }
        );

        $app->setEventManager($eventManager);
    }
}
