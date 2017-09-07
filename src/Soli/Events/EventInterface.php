<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Events;

/**
 * 事件接口
 */
interface EventInterface
{
    /**
     * Get event name
     *
     * @return string
     */
    public function getName();

    /**
     * Get target/context from which event was triggered
     *
     * @return object|string|null
     */
    public function getTarget();

    /**
     * Get parameters passed to the event
     *
     * @return mixed
     */
    public function getData();

    /**
     * Set the event name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Set the event target
     *
     * @param object|string $target
     */
    public function setTarget($target);

    /**
     * Set event data
     *
     * @param array $data
     */
    public function setData($data);

    /**
     * Indicate whether or not to stop propagating this event
     */
    public function stopPropagation();

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped();
}
