<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Di;

/**
 * 依赖注入容器
 *
 * 依赖注入容器的目的为了降低代码的耦合度，提高应用的可维护性。
 * 把组件之间的依赖，转换为对容器的依赖，通过容器
 * 进行服务管理(创建、配置和定位)。
 */
class Container implements ContainerInterface, \ArrayAccess
{
    /**
     * 存储容器对象实例
     *
     * @var \Soli\Di\ContainerInterface
     */
    public static $instance;

    /**
     * 存储所有注册的服务
     *
     * @var \Soli\Di\ServiceInterface[]
     */
    protected static $services = [];

    /**
     * 存储 getShared 方法返回的服务实例（服务定义的执行结果）
     *
     * @var array
     */
    protected static $sharedInstances = [];

    /**
     * 初始化容器默认实例
     */
    public function __construct()
    {
        if (static::$instance === null) {
            static::$instance = $this;
        }
        return static::$instance;
    }

    /**
     * 获取容器对象实例
     *
     * @return \Soli\Di\ContainerInterface
     */
    public static function instance()
    {
        return static::$instance;
    }

    /**
     * 注册一个服务到容器
     *
     * @param string $id 服务标识
     * @param mixed $definition 服务定义
     * @return \Soli\Di\ServiceInterface
     */
    public function set($id, $definition)
    {
        $service = new Service($id, $definition, false);
        static::$services[$id] = $service;
        return $service;
    }

    /**
     * 注册单例服务
     *
     * @param string $id 服务标识
     * @param mixed $definition 服务定义
     * @return \Soli\Di\ServiceInterface
     */
    public function setShared($id, $definition)
    {
        $service = new Service($id, $definition, true);
        static::$services[$id] = $service;
        return $service;
    }

    /**
     * 从容器中获取一个服务
     *
     * 当传入未注册为服务标识的类名时，自动将类名注册为服务，并返回类实例
     *
     * @param string $id 服务标识|类名
     * @param array $parameters 参数
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($id, array $parameters = null)
    {
        if (isset(static::$services[$id])) {
            /** @var \Soli\Di\ServiceInterface $service 服务实例 */
            $service = static::$services[$id];
        } elseif (class_exists($id)) {
            // 自动将类名注册为服务
            $service = $this->setShared($id, $id);
        } else {
            throw new \InvalidArgumentException("Service '$id' wasn't found in the dependency injection container");
        }

        // 解析服务, 返回服务定义的执行结果
        $instance = $service->resolve($parameters, $this);

        // 当前服务实现了 ContainerAwareInterface 接口时，自动为其设置容器
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this);
        }

        return $instance;
    }

    /**
     * 获取单例服务
     *
     * 当一个服务未被注册为单例服务，使用此方法也可以获取单例服务
     *
     * @param string $id 服务标识
     * @param array $parameters 参数
     * @return mixed
     */
    public function getShared($id, array $parameters = null)
    {
        // 检查是否已解析
        if (isset(static::$sharedInstances[$id])) {
            return static::$sharedInstances[$id];
        }

        // 解析服务实例
        $service = $this->get($id, $parameters);

        // 保存到 shared 实例列表
        static::$sharedInstances[$id] = $service;

        return $service;
    }

    /**
     * 查询容器中是否存在某个服务
     *
     * @param string $id 服务标识
     * @return bool
     */
    public function has($id)
    {
        return isset(static::$services[$id]);
    }

    /**
     * 从服务容器中删除一个服务
     *
     * @param string $id 服务标识
     * @return void
     */
    public function remove($id)
    {
        unset(static::$services[$id]);
        unset(static::$sharedInstances[$id]);
    }

    /**
     * 清空容器
     *
     * @return void
     */
    public function clear()
    {
        static::$services = [];
        static::$sharedInstances = [];
    }

    /**
     * 获取容器中的某个 Service 对象实例
     *
     * @param string $id 服务标识
     * @return \Soli\Di\ServiceInterface
     * @throws \InvalidArgumentException
     */
    public function getService($id)
    {
        if (isset(static::$services[$id])) {
            return static::$services[$id];
        }

        throw new \InvalidArgumentException("Service '$id' wasn't found in the dependency injection container");
    }

    /**
     * 获取容器中的所有服务
     *
     * @return \Soli\Di\ServiceInterface[]
     */
    public function getServices()
    {
        return static::$services;
    }

    // 实现 \ArrayAccess 方法

    public function offsetExists($id)
    {
        return $this->has($id);
    }

    public function offsetGet($id)
    {
        return $this->getShared($id);
    }

    public function offsetSet($id, $definition)
    {
        $this->setShared($id, $definition);
    }

    public function offsetUnset($id)
    {
        $this->remove($id);
    }

    /**
     * 允许将服务标识作为属性名访问
     *
     *<pre>
     * $container->someService;
     *</pre>
     *
     * @param string $id 服务标识
     * @return mixed
     */
    public function __get($id)
    {
        return $this->getShared($id);
    }
}
