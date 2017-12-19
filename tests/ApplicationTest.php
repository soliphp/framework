<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Application;
use Soli\Di\Container;
use Soli\Dispatcher;
use Soli\Events\EventManager;
use Soli\Events\Event;
use Soli\Http\Response;

class ApplicationTest extends TestCase
{
    protected function createApplication()
    {
        $container = new Container();
        $container->clear();

        $container->setShared('router', function () {
            $router = new \Soli\Router();

            $router->setDefaults([
                // 控制器的命名空间
                'namespace' => "Soli\\Tests\\Handlers\\",
                'controller' => "index",
                'action' => "index",
                'params' => []
            ]);

            $router->map('TEST', 'index/responseInstance', ['action' => 'responseInstance']);
            $router->map('TEST', 'index/hello/{name}', ['action' => 'hello']);
            $router->map('TEST', 'index/responseFalse', ['action' => 'responseFalse']);

            return $router;
        });

        $app = new Application();

        $_SERVER['REQUEST_METHOD'] = 'TEST';

        return $app;
    }

    public function testResponseInstance()
    {
        $app = $this->createApplication();
        $response = $app->handle('index/responseInstance');

        $this->assertEquals('response instance', $response->getContent());
    }

    public function testResponseString()
    {
        $app = $this->createApplication();
        $response = $app->handle('index/hello/Soli');
        $app->terminate();

        $this->assertEquals('Hello, Soli.', $response->getContent());
    }

    public function testResponseFalse()
    {
        $app = $this->createApplication();
        $response = $app->handle('index/responseFalse');

        $this->assertNull($response->getContent());
    }

    public function testRouterNamespace()
    {
        $app = $this->createApplication();

        $router = $app->router;
        $p = new \ReflectionProperty($router, 'namespaceName');
        $p->setAccessible(true);
        $p->setValue($router, "Soli\\Tests\\Handlers\\");

        $response = $app->handle('index/hello/Soli');

        $this->assertEquals('Hello, Soli.', $response->getContent());
    }

    public function testCatchExceptionEvent1()
    {
        $app = $this->createApplication();

        $exceptionResponseContent = 'Handled Exception: not found action';

        $this->setEventManager($app, $exceptionResponseContent);

        $response = $app->handle('index/notfoundxxxxxxx');

        $this->assertEquals($exceptionResponseContent, $response->getContent());
    }

    public function testCatchExceptionEvent2()
    {
        $app = $this->createApplication();

        $exceptionResponse = $app->response;

        $exceptionResponseContent = 'Handled Exception: not found action';
        $exceptionResponse->setContent($exceptionResponseContent);

        $this->setEventManager($app, $exceptionResponse);

        $response = $app->handle('index/notfoundxxxxxxx');

        $this->assertEquals($exceptionResponseContent, $response->getContent());
    }

    /**
     * @expectedException \Exception
     */
    public function testUncatchExceptionEvent3()
    {
        $app = $this->createApplication();

        $this->setEventManager($app, null);

        $app->handle('index/notfoundxxxxxxx');
    }

    /**
     * @param Application $app
     * @param Response|string $response 响应内容
     */
    protected function setEventManager(Application $app, $response)
    {
        $eventManager = new EventManager();

        $eventManager->attach(
            'application.exception',
            function (Event $event, Application $app, \Exception $exception) use ($response) {
                // exception handling
                $app->dispatcher->forward([
                    'namespace'  => "Soli\\Tests\\Handlers\\",
                    'controller' => 'index',
                    'action'     => 'handleException',
                    'params'     => [$response]
                ]);
                return $app->dispatcher->dispatch();
            }
        );

        $app->setEventManager($eventManager);
    }
}
