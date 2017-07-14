<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\View;

use Soli\ViewInterface;

/**
 * 模版引擎抽象类
 */
abstract class Engine
{
    /** @var object $engine 模版引擎实例 */
    protected $engine;

    /** @var \Soli\ViewInterface $view 视图实例 */
    protected $view;

    /**
     * Engine constructor.
     *
     * @param \Soli\ViewInterface $view
     */
    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }

    /**
     * 调用 Engine 实例中的方法
     *
     * @param string $name 方法名
     * @param array $parameters 参数
     * @return mixed
     */
    public function __call($name, $parameters)
    {
        return call_user_func_array([$this->engine, $name], $parameters);
    }
}
