<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Http;

use Soli\Di\Container;
use Soli\Di\ContainerAwareInterface;
use Soli\ViewInterface;
use Soli\Exception;

/**
 * 响应
 *
 *<pre>
 * $response = new Response();
 * $response->setStatusCode(200);
 * $response->setContent($content);
 *
 * $cookie = [
 *     'name' => 'hello',
 *     'value' => 'hi cookie',
 *     'expire' => 60,
 * ];
 * $response->setCookies($cookie);
 *
 * $response->setHeaders("Cache-Control: no-cache, must-revalidate");
 *
 * $response->send();
 *</pre>
 */
class Response implements ContainerAwareInterface
{
    /**
     * 是否已发送响应信息
     *
     * @var bool
     */
    protected $isSend = false;

    /**
     * 状态码
     *
     * @var int
     */
    protected $code = 200;

    /**
     * 状态描述
     *
     * @var int
     */
    protected $message;

    /**
     * 响应内容
     *
     * @var string|array
     */
    protected $content = null;

    /**
     * 响应的数据类型, 默认为 html
     *
     * @var string
     */
    protected $contentType = 'html';

    /**
     * 响应头信息
     *
     * @var array
     */
    protected $headers = [];

    /**
     * 响应 cookie 信息
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * 响应文件
     *
     * @var string
     */
    protected $file = null;

    /**
     * @var \Soli\Di\Container
     */
    protected $di;

    /**
     * Response constructor.
     *
     * @param string $content 响应内容
     * @param int $code 状态码
     * @param string $message 状态描述
     */
    public function __construct($content = null, $code = 200, $message = null)
    {
        if ($content !== null) {
            $this->content = $content;
        }
        if ($code !== null) {
            $this->setStatusCode($code, $message);
        }
    }

    public function setDi(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @return \Soli\Di\Container
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * 设置响应状态
     *
     * @param int $code 状态码
     * @param string $message 状态描述
     */
    public function setStatusCode($code = 200, $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }

    /**
     * 获取响应类型
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * 设置响应类型
     *
     * @example
     *   $response->setContentType('application/javascript');
     *
     * @param string $contentType
     * @param string $charset
     */
    public function setContentType($contentType, $charset = null)
    {
        $this->contentType = $contentType;

        if ($charset !== null) {
            $contentType .= "; charset=$charset";
        }
        $this->headers['Content-type'] = $contentType;
    }

    /**
     * 追加响应内容
     *
     * @param string $content
     */
    public function appendContent($content)
    {
        $this->content .= $content;
    }

    /**
     * 获取响应内容
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * 设置响应内容
     *
     * @param string|array|int $content
     */
    public function setContent($content = null)
    {
        $this->content = $content;
    }

    /**
     * 设置 json 响应数据
     *
     * @example
     *   $response->setJsonContent(array('data' => '午休时刻'));
     *
     * @param array $content
     * @param int $jsonOptions
     * @param int $depth
     */
    public function setJsonContent($content, $jsonOptions = 0, $depth = 512)
    {
        $this->setContentType('application/json', 'UTF-8');
        $this->setContent(json_encode($content, $jsonOptions, $depth));
    }

    /**
     * 主动设置响应数据已发送
     *
     * @param bool $isSend 是否已发送
     */
    public function isSend($isSend)
    {
        $this->isSend = (bool)$isSend;
    }

    /**
     * 获取发送的 cookies 信息, 单个或多个 cookie 信息
     *
     * @example
     *   $response->getCookies();
     *   $response->getCookies('cookie_name', null);
     *
     * @param string $name 具体某个 cookie 的名称
     * @param mixed $defaultValue 默认值
     * @return array|mixed|null
     */
    public function getCookies($name = null, $defaultValue = null)
    {
        if (empty($name)) {
            return $this->cookies;
        }
        return isset($this->cookies[$name]) ? $this->cookies[$name] : $defaultValue;
    }

    /**
     * 设置响应的 cookies 信息
     *
     * @param array $options 单个或多个 cookie 信息
     */
    public function setCookies(array $options)
    {
        $default = [
            'name' => '__cookieDefault',
            'value' => '',
            'expire' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httpOnly' => true
        ];

        $item = current($options);
        if (!is_array($item)) {
            $options = [$options];
        }

        foreach ($options as $item) {
            $item = array_merge($default, $item);
            $this->cookies[$item['name']] = $item;
        }
    }

    /**
     * 设置响应头信息
     *
     * @param string|array $header
     * @param string $value
     */
    public function setHeaders($header, $value = null)
    {
        if (is_array($header)) {
            $this->headers = array_merge($this->headers, $header);
        } elseif (is_string($header)) {
            $this->headers[$header] = $value;
        }
    }

    /**
     * 跳转
     *
     * @param string $location 跳转地址
     * @param int $code 状态码，默认 302 临时重定向
     */
    public function redirect($location = null, $code = 302)
    {
        // disable view
        if ($this->di->has('view')) {
            $view = $this->di->getShared('view');
            if ($view instanceof ViewInterface) {
                $view->disable();
            }
        }

        if ($code < 300 || $code > 308) {
            $code = 302;
        }

        $this->code = $code;
        $this->setHeaders('Location', $location);
    }

    /**
     * 获取文件路径
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * 设置响应文件
     *
     * @param string $filePath 响应文件的路径
     * @param string $attachmentName 响应文件名称, 默认使用 $filePath 的文件名称
     * @param bool $attachment 是否发送下载文件的响应头, 默认发送
     */
    public function setFile($filePath, $attachmentName = null, $attachment = true)
    {
        if (!is_string($attachmentName)) {
            $attachmentName = basename($filePath);
        }

        if ($attachment) {
            $this->setHeaders([
                'Content-Type: application/octet-stream',
                'Content-Description: File Transfer',
                'Content-Disposition: attachment; filename=' . $attachmentName,
                'Content-Transfer-Encoding: binary',
            ]);
        }

        $this->file = $filePath;
    }

    /**
     * 发送响应数据
     */
    public function send()
    {
        if ($this->isSend) {
            throw new Exception('Response was already sent');
        }

        // send headers
        $this->sendHeaders();

        // send cookies
        $this->sendCookies();

        // send content
        if ($this->content !== null) {
            // 输出响应内容
            echo $this->content;
        } else {
            // 是否响应文件
            if (is_string($this->file) && strlen($this->file)) {
                readfile($this->file);
            }
        }

        $this->isSend = true;
    }

    /**
     * 发送响应 cookie
     */
    public function sendCookies()
    {
        foreach ($this->cookies as $name => $c) {
            setcookie(
                $name,
                $c['value'], // encryptValue
                $c['expire'],
                $c['path'],
                $c['domain'],
                $c['secure'],
                $c['httpOnly']
            );
        }
    }

    /**
     * 发送响应头
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return false;
        }

        if (isset($this->headers['Location']) && $this->code === 200) {
            $this->setStatusCode(302);
        }

        // 发送状态码
        http_response_code($this->code);

        // 发送自定义响应头
        foreach ($this->headers as $header => $value) {
            if (empty($value)) {
                header($header, true);
            } else {
                header("$header: $value", true);
            }
        }

        return true;
    }
}
