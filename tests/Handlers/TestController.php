<?php

namespace Soli\Tests\Handlers;

use Soli\Controller;
use Soli\Exception;

class TestController extends Controller
{
    public function indexAction()
    {
        return 'test/index page';
    }

    public function helloAction()
    {
        return 'hello, Soli';
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
