<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 应用
 *
 * @property \Soli\RouterInterface $router
 * @property \Soli\DispatcherInterface $dispatcher
 */
class App extends Component
{
    const VERSION = '2.0.0-dev';

    const ON_BOOT      = 'app.boot';
    const ON_FINISH    = 'app.finish';
    const ON_TERMINATE = 'app.terminate';

    /**
     * 默认核心服务
     */
    protected $coreServices = [
        //'router'     => \Soli\RouterInterface::class,
        'dispatcher' => \Soli\Dispatcher::class,
        'events'     => \Soli\Events\EventManager::class,
    ];

    /**
     * 应用初始化
     */
    public function __construct()
    {
        $this->registerCoreServices();
        // $this->registerAppServices();
    }

    protected function registerCoreServices()
    {
        $container = $this->getContainer();

        foreach ($this->coreServices as $name => $service) {
            // 允许自定义同名的 Service 覆盖默认的 Service
            if (!$container->has($name)) {
                $container->set($name, $service);
            }
        }
    }

    /**
     * 应用程序启动方法
     */
    public function handle($argv = null)
    {
        $this->trigger(static::ON_BOOT);

        $router = $this->router;
        $dispatcher = $this->dispatcher;

        $router->dispatch($argv);

        // 调度器预处理：设置命名空间、控制器、方法及参数
        if ($router->getNamespaceName()) {
            $dispatcher->setNamespaceName($router->getNamespaceName());
        }
        if ($router->getHandlerName()) {
            $dispatcher->setHandlerName($router->getHandlerName());
        }
        if ($router->getActionName()) {
            $dispatcher->setActionName($router->getActionName());
        }
        if ($router->getParams()) {
            $dispatcher->setParams($router->getParams());
        }

        // 执行调度
        $returnedResponse = $dispatcher->dispatch();

        $this->trigger(static::ON_FINISH, $returnedResponse);

        return $returnedResponse;
    }

    public function terminate()
    {
        $this->trigger(static::ON_TERMINATE);
    }
}
