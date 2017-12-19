<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\ContainerInterface;
use Soli\Http\Response;
use Exception;
use Throwable;

/**
 * 应用
 *
 * @property \Soli\Router $router
 * @property \Soli\Dispatcher $dispatcher
 * @property \Soli\Http\Request $request
 * @property \Soli\Http\Response $response
 * @property \Soli\Session $session
 * @property \Soli\Session\Flash $flash
 * @property \Soli\ViewInterface $view
 */
class Application extends Component
{
    const VERSION = '1.3.0';

    /**
     * 默认注册服务
     */
    protected $defaultServices = [
        'router'     => \Soli\Router::class,
        'dispatcher' => \Soli\Dispatcher::class,
        'request'    => \Soli\Http\Request::class,
        'response'   => \Soli\Http\Response::class,
        'session'    => \Soli\Session::class,
        'flash'      => \Soli\Session\Flash::class,
        'filter'     => \Soli\Filter::class,
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
     * @param string|null $uri
     * @return \Soli\Http\Response
     */
    public function handle($uri = null)
    {
        try {
            return $this->handleInternal($uri);
        } catch (Exception $e) {
            return $this->handleException($e);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function terminate()
    {
        $this->trigger('application.terminate');
    }

    protected function handleInternal($uri)
    {
        $this->trigger('application.boot');

        $router = $this->router;
        $dispatcher = $this->dispatcher;

        $router->handle($uri);

        // 调度器预处理：设置控制器、方法及参数
        if ($router->getNamespaceName()) {
            $dispatcher->setNamespaceName($router->getNamespaceName());
        }
        if ($router->getControllerName()) {
            $dispatcher->setControllerName($router->getControllerName());
        }
        if ($router->getActionName()) {
            $dispatcher->setActionName($router->getActionName());
        }
        if ($router->getParams()) {
            $dispatcher->setParams($router->getParams());
        }

        // 不自动渲染视图的四种方式:
        // 1. 返回 Response 实例
        // 2. 返回 string 类型作为响应内容
        // 3. 返回 false
        // 4. 禁用视图

        // 执行调度
        $returnedResponse = $dispatcher->dispatch();

        if ($returnedResponse instanceof Response) {
            $response = $returnedResponse;
        } else {
            $response = $this->response;
            if (is_string($returnedResponse)) {
                // 作为响应内容
                $response->setContent($returnedResponse);
            } elseif ($returnedResponse !== false) {
                // 渲染视图
                $response->setContent($this->viewRender());
            }
        }

        $this->trigger('application.finish', $response);

        return $response;
    }

    /**
     * 获取视图自动渲染内容
     *
     * @codeCoverageIgnore
     * @return string
     */
    protected function viewRender()
    {
        if (!$this->container->has('view')) {
            return null;
        }

        // 视图实例
        $view = $this->view;

        // 视图被禁用
        if ($view->isDisabled()) {
            return null;
        }

        // 获取模版文件路径
        $controller = $this->dispatcher->getControllerName();
        $action     = $this->dispatcher->getActionName();
        $template   = "$controller/$action";

        // 将 Flash 服务添加到 View
        $view->setVar('flash', $this->flash);

        // 自动渲染视图
        return $view->render($template);
    }

    protected function handleException(Exception $e)
    {
        $returnedResponse = $this->trigger('application.exception', $e);
        if ($returnedResponse instanceof Response) {
            return $returnedResponse;
        } elseif (is_string($returnedResponse)) {
            return $this->response->setContent($returnedResponse);
        }

        throw $e;
    }
}
