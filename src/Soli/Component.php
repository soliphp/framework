<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Container;
use Soli\Di\ContainerInterface;
use Soli\Di\ContainerAwareInterface;
use Soli\Events\EventManagerInterface;
use Soli\Events\EventManagerAwareInterface;

/**
 * 组件基类
 *
 * 通过 $this->{serviceName} 访问属性的方式访问所有注册到容器中的服务
 *
 * @property \Soli\Di\ContainerInterface $container
 * @property \Soli\Events\EventManagerInterface $eventManager
 */
class Component implements ContainerAwareInterface, EventManagerAwareInterface
{
    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $diContainer;

    /**
     * @var \Soli\Events\EventManagerInterface
     */
    protected $eventManager;

    public function setContainer(ContainerInterface $container)
    {
        $this->diContainer = $container;
    }

    /**
     * @return \Soli\Di\ContainerInterface
     */
    public function getContainer()
    {
        if ($this->diContainer === null) {
            $this->diContainer = Container::instance() ?: new Container;
        }
        return $this->diContainer;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return \Soli\Events\EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * 触发事件
     *
     *<code>
     * $this->trigger('dispatch.beforeDispatchLoop', $data);
     *
     * $event = new Event('application.boot', $data);
     * $this->trigger($event);
     *</code>
     *
     * @param string|EventInterface $event 事件名称或事件对象实例
     * @param mixed $data 事件相关数据
     * @return mixed
     *
     */
    public function trigger($event, $data = null)
    {
        if (is_object($this->eventManager)) {
            return $this->eventManager->trigger($event, $this, $data);
        }
    }

    /**
     * 获取容器本身，或者获取容器中的某个服务
     *
     * @param string $name
     * @return \Soli\Di\ContainerInterface|mixed
     */
    public function __get($name)
    {
        $container = $this->getContainer();

        if ($name == 'container') {
            $this->container = $container;
            return $container;
        }

        if ($container->has($name)) {
            $service = $container->getShared($name);
            // 将找到的服务添加到属性, 以便下次直接调用
            $this->$name = $service;
            return $service;
        }

        trigger_error("Access to undefined property $name");
    }
}
