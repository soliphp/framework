<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

/**
 * EventManagerAwareTrait
 */
trait EventManagerAwareTrait
{
    /**
     * @var \Soli\Events\EventManagerInterface
     */
    protected $eventManager;

    /**
     * 设置事件管理器
     *
     * @param \Soli\Events\EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * 获取事件管理器
     *
     * @return \Soli\Events\EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }
}
