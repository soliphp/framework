<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\View\Engine;

use Soli\View\Engine;
use Soli\View\EngineInterface;
use Soli\ViewInterface;

/**
 * Twig Engine
 *
 * @property \Twig_Environment $engine
 */
class Twig extends Engine implements EngineInterface
{
    /**
     * Twig constructor.
     *
     * @param \Soli\ViewInterface $view
     */
    public function __construct(ViewInterface $view)
    {
        $loader = new \Twig_Loader_Filesystem();
        $loader->setPaths($view->getViewsDir());

        $twig = new \Twig_Environment($loader);
        $this->engine = $twig;

        parent::__construct($view);
    }

    /**
     * 是否开启 debug, 开启 debug 每次都不会缓存
     *
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        if ($debug) {
            $this->engine->enableDebug();
            // 自动加载变动文件内容
            $this->engine->enableAutoReload();
            // 不进行缓存
            $this->setCacheDir(false);
            $this->engine->addExtension(new \Twig_Extension_Debug());
        } else {
            $this->engine->disableDebug();
            $this->engine->disableAutoReload();
        }
    }

    /**
     * 设置缓存路径
     *
     * @param string $cache
     */
    public function setCacheDir($cache)
    {
        if (!$this->engine->isDebug()) {
            $this->engine->setCache($cache);
        }
    }

    /**
     * Render
     *
     * @param string $path
     * @param array $vars
     * @return string
     */
    public function render($path, array $vars = null)
    {
        return $this->engine->render($path, (array)$vars);
    }
}
