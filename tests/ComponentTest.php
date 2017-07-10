<?php

namespace Soli\Tests;

use Soli\Tests\TestCase;
use Soli\Di\Container;
use Soli\Component;

class ComponentTest extends TestCase
{
    protected $container;

    public function setUp()
    {
        $container = new Container;
        $container->remove('some_service');

        $ao = new \ArrayObject;
        $ao->name = 'Injectable';
        $container['some_service'] = $ao;

        $this->container = $container;
    }

    public function testInjectionAware()
    {
        $myComponent = $this->container->getShared('Soli\Tests\MyComponent');

        // 获取容器
        $container = $myComponent->container;
        // 获取容器中的服务
        $s = $myComponent->some_service;

        $this->assertInstanceOf(Container::class, $container);
        $this->assertEquals('Injectable', $s->name);
    }
}

class MyComponent extends Component
{
}
