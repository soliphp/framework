<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Logger\Adapter;

use Psr\Log\AbstractLogger;
use JsonSerializable;
use Soli\Exception;

/**
 * 日志记录器
 */
class File extends AbstractLogger
{
    /**
     * 日志文件路径
     *
     * @var string
     */
    protected $path = null;

    /**
     * File constructor.
     *
     * @param string $path 日志文件路径
     * @throws \Soli\Exception
     */
    public function __construct($path)
    {
        if (!is_string($path) || empty($path)) {
            throw new Exception('Invalid parameter path.');
        }

        $this->path = $path;
    }

    /**
     * 获取日志文件路径
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 写入日志
     *
     * @param string $level 日志级别
     * @param mixed $message 日志信息
     * @param array $context
     * @return int|false 成功返回写入文件的字节数，失败返回 false
     * @throws \Soli\Exception
     */
    public function log($level, $message, array $context = [])
    {
        $message = $this->format($message, $context);
        $output = '[' . date('Y-m-d H:i:s') . "] [$level] $message\n";

        if (!is_file($this->path)) {
            // 创建日志目录
            $dirname = dirname($this->path);
            is_dir($dirname) || mkdir($dirname, 0775, true);
        }

        return file_put_contents($this->path, $output, FILE_APPEND);
    }

    /**
     * 格式化日志信息
     *
     * @param mixed $data 日志信息
     * @param array $context
     * @return string
     */
    protected function format($data, array $context = [])
    {
        // 标量
        if ($data === null || is_scalar($data)) {
            return $data;
        }

        if (is_array($data)) {
            // PHP_VERSION >= 5.4.0
            $value = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            return sprintf('[array] (%s)', $value);
        }

        // 对象
        if (is_object($data)) {
            // Exception
            if ($data instanceof \Exception || (PHP_VERSION_ID > 70000 && $data instanceof \Throwable)) {
                return $this->normalizeException($data);
            }

            if (method_exists($data, '__toString') && !($data instanceof JsonSerializable)) {
                $value = (string)$data;
            } else {
                $value = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            return sprintf("[object] (%s: %s)", get_class($data), $value);
        }

        // 资源
        if (is_resource($data)) {
            return sprintf('[resource] (%s)', get_resource_type($data));
        }

        return sprintf('[unknown(%s)] (%s)', gettype($data), print_r($data, true));
    }

    /**
     * @param \Exception|\Throwable $e
     * @return string
     */
    public function normalizeException($e)
    {
        $head = sprintf('%s(%s): %s', $e->getFile(), $e->getLine(), $e->getMessage()) . "\n";
        return $head . $e->getTraceAsString();
    }
}
