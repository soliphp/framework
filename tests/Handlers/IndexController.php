<?php

namespace Soli\Tests\Handlers;

use Soli\Controller;

class IndexController extends Controller
{
    public function initialize()
    {
    }

    public function indexAction()
    {
        return 'index page';
    }

    public function helloAction($name = 'Soli')
    {
        return "Hello, $name.";
    }

    public function forwardToHelloAction()
    {
        return $this->dispatcher->forward([
            'action' => 'hello',
        ]);
    }

    public function responseFalseAction()
    {
        return false;
    }

    public function responseInstanceAction()
    {
        return $this->response->setContent('response instance');
    }

    public function handleExceptionAction($response)
    {
        return $response;
    }
}
