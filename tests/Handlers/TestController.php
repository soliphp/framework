<?php

namespace Soli\Tests\Handlers;

use Soli\Component;

/**
 * @property \Soli\DispatcherInterface $dispatcher
 */
class TestController extends Component
{
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

    public function typeError(int $id = 0)
    {
        return $id;
    }
}
