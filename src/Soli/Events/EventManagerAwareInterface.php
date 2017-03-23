<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

/**
 * 事件管理器感知接口
 */
interface EventManagerAwareInterface
{
    /**
     * 设置事件管理器
     *
     * @param \Soli\Events\EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager);

    /**
     * 获取事件管理器
     *
     * @return \Soli\Events\EventManager
     */
    public function getEventManager();
}
