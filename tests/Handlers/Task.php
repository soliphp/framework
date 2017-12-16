<?php

namespace Soli\Tests\Handlers;

use Soli\Console\Command;

class Task extends Command
{
    public function handle()
    {
        return 'Hello, Soli.';
    }
}
