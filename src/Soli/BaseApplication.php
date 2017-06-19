<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Container;

/**
 * 应用基类
 *
 * @property \Soli\BaseDispatcher $dispatcher
*/
abstract class BaseApplication extends Component
{
    const VERSION = '1.0';

    /**
     * 默认注册服务
     */
    protected $defaultServices = [];

    /**
     * 应用初始化
     *
     * @param \Soli\Di\Container $di
     */
    public function __construct(Container $di = null)
    {
        if (!is_object($di)) {
            $di = $this->getDi();
        }

        foreach ($this->defaultServices as $name => $service) {
            // 允许自定义同名的 Service 覆盖默认的 Service
            if (!$di->has($name)) {
                $di->set($name, $service, true);
            }
        }
    }

    /**
     * 应用程序启动方法
     */
    public function handle()
    {
        $eventManager = $this->getEventManager();

        // 调用 boot 事件
        if (is_object($eventManager)) {
            $eventManager->fire('application:boot', $this);
        }
    }

    /**
     * 调度器预处理
     *
     * @param array $args
     */
    protected function dispatcherPrepare(array $args)
    {
        // 设置控制器、方法及参数
        if (isset($args[0])) {
            $this->dispatcher->setHandlerName($args[0]);
        }
        if (isset($args[1])) {
            $this->dispatcher->setActionName($args[1]);
        }
        if (isset($args[2])) {
            $this->dispatcher->setParams(array_slice($args, 2));
        }
    }
}
