<?php

namespace Soli\Tests\Handlers;

use Soli\Console\Task;

class MainTask extends Task
{
    public function mainAction()
    {
        return 'Hello, Soli.';
    }
}
