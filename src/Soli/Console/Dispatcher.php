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
     * Default Handler
     */
    protected $handlerName = 'main';

    /**
     * Default Action
     */
    protected $actionName = 'main';

    protected $handlerSuffix = 'Task';

    public function setTaskSuffix($handlerSuffix)
    {
        $this->handlerSuffix = $handlerSuffix;
    }

    public function setTaskName($handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getTaskName()
    {
        return $this->handlerName;
    }
}
