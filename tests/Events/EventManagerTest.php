<?php

namespace Soli\Tests\Events;

use Soli\Tests\TestCase;
use Soli\Events\EventManager;
use Soli\Events\Event;

use Soli\Tests\Data\Events\EComponent;
use Soli\Tests\Data\Events\EComponentEvents;

class EventManagerTest extends TestCase
{
    public function testFireByClosure()
    {
        $eventManager = new EventManager;
        // 监听事件
        $eventManager->on(
            'my-component:before',
            function (Event $event, $eComponent) {
                return 'before';
            }
        );

        $eComponent = new EComponent;
        $eComponent->setEventManager($eventManager);

        $result = $eComponent->someTask();

        $this->assertStringStartsWith('before, do something', $result);
    }

    public function testFireByInstance()
    {
        $eventManager = new EventManager;

        $eventManager->on(
            'my-component',
            new EComponentEvents()
        );

        $eComponent = new EComponent();
        $eComponent->setEventManager($eventManager);

        $result = $eComponent->someTask();

        $this->assertStringEndsWith('do something, after', $result);
    }
}
