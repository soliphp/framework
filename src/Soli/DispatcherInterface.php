<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 调度器
 */
interface DispatcherInterface
{
    /**
     * 执行调度
     */
    public function dispatch();

    /**
     * 无需 redirect 跳转，而直接调用对应的 Handler->Action
     *
     * @param array $forward {
     *   @var string namespace
     *   @var string handler
     *   @var string action
     *   @var array  params
     * }
     */
    public function forward(array $forward);

    public function setNamespaceName(string $namespaceName);

    public function getNamespaceName(): string;

    public function setHandlerName(string $handlerName);

    public function getHandlerName(): string;

    public function setActionName(string $actionName);

    public function getActionName(): string;

    public function setParams(array $params);

    public function getParams(): array;
}
