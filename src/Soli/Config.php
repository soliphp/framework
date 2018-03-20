<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * Config.
 *
 *<code>
 * $config = new \Soli\Config($arrayConfig);
 *</code>
 */
class Config implements \ArrayAccess, \Countable
{
    /**
     * Config constructor.
     */
    public function __construct(array $arrayConfig = null)
    {
        if (!empty($arrayConfig)) {
            foreach ($arrayConfig as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
    }

    /**
     * var_dump(isset($config['database']));
     */
    public function offsetExists($index)
    {
        return isset($this->{$index});
    }

    /**
     * var_dump($config['database']);
     */
    public function offsetGet($index)
    {
        return $this->{$index};
    }

    /**
     * $config['database'] = [
     *     'host' => '127.0.0.1',
     *     'port' => '3306',
     * ]
     */
    public function offsetSet($index, $value)
    {
        if (is_array($value)) {
            $this->{$index} = new self($value);
        } else {
            $this->{$index} = $value;
        }
    }

    /**
     * unset($config['cacheDir']);
     */
    public function offsetUnset($index)
    {
        $this->{$index} = null;
    }

    /**
     * echo count($config);
     * echo $config->count();
     */
    public function count()
    {
        return count(get_object_vars($this));
    }

    /**
     * $config->set('database', [
     *     'host' => '127.0.0.1',
     *     'port' => '3306',
     * ]);
     * $config->set('database.host', '192.168.1.100');
     * $config->set('database.dbname', 'demo');
     */
    public function set($index, $value)
    {
        $config = $this;
        $keys = explode('.', $index);

        foreach ($keys as $key) {
            if (!isset($config->{$key})) {
                $config->{$key} = new self();
            }

            $config = &$config->{$key};
        }

        if (is_array($value)) {
            $config = new self($value);
        } else {
            $config = $value;
        }
    }

    /**
     * print_r($config->get('database'));
     * print_r($config->get('database.host', '192.168.1.100'));
     */
    public function get($index, $defaultValue = null)
    {
        if (isset($this->{$index})) {
            return $this->{$index};
        }

        $config = $this;
        $keys = explode('.', $index);

        while (!empty($keys)) {
            $key = array_shift($keys);

            if (!isset($config->{$key})) {
                break;
            }

            if (empty($keys)) {
                return $config->{$key};
            }

            $config = $config->{$key};

            if (empty($config)) {
                break;
            }
        }

        return $defaultValue;
    }

    /**
     * var_dump($config->toArray());
     */
    public function toArray()
    {
        $arrayConfig = [];
        foreach (get_object_vars($this) as $key => $value) {
            if (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $arrayConfig[$key] = $value->toArray();
                } else {
                    $arrayConfig[$key] = $value;
                }
            } else {
                $arrayConfig[$key] = $value;
            }
        }
        return $arrayConfig;
    }

    /**
     * var_export($config);
     */
    public static function __set_state($data)
    {
        return new self($data);
    }
}
