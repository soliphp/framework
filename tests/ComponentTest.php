<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Di\ContainerInterface;

use Soli\Tests\Data\AComponent;

class ComponentTest extends TestCase
{
    /** @var \Soli\Di\ContainerInterface */
    protected $container;

    public function setUp()
    {
        $container = new Container();
        $container->remove('someService');

        $ao = new \ArrayObject();
        $ao->name = 'Injectable';
        $container['someService'] = $ao;

        $this->container = $container;
    }

    public function testInjectionAware()
    {
        $aComponent = $this->container->getShared(AComponent::class);

        // 获取容器
        $container = $aComponent->container;
        // 获取容器中的服务
        $s = $aComponent->someService;

        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertEquals('Injectable', $s->name);
    }

    /**
     * @expectedException \PHPUnit\Framework\Exception
     * @expectedExceptionMessageRegExp /Access to undefined property .+/
     */
    public function testUndefinedPropertyException()
    {
        $aComponent = $this->container->getShared(AComponent::class);
        $aComponent->undefinedProperty;
    }

    public function testUndefinedPropertyReturnFalse()
    {
        error_reporting(0);
        $aComponent = $this->container->getShared(AComponent::class);
        $this->assertNull($aComponent->undefinedProperty);
    }
}
