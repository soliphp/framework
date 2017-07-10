<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Session;

use Soli\Di\Container;
use Soli\Di\ContainerAwareInterface;
use Soli\Session;

/**
 * 闪存消息
 */
class Flash implements ContainerAwareInterface
{
    protected $cssClasses = [
        'error'   => 'error',
        'notice'  => 'notice',
        'success' => 'success',
        'warning' => 'warning'
    ];

    protected $messages;

    protected $flashKey = '__flashMessages';

    /**
     * @var \Soli\Di\Container
     */
    protected $di;

    /**
     * Flash constructor.
     *
     * @param array $cssClasses 消息样式
     */
    public function __construct(array $cssClasses = null)
    {
        if (is_array($cssClasses)) {
            $this->setCssClasses($cssClasses);
        }
    }

    public function setDi(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @return \Soli\Di\Container
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * 设置消息样式
     *
     * @param array $cssClasses
     */
    public function setCssClasses(array $cssClasses)
    {
        $this->cssClasses = array_merge($this->cssClasses, $cssClasses);
    }

    /**
     * @return array
     */
    public function getCssClasses()
    {
        return $this->cssClasses;
    }

    public function error($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    public function notice($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    public function success($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    public function warning($message)
    {
        $this->message(__FUNCTION__, $message);
    }

    /**
     * 处理各个类型的 flash message
     *
     * @param string $type success|error|notice|warning
     * @param string $message
     */
    public function message($type, $message)
    {
        if (isset($this->cssClasses[$type])) {
            $session = $this->di->getShared('session');
            $html = '<div class="%s">%s</div>';
            $this->messages[] = sprintf($html, $this->cssClasses[$type], $message);
            $session->set($this->flashKey, $this->messages);
        }
    }

    /**
     * 输出 flash messages
     *
     * @param bool $remove 输出后是否删除 flash messages
     */
    public function output($remove = true)
    {
        $remove = (bool)$remove;
        /** @var \Soli\Session $session */
        $session = $this->di->getShared('session');
        $messages = $session->get($this->flashKey, [], $remove);
        if (!empty($messages)) {
            foreach ($messages as $message) {
                echo $message;
            }
        }
        if ($remove) {
            $this->clear();
        }
    }

    /**
     * 清空 flash messages
     * 在同一次请求中，要清除已经设置的 messages 并且要设置新的 messages 时会有用
     */
    public function clear()
    {
        $this->messages = [];
    }
}
