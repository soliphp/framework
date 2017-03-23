<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Soli\Di\Container;
use Soli\Di\ContainerAwareInterface;
use Soli\Events\EventManagerInterface;
use Soli\Events\EventManagerAwareInterface;

/**
 * 组件基类
 *
 * 通过 $this->{serviceName} 访问属性的方式访问所有注册到容器中的服务
 *
 * @property \Soli\Di\Container $di
 * @property \Soli\Events\EventManager $eventManager
 */
class Component implements ContainerAwareInterface, EventManagerAwareInterface
{
    /**
     * @var \Soli\Di\Container
     */
    protected $container;

    /**
     * @var \Soli\Events\EventManager
     */
    protected $eventManager;

    public function setDi(Container $di)
    {
        $this->container = $di;
    }

    /**
     * @return \Soli\Di\Container
     */
    public function getDi()
    {
        if ($this->container === null) {
            $this->container = Container::instance() ?: new Container;
        }
        return $this->container;
    }

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return \Soli\Events\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * 获取容器本身，或者获取容器中的某个服务
     *
     * @param string $name
     * @return \Soli\Di\Container|mixed
     */
    public function __get($name)
    {
        $di = $this->getDi();

        if ($di->has($name)) {
            $service = $di->getShared($name);
            // 将找到的服务添加到属性, 以便下次直接调用
            $this->$name = $service;
            return $service;
        }

        if ($name == 'di') {
            $this->di = $di;
            return $di;
        }

        trigger_error("Access to undefined property $name");
        return null;
    }
}
