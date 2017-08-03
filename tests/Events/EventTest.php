<?php

namespace Soli\Tests\Events;

use Soli\Tests\TestCase;
use Soli\Events\Event;

use Soli\Tests\Data\Events\Observer;

class EventTest extends TestCase
{
    public function testFire()
    {
        $queue = [
            // Closure
            function (Event $event, $eComponent) {
                return 'notify 1';
            },
            // Object Instance
            new Observer(),
        ];

        $event = new Event('notify', $this);
        // 最后一个监听者的返回值
        $status = $event->fire($queue);

        $this->assertEquals('notify 2', $status);
    }
}
