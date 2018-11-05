<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

trait RouterTrait
{
    protected $defaultNamespaceName = null;
    protected $defaultHandlerName = 'index';
    protected $defaultActionName = 'index';
    protected $defaultParams = [];

    protected $namespaceName = null;
    protected $handlerName = null;
    protected $actionName = null;
    protected $params = null;

    public function setDefaults(array $defaults)
    {
        if (isset($defaults['namespace'])) {
            $this->defaultNamespaceName = $defaults['namespace'];
        }
        if (isset($defaults['handler'])) {
            $this->defaultHandlerName = $defaults['handler'];
        }
        if (isset($defaults['action'])) {
            $this->defaultActionName = $defaults['action'];
        }
        if (isset($defaults['params'])) {
            $this->defaultParams = $defaults['params'];
        }

        return $this;
    }

    public function getNamespaceName(): string
    {
        return $this->namespaceName ?? $this->defaultNamespaceName;
    }

    public function getHandlerName(): string
    {
        return $this->handlerName ?? $this->defaultHandlerName;
    }

    public function getActionName(): string
    {
        return $this->actionName ?? $this->defaultActionName;
    }

    public function getParams(): array
    {
        return $this->params ?? $this->defaultParams;
    }
}
