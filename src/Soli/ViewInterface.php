<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 视图接口
 */
interface ViewInterface
{
    /**
     * 获取视图目录
     *
     * @return string
     */
    public function getViewsDir();

    /**
     * 设置视图目录
     *
     * @param string $viewsDir 视图文件目录
     */
    public function setViewsDir($viewsDir);

    /**
     * 设置一个视图变量
     *
     * @param string $name
     * @param mixed $value
     */
    public function setVar($name, $value);

    /**
     * Render
     *
     * @param string $path
     * @return string
     */
    public function render($path);

    /**
     * 是否自动渲染视图
     *
     * @return bool
     */
    public function isDisabled();
}
