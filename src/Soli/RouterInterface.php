<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

interface RouterInterface
{
    public function getNamespaceName(): string;

    public function getHandlerName(): string;

    public function getActionName(): string;

    public function getParams(): array;

    public function dispatch($argv = null);
}
