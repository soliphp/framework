<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\View\Engine;

use Soli\View\Engine;
use Soli\View\EngineInterface;

/**
 * Simple Engine
 *
 * @property null $engine
 */
class Simple extends Engine implements EngineInterface
{
    /**
     * Render
     *
     * @param string $path
     * @param array $vars
     * @return string
     * @throws \Exception
     */
    public function render($path, array $vars = null)
    {
        $template = $this->view->getViewsDir() . $path;
        if (!is_file($template)) {
            throw new \InvalidArgumentException("Template file not found: $template.");
        }

        // 设置视图变量
        if (!empty($vars)) {
            extract($vars);
        }

        // 渲染视图
        ob_start();
        require $template;
        return ob_get_clean();
    }
}
