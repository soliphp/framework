<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Console;

use Soli\BaseApplication;

/**
 * 命令行应用
 *
 * @property \Soli\Console\Dispatcher $dispatcher
 */
class Application extends BaseApplication
{
    /**
     * 默认注册服务
     */
    protected $defaultServices = [
        'dispatcher' => \Soli\Console\Dispatcher::class,
    ];

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
            $this->dispatcher->setHandlerName($args[0]);
        }
        if (isset($args[1])) {
            $this->dispatcher->setParams(array_slice($args, 1));
        }
    }
}
