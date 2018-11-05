<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Component;
use Soli\Di\Container;
use Soli\Di\ContainerInterface;

use Soli\Events\Event;
use Soli\Events\EventManager;

class ComponentTest extends TestCase
{
    /** @var \Soli\Di\ContainerInterface */
    protected static $container;

    public static function setUpBeforeClass()
    {
        static::$container = new Container();
    }

    public static function tearDownAfterClass()
    {
        static::$container = null;
    }

    public function testInjectionAware()
    {
        $component = static::$container->get(Component::class);

        // 获取容器
        $container = $component->getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testGetSomeServiceInContainer()
    {
        $container = static::$container;

        $component = $container->get(Component::class);

        $container->set('someService', function () {
            $someService = new \ArrayObject();
            $someService->name = 'Injectable';
            return $someService;
        });

        // 获取容器中的服务
        $someService = $component->someService;
        $this->assertEquals('Injectable', $someService->name);
    }

    public function testGetContainer()
    {
        $component = new Component();
        $container = $component->getContainer();

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testAccessContainerProperty()
    {
        $component = static::$container->get(Component::class);
        $this->assertInstanceOf(ContainerInterface::class, $component->container);
    }

    public function testEvents()
    {
        $uid = 100;
        $this->expectOutputString($uid);

        $container = static::$container;
        $container->set('events', EventManager::class);

        $user = new class() extends Component {
            const ON_LOGIN = 'login';
            public function __construct()
            {
                // 监听登录事件
                $this->listen(self::ON_LOGIN, function (Event $event) {
                    echo $event->getData();
                });
            }
            public function login($uid)
            {
                // 触发登录事件
                $this->trigger(self::ON_LOGIN, $uid);
            }
        };

        $user->login($uid);
    }

    /**
     * @expectedException \PHPUnit\Framework\Exception
     * @expectedExceptionMessageRegExp /Access to undefined property .+/
     */
    public function testUndefinedPropertyException()
    {
        $component = static::$container->get(Component::class);
        $component->undefinedProperty;
    }

    public function testUndefinedPropertyReturnFalse()
    {
        error_reporting(0);
        $component = static::$container->get(component::class);
        $this->assertNull($component->undefinedProperty);
    }
}
