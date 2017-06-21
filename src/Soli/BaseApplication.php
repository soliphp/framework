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
    abstract public function handle();
}
