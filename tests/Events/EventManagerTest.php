<?php

namespace Soli\Tests\Events;

use Soli\Tests\TestCase;
use Soli\Events\EventManager;
use Soli\Events\Event;

use Soli\Tests\Data\Events\EComponent;
use Soli\Tests\Data\Events\EComponentEvents;

class EventManagerTest extends TestCase
{
    public function testTrait()
    {
        $eventManager = new EventManager;
        $eComponent = new EComponent;

        $eComponent->setEventManager($eventManager);

        $this->assertTrue($eventManager === $eComponent->getEventManager());
    }

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

        $result = $eventManager->fire('my-component:before', $eventManager);
        $this->assertStringStartsWith('before', $result);
    }

    public function testFireByInstance()
    {
        $eventManager = new EventManager;

        $eventManager->on(
            'my-component',
            new EComponentEvents()
        );

        $result = $eventManager->fire('my-component:before', $eventManager);
        $this->assertNull($result);

        $result = $eventManager->fire('my-component:after', $eventManager);
        $this->assertStringStartsWith('after', $result);
    }

    public function testFireEmptyEvents()
    {
        $eventManager = new EventManager();

        $result = $eventManager->fire('events:empty', $eventManager);

        $this->assertNull($result);
    }

    /**
     * @expectedException \Exception
     */
    public function testFireInvalidEventType()
    {
        $eventManager = new EventManager();

        $eventManager->on(
            'my-component:before',
            function (Event $event, $eComponent) {
                return 'before';
            }
        );

        $eventManager->fire('invalidEventType', $eventManager);
    }

    public function testHasListeners()
    {
        $eventManager = new EventManager();

        $eventManager->on(
            'my-component:before',
            function (Event $event, $eComponent) {
                return 'before';
            }
        );

        $has = $eventManager->hasListeners('my-component:before');
        $this->assertTrue($has);
    }

    public function testGetListeners()
    {
        $eventManager = new EventManager();

        $eventManager->on(
            'my-component:before',
            function (Event $event, $eComponent) {
                return 'before';
            }
        );

        $listeners = $eventManager->getListeners('my-component:before');
        $this->assertFalse(empty($listeners));

        // off
        $eventManager->off('my-component:before');

        $listeners = $eventManager->getListeners('my-component:before');
        $this->assertTrue(empty($listeners));
    }
}
