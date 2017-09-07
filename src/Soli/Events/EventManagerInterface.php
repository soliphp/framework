<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

/**
 * 事件管理器接口
 */
interface EventManagerInterface
{
    /**
     * 注册某个事件的监听器
     *
     * @param string $name 事件名称
     * @param object $listener 监听器
     */
    public function attach($name, $listener);

    /**
     * 移除某个事件的监听器
     *
     * @param string $name 事件名称
     * @param object $listener 监听器
     */
    public function detach($name, $listener);

    /**
     * 触发事件
     *
     * 可以接受一个 EventInterface 实例，如果没有传就会创建一个
     *
     * @param string|EventInterface $event 事件名称或事件对象实例
     * @param object|string $target 事件来源
     * @param mixed $data 事件相关数据
     * @return mixed
     */
    public function trigger($event, $target = null, $data = null);
}
