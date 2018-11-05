<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 调度器
 */
class Dispatcher extends Component implements DispatcherInterface
{
    protected $namespaceName = null;
    protected $handlerName = null;
    protected $actionName = null;
    protected $params = [];

    protected $handlerSuffix = 'Controller';
    protected $actionSuffix = null;

    const ON_BEFORE_DISPATCH_LOOP = 'dispatcher.beforeDispatchLoop';
    const ON_AFTER_DISPATCH_LOOP  = 'dispatcher.afterDispatchLoop';

    const ON_BEFORE_DISPATCH      = 'dispatcher.beforeDispatch';
    const ON_AFTER_DISPATCH       = 'dispatcher.afterDispatch';

    /**
     * dispatch loop 是否结束
     *
     * @var bool
     */
    protected $finished = null;

    /**
     * 执行调度
     */
    public function dispatch()
    {
        $numberDispatches = 0;
        $returnedResponse = null;
        $this->finished = false;

        $this->trigger(Dispatcher::ON_BEFORE_DISPATCH_LOOP);

        // dispatch loop
        while (!$this->finished) {
            ++$numberDispatches;

            if ($numberDispatches >= 256) {
                throw new \LogicException('Dispatcher has detected a cyclic routing causing stability problems');
            }

            $this->finished = true;

            $this->trigger(Dispatcher::ON_BEFORE_DISPATCH);

            // Check if the user made a forward in the listener
            if ($this->finished === false) {
                continue;
            }

            $handlerName = $this->namespaceName . ucfirst($this->handlerName) . $this->handlerSuffix;
            $actionName = $this->actionName . $this->actionSuffix;
            $params = $this->params;

            // Handler 是否存在
            if (!class_exists($handlerName)) {
                throw new \InvalidArgumentException('Handler not found: ' . $handlerName);
            }

            // Action 是否可调用
            if (!is_callable([$handlerName, $actionName])) {
                throw new \InvalidArgumentException("Action is not callable: $handlerName->$actionName()");
            }

            $handler = $this->container->get($handlerName);

            // 调用 Action
            $returnedResponse = call_user_func_array([$handler, $actionName], $params);

            $this->trigger(Dispatcher::ON_AFTER_DISPATCH, $returnedResponse);
        }

        $this->trigger(Dispatcher::ON_AFTER_DISPATCH_LOOP, $returnedResponse);

        return $returnedResponse;
    }

    /**
     * 无需 redirect 跳转，而直接调用对应的 Handler->Action
     *
     * @param array $forward {
     *   @var string namespace
     *   @var string handler
     *   @var string action
     *   @var array  params
     * }
     */
    public function forward(array $forward)
    {
        if (isset($forward['namespace'])) {
            $this->setNamespaceName($forward['namespace']);
        }

        if (isset($forward['handler'])) {
            $this->setHandlerName($forward['handler']);
        }

        if (isset($forward['action'])) {
            $this->setActionName($forward['action']);
        }

        if (isset($forward['params'])) {
            $this->setParams($forward['params']);
        }

        $this->finished = false;
    }

    public function setNamespaceName(string $namespaceName)
    {
        $this->namespaceName = $namespaceName;
    }

    public function getNamespaceName(): string
    {
        return $this->namespaceName;
    }

    public function setHandlerName(string $handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getHandlerName(): string
    {
        return $this->handlerName;
    }

    public function setActionName(string $actionName)
    {
        $this->actionName = $actionName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
