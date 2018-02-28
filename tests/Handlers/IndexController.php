<?php

namespace Soli\Tests\Handlers;

use Soli\Controller;

class IndexController extends Controller
{
    public function initialize()
    {
    }

    public function index()
    {
        return 'index page';
    }

    public function hello($name = 'Soli')
    {
        return "Hello, $name.";
    }

    public function forwardToHello()
    {
        return $this->dispatcher->forward([
            'action' => 'hello',
        ]);
    }

    public function responseFalse()
    {
        return false;
    }

    public function responseInstance()
    {
        return $this->response->setContent('response instance');
    }

    public function handleException($response)
    {
        return $response;
    }

    public function normal()
    {
    }

    public function typeError(int $id)
    {
        return $id;
    }
}
