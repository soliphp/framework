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

    protected $handlerSuffix = 'Task';

    /**
     * Default Action
     */
    protected $actionName = 'main';

    protected $actionSuffix = 'Action';

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
