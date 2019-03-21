<?php

namespace Soli\Tests\Handlers;

use Soli\Component;
use Soli\DispatcherInterface;

/**
 * @property \Soli\DispatcherInterface $dispatcher
 */
class TestController extends Component
{
    protected $isSameInstance;

    public function __construct(DispatcherInterface $dispatcher)
    {
        $this->isSameInstance = $dispatcher === $this->dispatcher;
    }

    public function serviceInstanceEqualConstructInjectInstance()
    {
        return $this->isSameInstance;
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

    public function typeError(int $id = 0)
    {
        return $id;
    }
}
