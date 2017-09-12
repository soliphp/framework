<?php

namespace Soli\Tests\Handlers;

use Soli\Controller;

class TestController extends Controller
{
    public function initialize()
    {

    }

    public function indexAction()
    {
        return 'test/index page';
    }

    public function helloAction()
    {
        return 'Hello, Soli.';
    }

    public function forwardAction()
    {
        return $this->dispatcher->forward([
            'controller' => 'test',
            'action'     => 'index',
        ]);
    }

    public function handleExceptionAction($msg)
    {
        return 'Handled Exception: ' . $msg;
    }
}
