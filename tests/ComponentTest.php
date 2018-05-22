<?php

namespace Soli\Tests;

use Soli\Di\ContainerInterface;

use Soli\Tests\Data\AComponent;

class ComponentTest extends TestCase
{
    public function setUp()
    {
        $container = static::$container;
        $container->remove('someService');

        $ao = new \ArrayObject();
        $ao->name = 'Injectable';
        $container['someService'] = $ao;
    }

    public function testInjectionAware()
    {
        $aComponent = static::$container->get(AComponent::class);

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
        $aComponent = static::$container->get(AComponent::class);
        $aComponent->undefinedProperty;
    }

    public function testUndefinedPropertyReturnFalse()
    {
        error_reporting(0);
        $aComponent = static::$container->get(AComponent::class);
        $this->assertNull($aComponent->undefinedProperty);
    }
}
