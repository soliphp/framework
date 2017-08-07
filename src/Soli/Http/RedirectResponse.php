<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Http;

/**
 * 跳转
 *
 *<pre>
 * return new RedirectResponse('/session/login');
 *</pre>
 */
class RedirectResponse extends Response
{
    protected $targetUrl;

    /**
     * RedirectResponse constructor.
     *
     * @param string $url
     * @param int $code
     * @param string $message
     */
    public function __construct($url, $code = 302)
    {
        parent::__construct('', $code);

        $this->setTargetUrl($url);
    }

    /**
     * Returns the target URL.
     *
     * @return string target URL
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Sets the redirect target of this response.
     *
     * @param string $url The URL to redirect to
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTargetUrl($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->targetUrl = $url;

        $this->setContent(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'))
        );

        $this->setHeader('Location', $url);

        return $this;
    }
}
