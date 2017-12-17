<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Console;

use Soli\Component;
use Soli\Di\ContainerInterface;

/**
 * 命令行应用
 *
 * @property \Soli\Console\Dispatcher $dispatcher
 */
class Application extends Component
{
    /**
     * 默认注册服务
     */
    protected $defaultServices = [
        'dispatcher' => \Soli\Console\Dispatcher::class,
    ];

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
     *
     * @param array|null $args
     * @return mixed
     */
    public function handle(array $args = null)
    {
        $eventManager = $this->getEventManager();

        // 调用 boot 事件
        if (is_object($eventManager)) {
            $eventManager->trigger('console.boot', $this);
        }

        $this->router($args);

        // 执行调度，并返回调度结果
        return $this->dispatcher->dispatch();
    }

    /**
     * @param array $args
     */
    protected function router($args)
    {
        if (empty($args)) {
            $args = array_slice($_SERVER['argv'], 1);
        }

        // 调度器预处理：设置控制器、参数
        if (isset($args[0])) {
            $this->dispatcher->setCommandName($args[0]);
        }
        if (isset($args[1])) {
            $this->dispatcher->setParams(array_slice($args, 1));
        }
    }
}
