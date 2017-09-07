<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

use Closure;

/**
 * 事件管理器
 *
 * 事件管理器的目的是为了通过创建"钩子"拦截框架或应用中的部分组件操作。
 * 这些钩子允许开发者获得状态信息，操纵数据或者改变某个组件进程中的执行流向。
 *
 *<pre>
 * use Soli\Events\EventManager;
 * use Soli\Events\Event;
 *
 * $eventManager = new EventManager();
 *
 * // 注册具体的某个事件监听器
 * $eventManager->attach('application.boot', function (Event $event, $application) {
 *     echo "应用已启动\n";
 * });
 *
 * // 也可以将针对 "application" 的事件统一整理到 AppEvents 类，一并注册
 * $eventManager->attach('application', new AppEvents);
 *
 * // 触发某个具体事件
 * $eventManager->trigger('application.boot', $this);
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
     * 注册某个事件的监听器
     *
     * @param string $name 完整的事件名称格式为 "事件空间.事件名称"
     *                     这里可以是事件空间，也可以是完整的事件名称
     * @param object $listener 监听器（匿名函数、对象实例）
     */
    public function attach($name, $listener)
    {
        // 追加到事件队列
        $this->events[$name][] = $listener;
    }

    /**
     * 移除某个事件的监听器
     *
     * @param string $name
     * @param object $listener 监听器（匿名函数、对象实例）
     */
    public function detach($name, $listener)
    {
        if (isset($this->events[$name])) {
            $key = array_search($listener, $this->events[$name], true);
            if ($key !== false) {
                unset($this->events[$name][$key]);
            }
        }
    }

    /**
     * 触发事件
     *
     *<code>
     * $eventManager->trigger('dispatch.beforeDispatchLoop', $dispatcher);
     *
     * $event = new Event('application.boot', $app);
     * $eventManager->trigger($event);
     *</code>
     *
     * @param string|EventInterface $event 事件名称或事件对象实例
     * @param object|string $target 事件来源
     * @param mixed $data 事件相关数据
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function trigger($event, $target = null, $data = null)
    {
        if (!is_array($this->events)) {
            return null;
        }

        if (is_object($event) && $event instanceof EventInterface) {
            $name = $event->getName();
        } elseif (is_string($event) && strpos($event, Event::DELIMITER)) {
            $name = $event;
            $event = null;
        } else {
            throw new \InvalidArgumentException('Invalid event type');
        }

        // 事件空间.事件名称
        list($eventSpace, $eventName) = explode(Event::DELIMITER, $name);

        // 事件监听队列中最后一个监听器的执行状态
        $status = null;

        // 以事件空间添加的事件
        if (isset($this->events[$eventSpace])) {
            // 未传入 Event 实例，实例化一个
            if ($event === null) {
                $event = new Event($name, $target, $data);
            }
            $status = $this->notify($this->events[$eventSpace], $event);
        }

        // 以具体的事件名称添加的事件
        if (isset($this->events[$name])) {
            // 在上一步事件空间的判断中没有实例化过 Event，才进行实例化
            if ($event === null) {
                $event = new Event($name, $target, $data);
            }
            // 通知事件监听者
            $status = $this->notify($this->events[$name], $event);
        }

        return $status;
    }

    /**
     * 触发事件监听队列
     *
     * @param array $queue
     * @param EventInterface $event
     * @return mixed
     */
    protected function notify(array $queue, EventInterface $event)
    {
        // 事件监听队列中最后一个监听器的执行状态
        $status = null;

        $name = $event->getName();
        $target = $event->getTarget();
        $data = $event->getData();

        // 事件空间.事件名称
        list($eventSpace, $eventName) = explode(Event::DELIMITER, $name);

        foreach ($queue as $listener) {
            if ($listener instanceof Closure) {
                // 调用闭包监听器
                $status = call_user_func_array($listener, [$event, $target, $data]);
            } elseif (method_exists($listener, $eventName)) {
                // 调用对象监听器
                $status = $listener->{$eventName}($event, $target, $data);
            }

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $status;
    }

    /**
     * 清除某个事件的监听器列表
     *
     * @param string $name
     * @return void
     */
    public function clearListeners($name)
    {
        if (is_array($this->events) && isset($this->events[$name])) {
            unset($this->events[$name]);
        }
    }

    /**
     * 获取某个事件的监听器列表
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
