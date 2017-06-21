<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Container;
use Soli\Di\ContainerAwareInterface;
use Soli\Events\EventManagerInterface;
use Soli\Events\EventManagerAwareInterface;

/**
 * 调度器基类
 */
abstract class BaseDispatcher implements ContainerAwareInterface, EventManagerAwareInterface
{
    protected $namespaceName = '';
    protected $handlerName = null;
    protected $actionName = null;
    protected $params = null;

    protected $handlerSuffix = null;
    protected $actionSuffix = 'Action';

    protected $previousNamespaceName = null;
    protected $previousHandlerName = null;
    protected $previousActionName = null;

    /**
     * dispatch loop 是否结束
     *
     * @var bool
     */
    protected $finished = null;

    /**
     * @var \Soli\Di\Container
     */
    protected $di;

    /**
     * @var \Soli\Events\EventManager
     */
    protected $eventManager;

    const EXCEPTION_CYCLIC_ROUTING = 1;

    const EXCEPTION_HANDLER_NOT_FOUND = 2;

    const EXCEPTION_INVALID_PARAMS = 3;

    const EXCEPTION_ACTION_NOT_FOUND = 4;

    /**
     * BaseDispatcher constructor.
     */
    public function __construct()
    {
        $this->params = [];
    }

    public function setDi(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @return \Soli\Di\Container
     */
    public function getDi()
    {
        return $this->di;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return \Soli\Events\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * 执行调度
     */
    public function dispatch()
    {
        $numberDispatches = 0;
        $returnedResponse = null;
        $this->finished = false;

        $eventManager = $this->getEventManager();

        if (is_object($eventManager)) {
            if ($eventManager->fire('dispatch:beforeDispatchLoop', $this) === false) {
                return false;
            }
        }

        // dispatch loop
        while (!$this->finished) {
            ++$numberDispatches;

            if ($numberDispatches >= 256) {
                $this->throwDispatchException(
                    'Dispatcher has detected a cyclic routing causing stability problems',
                    static::EXCEPTION_CYCLIC_ROUTING
                );
                break;
            }

            $this->finished = true;

            $handlerName = $this->namespaceName . ucfirst($this->handlerName) . $this->handlerSuffix;
            $actionName = $this->actionName . $this->actionSuffix;
            $params = $this->params;

            if (is_object($eventManager)) {
                if ($eventManager->fire('dispatch:beforeDispatch', $this) === false) {
                    continue;
                }
                // Check if the user made a forward in the listener
                if ($this->finished === false) {
                    continue;
                }
            }

            // Handler 是否存在
            if (!class_exists($handlerName)) {
                $status = $this->throwDispatchException(
                    'Not Found handler: ' . $handlerName,
                    static::EXCEPTION_HANDLER_NOT_FOUND
                );

                // Check if the user made a forward in the listener
                if ($status === false && $this->finished === false) {
                    continue;
                }
                break;
            }

            // 参数格式是否正确
            if (!is_array($params)) {
                $status = $this->throwDispatchException(
                    "Action parameters must be an Array",
                    static::EXCEPTION_INVALID_PARAMS
                );

                // Check if the user made a forward in the listener
                if ($status === false && $this->finished === false) {
                    continue;
                }
                break;
            }

            // Action 是否可调用
            if (!is_callable([$handlerName, $actionName])) {
                if (is_object($eventManager)) {
                    if ($eventManager->fire('dispatch:beforeNotFoundAction', $this) === false) {
                        continue;
                    }

                    if ($this->finished === false) {
                        continue;
                    }
                }

                $status = $this->throwDispatchException(
                    sprintf('Not Found Action: %s->%s', $handlerName, $actionName),
                    static::EXCEPTION_ACTION_NOT_FOUND
                );
                // Check if the user made a forward in the listener
                if ($status === false && $this->finished === false) {
                    continue;
                }
                break;
            }

            $handler = $this->di->getShared($handlerName);

            // 初始化
            if (method_exists($handler, 'initialize')) {
                $handler->initialize();
            }

            try {
                // 调用 Action
                $returnedResponse = call_user_func_array([$handler, $actionName], $params);
            } catch (\Exception $e) {
                if ($this->handleException($e) === false) {
                    // forward to exception handler
                    if ($this->finished === false) {
                        continue;
                    }
                } else {
                    // rethrow it
                    throw $e;
                }
            }

            if (is_object($eventManager)) {
                $eventManager->fire('dispatch:afterDispatch', $this, $returnedResponse);
            }
        }

        if (is_object($eventManager)) {
            $eventManager->fire('dispatch:afterDispatchLoop', $this, $returnedResponse);
        }

        return $returnedResponse;
    }

    /**
     * 无需 redirect 跳转，而直接调用对应的 Handler->Action
     *
     * @param array $forward {
     *   @var string namespace
     *   @var string controller
     *   @var string task
     *   @var string action
     *   @var array  params
     * }
     */
    public function forward(array $forward)
    {
        if (isset($forward['namespace'])) {
            $this->previousNamespaceName = $this->namespaceName;
            $this->namespaceName = $forward['namespace'];
        }

        if (isset($forward['controller'])) {
            $this->previousHandlerName = $this->handlerName;
            $this->handlerName = $forward['controller'];
        } else {
            if (isset($forward['task'])) {
                $this->previousHandlerName = $this->handlerName;
                $this->handlerName = $forward['task'];
            }
        }

        if (isset($forward['action'])) {
            $this->previousActionName = $this->actionName;
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

    public function setHandlerName($handlerName)
    {
        $this->handlerName = $handlerName;
    }

    public function getHandlerName()
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

    /**
     * 设置 Action 参数
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * 获取 Action 参数
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 抛出内部异常
     *
     * @param string $message 异常消息内容
     * @param int $code 异常代码
     * @return bool
     * @throws \Soli\Exception
     */
    protected function throwDispatchException($message, $code = 0)
    {
        // 实例化异常
        $e = new Exception($message, $code);

        // 处理异常
        if ($this->handleException($e) === false) {
            return false;
        }

        // 如果没有处理，则抛出异常
        throw $e;
    }

    /**
     * 处理用户异常
     *
     * @param \Exception $e
     * @return bool
     */
    protected function handleException($e)
    {
        $eventManager = $this->getEventManager();
        if (is_object($eventManager)) {
            if ($eventManager->fire('dispatch:beforeException', $this, $e) === false) {
                return false;
            }
        }

        return true;
    }
}
