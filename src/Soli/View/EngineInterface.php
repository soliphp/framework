<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\View;

/**
 * 模版引擎接口
 */
interface EngineInterface
{
    /**
     * 获取视图渲染后的结果
     *
     * @param string $path 视图文件路径
     * @param array $vars
     * @return string
     */
    public function render($path, array $vars = null);
}
