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
        $di = new Container();
        $di->set('dispatcher', function () use ($di) {
            $dispatcher = new Dispatcher();
            $dispatcher->setNamespaceName("\\Soli\\Tests\\Handlers\\");
            return $dispatcher;
        });

        $app = new Application($di);

        return $app;
    }

    public function testSimple()
    {
        $app = $this->createApplication();
        $response = $app->handle('test/hello');

        $this->assertEquals('hello, Soli', $response->getContent());
    }
}
