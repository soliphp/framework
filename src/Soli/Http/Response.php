<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Http;

use Soli\Di\ContainerAwareInterface;
use Soli\Di\ContainerAwareTrait;
use Soli\ViewInterface;

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
 * $response->setCookie($cookie);
 *
 * $response->setHeader("Cache-Control: max-age=0");
 *
 * $response->send();
 *</pre>
 */
class Response implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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
    protected $contentType = 'text/html';

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

    /**
     * 设置响应状态
     *
     * @param int $code 状态码
     * @param string $message 状态描述
     *
     * @return $this
     */
    public function setStatusCode($code, $message = null)
    {
        $this->code = $code;
        $this->message = $message;

        return $this;
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
     *<pre>
     * $response->setContentType('application/javascript');
     *</pre>
     *
     * @param string $contentType
     * @param string $charset
     *
     * @return $this
     */
    public function setContentType($contentType, $charset = null)
    {
        $this->contentType = $contentType;

        if ($charset !== null) {
            $contentType .= "; charset=$charset";
        }
        $this->headers['Content-type'] = $contentType;

        return $this;
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
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content = null)
    {
        $this->content = (string)$content;

        return $this;
    }

    /**
     * 获取响应的 cookies 信息
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * 设置响应的 cookie 信息
     *
     * @param array $cookie 单个 cookie 信息
     *
     * @return $this
     */
    public function setCookie(array $cookie)
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

        $cookie = array_merge($default, $cookie);
        $this->cookies[$cookie['name']] = $cookie;

        return $this;
    }

    /**
     * 获取响应的头信息
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 设置响应头信息
     *
     * @param string $header
     * @param string $value
     *
     * @return $this
     */
    public function setHeader($header, $value = null)
    {
        if (is_string($header)) {
            $this->headers[$header] = $value;
        }

        return $this;
    }

    /**
     * 跳转
     *
     * @param string $location 跳转地址
     * @param int $code 状态码，默认 302 临时重定向
     *
     * @return $this
     */
    public function redirect($location = null, $code = 302)
    {
        // disable view
        if ($this->container->has('view')) {
            $view = $this->container->getShared('view');
            if ($view instanceof ViewInterface) {
                $view->disable();
            }
        }

        if ($code < 300 || $code > 308) {
            $code = 302;
        }

        $this->code = $code;
        $this->setHeader('Location', $location);

        return $this;
    }

    /**
     * 发送响应数据
     *
     * @return $this
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $this;
    }

    /**
     * 发送响应内容
     *
     * @return $this
     */
    public function sendContent()
    {
        echo $this->content;

        return $this;
    }

    /**
     * 发送响应 cookie
     *
     * @return $this
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

        return $this;
    }

    /**
     * 发送响应头
     *
     * @return $this
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
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

        return $this;
    }
}
