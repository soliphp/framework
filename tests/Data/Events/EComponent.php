<?php

namespace Soli\Tests\Data\Events;

use Soli\Events\EventManagerAwareTrait;

class EComponent
{
    use EventManagerAwareTrait;

    public function someTask()
    {
        $before = $this->eventManager->fire('my-component:before', $this);

        // do something ...

        $after = $this->eventManager->fire('my-component:after', $this);

        return "$before, do something, $after";
    }
}
