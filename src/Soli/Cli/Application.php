<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Cli;

use Soli\BaseApplication;

/**
 * 命令行应用
 *
 * @property \Soli\Cli\Dispatcher $dispatcher
 */
class Application extends BaseApplication
{
    /**
     * 默认注册服务
     */
    protected $defaultServices = [
        'dispatcher' => \Soli\Cli\Dispatcher::class,
    ];

    /**
     * 应用程序启动方法
     *
     * @param array|null $args
     * @return mixed
     */
    public function handle(array $args = null)
    {
        parent::handle();

        $this->router($args);

        // 执行调度，并返回调度结果
        return $this->dispatcher->dispatch();
    }

    protected function router($args)
    {
        if (empty($args)) {
            $args = array_slice($_SERVER['argv'], 1);
        }

        $this->dispatcherPrepare($args);
    }
}
