<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 自动加载器
 *
 *<pre>
 * // Creates the autoloader
 * $loader = new Loader();
 *
 * // Register some namespaces
 * $loader->registerNamespaces([
 *   'Example\Base' => 'vendor/example/base/',
 *   'Example\Adapter' => 'vendor/example/adapter/',
 *   'Example' => 'vendor/example/'
 * ]);
 *
 * // register autoloader
 * $loader->register();
 *
 * // Requiring $this class will automatically include file vendor/example/adapter/Some.php
 * $adapter = Example\Adapter\Some();
 *</pre>
 */
class Loader
{
    /**
     * 当前自动加载的类名
     */
    protected $currentClassName = null;

    /**
     * 当前自动加载的类文件路径
     */
    protected $foundPath = null;

    /**
     * 已注册的类文件
     */
    protected $classes = null;

    /**
     * 已注册的命名空间
     */
    protected $namespaces = null;

    /**
     * 已注册的目录
     */
    protected $directories = null;

    /**
     * 自动加载的类文件后缀
     */
    protected $extension = '.php';

    /**
     * 是否已注册当前类中的自动加载方法
     */
    protected $registered = false;

    /**
     * Loader constructor.
     */
    public function __construct()
    {
        $classmap = __DIR__ . '/classmap.php';
        if (is_file($classmap)) {
            $this->registerClasses(include $classmap);
        }
    }

    /**
     * 注册多个类
     *
     * @param array $classes
     * @param bool $merge
     * @return $this
     */
    public function registerClasses(array $classes, $merge = true)
    {
        if ($merge && is_array($this->classes)) {
            $classes = array_merge($this->classes, $classes);
        }
        $this->classes = $classes;

        return $this;
    }

    /**
     * 注册多个命名空间
     *
     * @param array $namespaces 命名空间与路径的键值对
     * @param bool $merge 是否合并已有命名空间
     * @return $this
     */
    public function registerNamespaces(array $namespaces, $merge = true)
    {
        if ($merge && is_array($this->namespaces)) {
            $namespaces = array_merge($this->namespaces, $namespaces);
        }
        $this->namespaces = $namespaces;

        return $this;
    }

    /**
     * 注册多个目录
     *
     * @param array $directories 目录列表
     * @param bool $merge  是否合并已有目录列表
     * @return $this
     */
    public function registerDirs(array $directories, $merge = true)
    {
        if ($merge && is_array($this->directories)) {
            $directories = array_merge($this->directories, $directories);
        }
        $this->directories = $directories;

        return $this;
    }

    /**
     * 注册自动加载方法
     */
    public function register()
    {
        if ($this->registered === false) {
            // 可以使用 spl_autoload_functions() 打印已经注册的自动加载函数列表
            spl_autoload_register([$this, 'autoload'], true, true);
            $this->registered = true;
        }
        return $this;
    }

    /**
     * 注销自动加载方法
     */
    public function unregister()
    {
        if ($this->registered === true) {
            spl_autoload_unregister([$this, 'autoload']);
            $this->registered = false;
        }
        return $this;
    }

    /**
     * 自动加载方法
     *
     * @param string $className 类名
     * @return bool
     */
    public function autoload($className)
    {
        // 重置类加载信息
        $this->foundPath = null;
        $this->currentClassName = $className;

        // 从类路径中找
        if (is_array($this->classes) && $this->autoloadFromClasses()) {
            return true;
        }

        // 从命名空间中找
        if (is_array($this->namespaces) && $this->autoloadFromNamespaces()) {
            return true;
        }

        // 从目录中找
        if (is_array($this->directories) && $this->autoloadFromDirs()) {
            return true;
        }

        return false;
    }

    /**
     * 获取当前自动加载的类的文件路径，未找到则为 null
     *
     * @return null|string
     */
    public function getFoundPath()
    {
        return $this->foundPath;
    }

    /**
     * 获取自动加载的类文件列表
     *
     * @return null|array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * 获取自动加载的命名空间列表
     *
     * @return null|array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * 获取自动加载的目录列表
     *
     * @return null|array
     */
    public function getDirs()
    {
        return $this->directories;
    }

    /**
     * 从已注册的类路径中查找类文件
     */
    protected function autoloadFromClasses()
    {
        $className = $this->currentClassName;
        $classes = $this->classes;
        if (isset($classes[$className])) {
            $this->foundPath = $classes[$className];
            require $this->foundPath;
            return true;
        }
        return false;
    }

    /**
     * 从已注册的命名空间中查找类文件
     */
    protected function autoloadFromNamespaces()
    {
        $className = $this->currentClassName;
        foreach ($this->namespaces as $nsPrefix => $directory) {
            // 类名称以当前命名空间开头
            if (strpos($className, $nsPrefix) === 0) {
                // 去除前缀
                $fileName = substr($className, strlen($nsPrefix . "\\"));
                $fileName = str_replace("\\", DIRECTORY_SEPARATOR, $fileName);

                if ($fileName) {
                    // fixed directory
                    $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                    $filePath = $directory . $fileName . $this->extension;

                    if (is_file($filePath)) {
                        $this->foundPath = $filePath;
                        require $this->foundPath;
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * 从已注册的目录中查找类文件
     */
    protected function autoloadFromDirs()
    {
        $className = $this->currentClassName;
        // 允许使用下划线 "_" 作为命名空间分隔符, 不可以是已注册的命名空间
        $className = str_replace('_', DIRECTORY_SEPARATOR, $className);
        $fileName = str_replace("\\", DIRECTORY_SEPARATOR, $className);

        foreach ($this->directories as $directory) {
            // fixed directory
            $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $filePath = $directory . $fileName . $this->extension;

            if (is_file($filePath)) {
                $this->foundPath = $filePath;
                require $this->foundPath;
                return true;
            }
        }
        return false;
    }
}
