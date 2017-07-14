<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

/**
 * 事件管理器
 *
 * 事件管理器的目的是为了通过创建"钩子"拦截框架或应用中的部分组件操作。
 * 这些钩子允许开发者获得状态信息，操纵数据或者改变某个组件进程中的执行流向。
 *
 *<pre>
 * $eventManager = new \Soli\Events\EventManager();
 *
 * // 注册具体的某个事件监听器
 * $eventManager->on('application:boot', function (\Soli\Events\Event $event, $application) {
 *     echo "应用已启动\n";
 * });
 *
 * // 也可以将针对 "application" 的事件统一整理到 AppEvents 类，一并注册
 * $eventManager->on('application', new AppEvents);
 *
 * // 触发某个具体事件
 * $eventManager->fire('application:boot', $this);
 *</pre>
 */
class EventManager implements EventManagerInterface
{
    /**
     * 事件列表
     *
     * @var array
     */
    protected $events;

    /**
     * 注册某个事件的监听器，采用事件分组的方式
     * 添加事件监听的命名规则为：
     * 如果 $name 不含有分号":"则认为是以分组的方式添加
     * 有分号则认为是为具体的某个事件添加监听器
     * 此规则会在 fire 方法中体现
     *
     * @param string $name 事件名称，格式为：「事件分组类型:事件名称」
     *                     可以是事件分组类型，也可以是完整的事件名称
     * @param \Closure|object $listener 监听器（匿名函数、对象实例）
     */
    public function on($name, $listener)
    {
        // 追加到事件队列
        $this->events[$name][] = $listener;
    }

    /**
     * 移除某个事件的监听器
     *
     * @param string $name
     */
    public function off($name)
    {
        if (isset($this->events[$name])) {
            unset($this->events[$name]);
        }
    }

    /**
     * 激活某个事件的监听器
     *
     *<code>
     *  $eventManager->fire('dispatch:beforeDispatchLoop', $dispatcher);
     *</code>
     *
     * @param string $name 具体的某个事件名称，格式为： 事件分组类型:事件名称
     * @param object $source 事件来源
     * @param mixed $data 事件相关数据
     * @return mixed
     * @throws \Exception
     */
    public function fire($name, $source, $data = null)
    {
        if (!is_array($this->events)) {
            return null;
        }

        // 含有分号":"且不以分号开头，必须要指定具体调用的哪个事件
        if (!strpos($name, ':')) {
            throw new \Exception('Invalid event type ' . $name);
        }

        // 事件空间:事件名称
        list($eventSpace, $eventName) = explode(':', $name);

        // 事件监听队列中最后一个监听器的执行状态
        $status = null;
        // Event 实例
        $event = null;

        // 以事件分组类型添加的事件
        if (isset($this->events[$eventSpace])) {
            $event = new Event($eventName, $source, $data);
            $status = $event->fire($this->events[$eventSpace]);
        }

        // 以具体的事件名称添加的事件
        if (isset($this->events[$name])) {
            // 在上一步事件分组类型的判断中没有实例化过 Event，才进行实例化
            if ($event === null) {
                $event = new Event($eventName, $source, $data);
            }
            // 调用事件队列
            $status = $event->fire($this->events[$name]);
        }

        return $status;
    }

    /**
     * 检查某个事件是否已注册监听器
     *
     * @param string $name
     * @return bool
     */
    public function hasListeners($name)
    {
        return is_array($this->events) && isset($this->events[$name]);
    }

    /**
     * 获取某个已知事件的监听器列表
     *
     * @param string $name
     * @return array
     */
    public function getListeners($name)
    {
        if (is_array($this->events) && isset($this->events[$name])) {
            return $this->events[$name];
        }

        return [];
    }
}
