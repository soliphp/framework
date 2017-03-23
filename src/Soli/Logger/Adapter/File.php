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
        if (is_string($data)) {
            return $data;
        }

        if (is_object($data)) {
            if (method_exists($data, '__toString')) {
                return (string)$data;
            }

            if ($data instanceof JsonSerializable) {
                return json_encode($data);
            }
        }

        return print_r($data, true);
    }
}
