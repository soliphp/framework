<?php

namespace Soli\Tests\Events;

use Soli\Tests\TestCase;
use Soli\Events\Event;

class EventTest extends TestCase
{
    public function testFire()
    {
        $queue = [
            // Closure
            function (Event $event, $myComponent) {
                return 'notify 1';
            },
            // Object Instance
            new Observer,
        ];

        $event = new Event('notify', $this);
        // 最后一个监听者的返回值
        $status = $event->fire($queue);

        $this->assertEquals('notify 2', $status);
    }
}

class Observer
{
    public function notify()
    {
        return 'notify 2';
    }
}
