<?php

namespace Soli\Tests\Handlers;

use Soli\Console\Task;

class TestTask extends Task
{
    public function mainAction()
    {
        return 'Hello, Soli.';
    }
}
