<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli\Http;

use ArrayObject;

/**
 * 响应JSON
 *
 *<pre>
 * $response = new JsonResponse();
 * $response->setStatusCode(200);
 * $response->setData(['data' => 123]);
 *
 * $response->send();
 *</pre>
 */
class JsonResponse extends Response
{
    // encode data
    protected $data;
    protected $callback;

    protected $encodingOptions = 0;

    /**
     * JsonResponse constructor.
     *
     * @param mixed $data
     * @param int $code
     * @param string $message
     */
    public function __construct($data = null, $code = 200, $message = null)
    {
        parent::__construct('', $code, $message);

        if ($data === null) {
            $data = new ArrayObject();
        }

        $this->setData($data);
    }

    /**
     * Sets the JSONP callback.
     *
     * @param string|null $callback The JSONP callback or null to use none
     *
     * @return $this
     */
    public function setCallback($callback = null)
    {
        $this->callback = $callback;

        return $this->update();
    }

    /**
     * Sets a raw string containing a JSON document to be sent.
     *
     * @param string $json
     *
     * @return $this
     */
    public function setJson($json)
    {
        $this->data = $json;

        return $this->update();
    }

    /**
     * Sets the data to be sent as JSON.
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $data = json_encode($data, $this->encodingOptions);

        return $this->setJson($data);
    }

    /**
     * Returns options used while encoding data to JSON.
     *
     * @return int
     */
    public function getEncodingOptions()
    {
        return $this->encodingOptions;
    }

    /**
     * Sets options used while encoding data to JSON.
     *
     * @param int $encodingOptions
     *
     * @return $this
     */
    public function setEncodingOptions($encodingOptions)
    {
        $this->encodingOptions = (int) $encodingOptions;

        return $this->setData(json_decode($this->data));
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return $this
     */
    protected function update()
    {
        if ($this->callback !== null) {
            $this->setContentType('application/javascript');
            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        if (empty($this->getContentType()) || $this->getContentType() === 'application/javascript') {
            $this->setContentType('application/json', 'UTF-8');
        }

        return $this->setContent($this->data);
    }
}
