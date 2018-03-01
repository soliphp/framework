<?php

namespace Soli\Tests\Events;

use PHPUnit\Framework\TestCase;

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

    public function testTriggerByClosure()
    {
        $eventManager = new EventManager;

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->attach('my-component.before', $before);

        $result = $eventManager->trigger('my-component.before', $eventManager);
        $this->assertStringStartsWith('before', $result);
    }

    public function testTriggerByInstance()
    {
        $eventManager = new EventManager;

        $eventManager->attach('my-component', new EComponentEvents());

        $result = $eventManager->trigger('my-component.before', $eventManager);
        $this->assertNull($result);

        $result = $eventManager->trigger('my-component.after', $eventManager);
        $this->assertStringStartsWith('after', $result);
    }

    public function testTriggerEmptyEvents()
    {
        $eventManager = new EventManager();

        $result = $eventManager->trigger('events.empty', $eventManager);

        $this->assertNull($result);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid event type
     */
    public function testTriggerInvalidEventType()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->attach('my-component.before', $before);

        $eventManager->trigger(new \stdClass(), $eventManager);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid event type
     */
    public function testTriggerInvalidEventType2()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->attach('my-component.before', $before);

        $eventManager->trigger('invalidEventType', $eventManager);
    }

    public function testTriggerEventInstance()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        $name = 'my-component.before';

        // 监听事件
        $eventManager->attach($name, $before);

        $event = new Event($name, $this);

        $result = $eventManager->trigger($event);

        $this->assertStringStartsWith('before', $result);
    }

    public function testClearListeners()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        // 监听事件
        $eventManager->attach('my-component.before', $before);

        $eventManager->clearListeners('my-component.before');

        $listeners = $eventManager->getListeners('my-component.before');
        $this->assertEmpty($listeners);
    }

    public function testGetListeners()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            return 'before';
        };

        $eventManager->attach('my-component.before', $before);

        $listeners = $eventManager->getListeners('my-component.before');
        $this->assertTrue($before === $listeners[0]);

        // detach
        $eventManager->detach('my-component.before', $before);

        $listeners = $eventManager->getListeners('my-component.before');
        $this->assertEmpty($listeners);
    }

    public function testStopPropagation()
    {
        $eventManager = new EventManager();

        $before = function (Event $event, $eComponent) {
            $event->stopPropagation();
            return 'before listener return value.';
        };

        $before2 = function (Event $event, $eComponent) {
            return 'Will not be executed.';
        };

        $eventManager->attach('my-component.before', $before);
        $eventManager->attach('my-component.before', $before2);

        $status = $eventManager->trigger('my-component.before');
        $this->assertEquals('before listener return value.', $status);
    }
}
