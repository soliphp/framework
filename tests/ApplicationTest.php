<?php

namespace Soli\Tests;

use Soli\Tests\TestCase;
use Soli\Application;
use Soli\Di\Container;
use Soli\Dispatcher;

class ApplicationTest extends TestCase
{
    protected function createApplication()
    {
        $container = new Container();
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

        $this->assertEquals('hello, Soli', $response->getContent());
    }
}
