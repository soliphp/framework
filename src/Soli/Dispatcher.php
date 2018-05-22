<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 调度器
 */
class Dispatcher extends Component
{
    protected $namespaceName = null;
    protected $handlerName = null;
    protected $actionName = null;
    protected $params = null;

    protected $handlerSuffix = 'Controller';
    protected $actionSuffix = null;

    /**
     * dispatch loop 是否结束
     *
     * @var bool
     */
    protected $finished = null;

    /**
     * BaseDispatcher constructor.
     */
    public function __construct()
    {
        $this->params = [];
    }

    /**
     * 执行调度
     */
    public function dispatch()
    {
        $numberDispatches = 0;
        $returnedResponse = null;
        $this->finished = false;

        if ($this->trigger('dispatcher.beforeDispatchLoop') === false) {
            return false;
        }

        // dispatch loop
        while (!$this->finished) {
            ++$numberDispatches;

            if ($numberDispatches >= 256) {
                throw new Exception('Dispatcher has detected a cyclic routing causing stability problems');
            }

            $this->finished = true;

            if ($this->trigger('dispatcher.beforeDispatch') === false) {
                continue;
            }
            // Check if the user made a forward in the listener
            if ($this->finished === false) {
                continue;
            }

            $handlerName = $this->namespaceName . ucfirst($this->handlerName) . $this->handlerSuffix;
            $actionName = $this->actionName . $this->actionSuffix;
            $params = $this->params;

            // Handler 是否存在
            if (!class_exists($handlerName)) {
                throw new Exception('Not found handler: ' . $handlerName);
            }

            // Action 是否可调用
            if (!is_callable([$handlerName, $actionName])) {
                throw new Exception("Not found action: $handlerName->$actionName");
            }

            // 参数格式是否正确
            if (!is_array($params)) {
                throw new Exception('Action parameters must be an array');
            }

            $handler = $this->container->get($handlerName);

            // 初始化
            if (method_exists($handler, 'initialize')) {
                $handler->initialize();
            }

            // 调用 Action
            $returnedResponse = call_user_func_array([$handler, $actionName], $params);

            $this->trigger('dispatcher.afterDispatch', $returnedResponse);
        }

        $this->trigger('dispatcher.afterDispatchLoop', $returnedResponse);

        return $returnedResponse;
    }

    /**
     * 无需 redirect 跳转，而直接调用对应的 Handler->Action
     *
     * @param array $forward {
     *   @var string namespace
     *   @var string controller
     *   @var string action
     *   @var array  params
     * }
     */
    public function forward(array $forward)
    {
        if (isset($forward['namespace'])) {
            $this->namespaceName = $forward['namespace'];
        }

        if (isset($forward['controller'])) {
            $this->handlerName = $forward['controller'];
        }

        if (isset($forward['action'])) {
            $this->actionName = $forward['action'];
        }

        if (isset($forward['params'])) {
            $this->params = $forward['params'];
        }

        $this->finished = false;
    }

    public function setNamespaceName($namespaceName)
    {
        $this->namespaceName = $namespaceName;
    }

    public function getNamespaceName()
    {
        return $this->namespaceName;
    }

    public function setControllerName($handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getControllerName()
    {
        return $this->handlerName;
    }

    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }
}
