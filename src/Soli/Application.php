<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Http\Response;

/**
 * 应用
 *
 * @property \Soli\RouterInterface $router
 * @property \Soli\Dispatcher $dispatcher
 * @property \Soli\Http\Request $request
 * @property \Soli\Http\Response $response
 * @property \Soli\Session $session
 * @property \Soli\Session\Flash $flash
 * @property \Soli\ViewInterface $view
 */
class Application extends BaseApplication
{
    /**
     * 默认注册服务
     */
    protected $defaultServices = [
        'router'     => \Soli\Router\Simple::class,
        'dispatcher' => \Soli\Dispatcher::class,
        'request'    => \Soli\Http\Request::class,
        'response'   => \Soli\Http\Response::class,
        'session'    => \Soli\Session::class,
        'flash'      => \Soli\Session\Flash::class,
        'filter'     => \Soli\Filter::class,
    ];

    /**
     * 应用程序启动方法
     *
     * @param string|null $uri
     * @return \Soli\Http\Response
     */
    public function handle($uri = null)
    {
        $eventManager = $this->getEventManager();
        $router = $this->router;
        $dispatcher = $this->dispatcher;
        $response = $this->response;

        // 调用 boot 事件
        if (is_object($eventManager)) {
            $eventManager->fire('application:boot', $this);
        }

        $router->handle($uri);

        // 调度器预处理：设置控制器、方法及参数
        if ($router->getControllerName()) {
            $dispatcher->setHandlerName($router->getControllerName());
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
            if (is_string($returnedResponse)) {
                // 作为响应内容
                $response->setContent($returnedResponse);
            } elseif ($returnedResponse !== false) {
                // 渲染视图
                $response->setContent($this->viewRender());
            }
        }

        // 调用 beforeSendResponse 事件
        if (is_object($eventManager)) {
            $eventManager->fire('application:beforeSendResponse', $this, $response);
        }

        $response->sendHeaders();
        $response->sendCookies();

        return $response;
    }

    /**
     * 获取视图自动渲染内容
     *
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
}
