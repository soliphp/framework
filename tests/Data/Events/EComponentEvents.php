<?php

namespace Soli\Tests\Data\Events;

use Soli\Events\Event;

class EComponentEvents
{
    public function after(Event $event, $myComponent)
    {
        return 'after';
    }
}
