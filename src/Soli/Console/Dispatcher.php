<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Console;

use Soli\BaseDispatcher;

/**
 * 命令行应用调度器
 *
 * @codeCoverageIgnore
 */
class Dispatcher extends BaseDispatcher
{
    /**
     * Default Action
     */
    protected $actionName = 'handle';

    public function setCommandName($handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getCommandName()
    {
        return $this->handlerName;
    }
}
