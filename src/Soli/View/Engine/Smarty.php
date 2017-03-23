<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\View\Engine;

use Soli\View\Engine;
use Soli\View\EngineInterface;
use Soli\ViewInterface;

/**
 * Smarty Engine, Smarty 3.1+
 *
 * @property \Smarty $engine
 */
class Smarty extends Engine implements EngineInterface
{
    /**
     * Smarty constructor.
     *
     * @see http://www.smarty.net/docs/zh_CN/caching.custom.tpl
     * @see https://github.com/smarty-php/smarty/tree/master/demo/plugins
     *
     * @param \Soli\ViewInterface $view
     */
    public function __construct(ViewInterface $view)
    {
        $smarty = new \Smarty();

        $smarty->caching_type = 'file';
        $smarty->debugging = false;
        $smarty->caching = true;
        $smarty->cache_lifetime = 86400;

        $smarty->left_delimiter = '{{';
        $smarty->right_delimiter = '}}';

        $smarty->setTemplateDir($view->getViewsDir());

        $this->engine = $smarty;

        parent::__construct($view);
    }

    /**
     * 是否开启 debug
     *
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->engine->debugging = (bool)$debug;
    }

    /**
     * 设置配置项
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'template_dir':
                    $this->engine->setTemplateDir($value);
                    break;
                case 'compile_dir':
                    $this->engine->setCompileDir($value);
                    break;
                case 'plugins_dir':
                    $this->engine->setPluginsDir($value);
                    break;
                case 'cache_dir':
                    $this->engine->setCacheDir($value);
                    break;
                case 'config_dir':
                    $this->engine->setConfigDir($value);
                    break;
                default:
                    $this->engine->$key = $value;
                    break;
            }
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
        if (!empty($vars)) {
            $this->engine->assign($vars);
        }

        return $this->engine->fetch($path);
    }
}
