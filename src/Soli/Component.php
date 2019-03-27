<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Container;
use Soli\Di\ContainerInterface;
use Soli\Di\ContainerAwareInterface;

/**
 * 组件基类
 *
 * @property \Soli\Di\ContainerInterface $container
 * @property \Soli\Events\EventManagerInterface $events
 */
class Component implements ContainerAwareInterface
{
    /**
     * @var \Soli\Di\ContainerInterface
     */
    protected $diContainer;

    public function setContainer(ContainerInterface $container)
    {
        $this->diContainer = $container;
    }

    public function getContainer(): ContainerInterface
    {
        if ($this->diContainer === null) {
            $this->diContainer = Container::instance() ?: new Container();
        }
        return $this->diContainer;
    }

    /**
     * 监听某个事件
     *
     * @param string $name 事件名称
     * @param object $listener 匿名函数|对象实例
     */
    public function listen($name, $listener)
    {
        $this->events->attach($name, $listener);
    }

    /**
     * 触发事件
     *
     *<pre>
     * $this->trigger(App::ON_BOOT, $data);
     *
     * $event = new Event(App::ON_FINISH, $data);
     * $this->trigger($event);
     *</pre>
     *
     * @param string|\Soli\Events\EventInterface $event 事件名称或事件对象实例
     * @param mixed $data 事件相关数据
     * @return bool 是否执行了当前事件的监听器
     *
     */
    public function trigger($event, $data = null)
    {
        return $this->events->trigger($event, $this, $data);
    }

    /**
     * 获取容器中的某个服务
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
            $service = $container->get($name);
            $this->{$name} = $service;
            return $service;
        }

        trigger_error("Access to undefined property $name");
        return null;
    }
}
