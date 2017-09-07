<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 应用调度器
 *
 * @codeCoverageIgnore
 */
class Dispatcher extends BaseDispatcher
{
    /**
     * Default Handler
     */
    protected $handlerName = 'index';

    /**
     * Default Action
     */
    protected $actionName = 'index';

    protected $handlerSuffix = 'Controller';

    public function setControllerSuffix($handlerSuffix)
    {
        $this->handlerSuffix = $handlerSuffix;
    }

    public function setControllerName($handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getControllerName()
    {
        return $this->handlerName;
    }

    public function getPreviousNamespaceName()
    {
        return $this->previousNamespaceName;
    }

    public function getPreviousControllerName()
    {
        return $this->previousHandlerName;
    }

    public function getPreviousActionName()
    {
        return $this->previousActionName;
    }
}
