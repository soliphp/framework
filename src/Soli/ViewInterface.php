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
     * 设置视图目录
     *
     * @param string $viewsDir 视图文件目录
     */
    public function setViewsDir($viewsDir);

    /**
     * 获取视图目录
     *
     * @return string
     */
    public function getViewsDir();

    /**
     * 设置视图文件扩展名
     *
     * @param string $ext
     */
    public function setViewExtension($ext);

    /**
     * 获取视图文件扩展名
     */
    public function getViewExtension();

    /**
     * 设置一个视图变量
     *
     * @param string $name
     * @param mixed $value
     */
    public function setVar($name, $value);

    /**
     * 获取一个视图变量
     *
     * @param string $name
     */
    public function getVar($name);

    /**
     * 设置多个视图变量
     *
     * @param array $vars
     * @param bool $merge 是否合并已有的视图变量
     */
    public function setVars(array $vars, $merge = true);

    /**
     * 获取当前设置的视图变量
     */
    public function getVars();

    /**
     * 启用自动渲染视图
     */
    public function enable();

    /**
     * 禁用自动渲染视图
     */
    public function disable();

    /**
     * 是否自动渲染视图
     *
     * @return bool
     */
    public function isDisabled();

    /**
     * Render
     *
     * @param string $path
     * @return string
     */
    public function render($path);
}
