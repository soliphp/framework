<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\ContainerInterface;

/**
 * 应用基类
 *
 * @property \Soli\BaseDispatcher $dispatcher
*/
abstract class BaseApplication extends Component
{
    const VERSION = '1.1.0';

    /**
     * 默认注册服务
     */
    protected $defaultServices = [];

    /**
     * 应用初始化
     *
     * @param \Soli\Di\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        if (!is_object($container)) {
            $container = $this->getContainer();
        }

        foreach ($this->defaultServices as $name => $service) {
            // 允许自定义同名的 Service 覆盖默认的 Service
            if (!$container->has($name)) {
                $container->setShared($name, $service);
            }
        }
    }

    /**
     * 应用程序启动方法
     */
    abstract public function handle();
}
