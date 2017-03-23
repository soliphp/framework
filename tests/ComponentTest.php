<?php

namespace Soli\Tests;

use Soli\Tests\TestCase;
use Soli\Di\Container;
use Soli\Component;

class ComponentTest extends TestCase
{
    protected $di;

    public function setUp()
    {
        $di = new Container;
        $di->remove('some_service');

        $ao = new \ArrayObject;
        $ao->name = 'Injectable';
        $di['some_service'] = $ao;

        $this->di = $di;
    }

    public function testInjectionAware()
    {
        $myComponent = $this->di->getShared('Soli\Tests\MyComponent');

        // 获取容器
        $di = $myComponent->di;
        // 获取容器中的服务
        $s = $myComponent->some_service;

        $this->assertInstanceOf(Container::class, $di);
        $this->assertEquals('Injectable', $s->name);
    }
}

class MyComponent extends Component
{
}
