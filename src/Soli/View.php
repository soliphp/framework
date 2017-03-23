<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

use Closure;
use Soli\View\EngineInterface;

/**
 * 视图
 */
class View implements ViewInterface
{
    /** @var \Soli\View\Engine $engine 模版引擎实例 */
    protected $engine;

    /** @var \Soli\View\Engine|Closure $engine 模版引擎定义 */
    protected $engineDefinition;

    /** @var string $viewsDir 视图文件目录 */
    protected $viewsDir;

    /** @var string $viewExtension 视图文件扩展名 */
    protected $viewExtension = '.tpl';

    /** @var array $viewParams 视图参数 */
    protected $viewVars;

    /** @var bool $disabled 是否自动渲染视图 */
    protected $disabled = false;

    /**
     * 获取视图目录
     *
     * @return string
     */
    public function getViewsDir()
    {
        return $this->viewsDir;
    }

    /**
     * 设置视图目录
     *
     * @param string $viewsDir 视图文件目录
     */
    public function setViewsDir($viewsDir)
    {
        $this->viewsDir = $viewsDir;
    }

    /**
     * 获取视图文件扩展名
     */
    public function getViewExtension()
    {
        return $this->viewExtension;
    }

    /**
     * 设置视图文件扩展名
     *
     * @param string $ext
     */
    public function setViewExtension($ext)
    {
        $this->viewExtension = $ext;
    }

    /**
     * 设置一个视图变量
     *
     * @param string $name
     * @param mixed $value
     */
    public function setVar($name, $value)
    {
        $this->viewVars[$name] = $value;
    }

    /**
     * 获取一个视图变量
     *
     * @param string $name
     * @return mixed
     */
    public function getVar($name)
    {
        if (isset($this->viewVars[$name])) {
            return $this->viewVars[$name];
        }
        return null;
    }

    /**
     * 设置多个视图变量
     *
     * @param array $vars
     * @param bool $merge 是否合并已有的视图变量
     */
    public function setVars(array $vars, $merge = true)
    {
        if ($merge) {
            $viewVars = $this->viewVars;
            if (is_array($viewVars)) {
                $this->viewVars = array_merge($viewVars, $vars);
            } else {
                $this->viewVars = $vars;
            }
        } else {
            $this->viewVars = $vars;
        }
    }

    /**
     * 获取当前设置的视图变量
     */
    public function getVars()
    {
        return $this->viewVars;
    }

    /**
     * 禁用自动渲染视图
     */
    public function disable()
    {
        $this->disabled = true;
    }

    /**
     * 启用自动渲染视图
     */
    public function enable()
    {
        $this->disabled = false;
    }

    /**
     * 是否自动渲染视图
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * 设置模版引擎
     *
     * 如果 $engineDefinition 是一个 Closure 调用后的返回结果也必须
     * 是一个实现了 EngineInterface 的实例
     *
     * @param \Soli\View\EngineInterface|Closure $engineDefinition 模版引擎定义
     */
    public function setEngine($engineDefinition)
    {
        $this->engineDefinition = $engineDefinition;
    }

    /**
     * 从服务定义中解析实例
     */
    public function getEngine()
    {
        if ($this->engine === null) {
            $definition = $this->engineDefinition;

            if ($definition instanceof Closure) {
                $definition = call_user_func($definition);
            }

            if ($definition instanceof EngineInterface) {
                $this->engine = $definition;
            }

            if ($this->engine === null) {
                throw new \Exception("Engine must be an instance of \Soli\View\EngineInterface");
            }
        }

        return $this->engine;
    }

    /**
     * Render
     *
     * @param string $path
     * @return string
     */
    public function render($path)
    {
        return $this->getEngine()->render(
            $path . $this->getViewExtension(),
            $this->viewVars
        );
    }

    public function __set($name, $value)
    {
        $this->setVar($name, $value);
    }

    public function __get($name)
    {
        return $this->getVar($name);
    }

    public function __isset($name)
    {
        return isset($this->viewVars[$name]);
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
        return call_user_func_array([$this->getEngine(), $name], $parameters);
    }
}
