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

            $routesConfig = [
                ['index/responseInstance', ['action' => 'responseInstance'], 'TEST'],
                ['index/hello/{name}', ['action' => 'hello'], 'TEST'],
                ['index/responseFalse', ['action' => 'responseFalse'], 'TEST'],
                ['index/normal', ['action' => 'normal'], 'TEST'],
                ['index/typeError/{id}', ['action' => 'typeError'], 'TEST'],
            ];

            $router->load($routesConfig);

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

    public function testResponseNormal()
    {
        $app = $this->createApplication();
        $response = $app->handle('index/normal');

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
     * @expectedExceptionMessage Not found handler
     */
    public function testUncatchExceptionEvent3()
    {
        $app = $this->createApplication();

        // Router 抛出 "Not found handler" 异常
        $app->handle('index/notfoundxxxxxxx');
    }

    public function testCatchThrowable()
    {
        $app = $this->createApplication();

        $exceptionResponseContent = 'Handled Exception: TypeError';

        $this->setEventManager($app, $exceptionResponseContent);

        $response = $app->handle('index/typeError/should-be-int');

        $this->assertEquals($exceptionResponseContent, $response->getContent());
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
            function (Event $event, Application $app, \Throwable $exception) use ($response) {
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
