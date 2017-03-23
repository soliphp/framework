<?php

namespace Soli\Tests\Events;

use Soli\Tests\TestCase;
use Soli\Events\EventManager;
use Soli\Events\Event;

class EventManagerTest extends TestCase
{
    public function testFire()
    {
        $eventManager = new EventManager;
        // 监听事件
        $eventManager->on(
            'my-component:before',
            function (Event $event, $myComponent) {
                return 'before';
            }
        );

        $eventManager->on(
            'my-component',
            new MyComponentEvents
        );

        $myComponent = new MyComponent;
        $myComponent->setEventManager($eventManager);

        $result = $myComponent->someTask();

        $this->assertStringStartsWith('before, do something', $result);
    }
}

class MyComponent
{
    protected $eventManager;

    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function someTask()
    {
        $before = $this->eventManager->fire('my-component:before', $this);

        // do something ...

        $after = $this->eventManager->fire('my-component:after', $this);

        return "$before, do something, $after";
    }
}

class MyComponentEvents
{
    public function after(Event $event, $myComponent)
    {
        return 'after';
    }
}
