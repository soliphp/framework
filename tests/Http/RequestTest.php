<?php

namespace Soli\Tests\Http;

use Soli\Tests\TestCase;

use Soli\Http\Request;
use Soli\Filter;

class RequestTest extends TestCase
{
    public function setUp()
    {
        $container = static::$container;
        $container->clear();

        $container->set('filter', function () {
            return new Filter();
        });

        $container->set('request', function () {
            return new Request();
        });
    }

    protected function getRequestObject()
    {
        return static::$container->get('request');
    }

    public function testHttpRequestMethod()
    {
        $request = $this->getRequestObject();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals($request->getMethod(), 'POST');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals($request->getMethod(), 'GET');

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertEquals($request->getMethod(), 'PUT');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $this->assertEquals($request->getMethod(), 'DELETE');

        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $this->assertEquals($request->getMethod(), 'OPTIONS');

        $_SERVER['REQUEST_METHOD'] = 'CONNECT';
        $this->assertEquals($request->getMethod(), 'CONNECT');

        $_SERVER['REQUEST_METHOD'] = 'TRACE';
        $this->assertEquals($request->getMethod(), 'TRACE');

        $_SERVER['REQUEST_METHOD'] = 'PURGE';
        $this->assertEquals($request->getMethod(), 'PURGE');
    }

    public function testHttpRequestGetQuery()
    {
        $request = $this->getRequestObject();

        $_REQUEST = $_GET = $_POST = [
            'id'    => 1,
            'num'   => 'a1a',
            'age'   => 'aa',
            'phone' => ''
        ];

        foreach (['get', 'getQuery', 'getPost'] as $f) {
            $this->assertEquals($_REQUEST, $request->$f());

            $this->assertEquals(1, $request->$f('id', 'int', 100));
            $this->assertEquals(1, $request->$f('num', 'int', 100));
            $this->assertEmpty($request->$f('age', 'int', 100));
            $this->assertEmpty($request->$f('phone', 'int', 100));
            $this->assertEquals(100, $request->$f('nonexistent', 'int', 100));
        }
    }

    public function testHttpRequestGetPut()
    {
        $raw = 'aa=100&bb=200';

        // 使用 getMockBuilder 方法创建测试替身，只替换 getRawBody 方法
        // 而使用 createMock 创建的桩件，所有方法都会被替换为只会返回 null 的伪实现
        $mock = $this->getMockBuilder(Request::class)
            ->setMethods(['getRawBody'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getRawBody')
            ->willReturn($raw);

        static::$container->set('request', $mock);

        $request = $this->getRequestObject();

        $put = $request->getPut();
        $this->assertEquals(100, $put['aa']);
        $this->assertEquals(200, $put['bb']);
    }

    public function testHttpRequestHas()
    {
        $request = $this->getRequestObject();

        $_REQUEST = [
            'name' => 'Soli'
        ];

        $this->assertTrue($request->has('name'));
        $this->assertFalse($request->has('nonexistent'));
    }


    public function testHttpRequestGetClientAddress()
    {
        $request = $this->getRequestObject();

        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $this->assertEquals($request->getClientAddress(), '192.168.0.1');

        $_SERVER['HTTP_CLIENT_IP'] = '214.55.34.56';
        $this->assertEquals($request->getClientAddress(), '214.55.34.56');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.7.21';
        $this->assertEquals($request->getClientAddress(), '192.168.7.21');

        $_SERVER['HTTP_X_REAL_IP'] = '86.45.89.47';
        $this->assertEquals($request->getClientAddress(), '86.45.89.47');
    }

    public function testHttpRequestGetUserAgent()
    {
        $request = $this->getRequestObject();

        $_SERVER['HTTP_USER_AGENT'] = 'Soli Test Suite';
        $this->assertEquals($request->getUserAgent(), 'Soli Test Suite');
    }

    public function testHttpRequestGetServerAddress()
    {
        $request = $this->getRequestObject();

        $_SERVER['SERVER_ADDR'] = '192.168.4.1';
        $this->assertEquals($request->getServerAddress(), '192.168.4.1');

        unset($_SERVER['SERVER_ADDR']);
        $this->assertEquals($request->getServerAddress(), '127.0.0.1');
    }

    public function testHttpRequestGetServerVars()
    {
        $request = $this->getRequestObject();

        $this->assertNotEmpty($request->getServer('REQUEST_TIME'));
        $this->assertNotEmpty($request->getServer('SCRIPT_NAME'));

        $this->assertArrayHasKey('PHP_SELF', $request->getServer());
    }

    public function testHttpRequestCookies()
    {
        $request = $this->getRequestObject();

        $_COOKIE['id'] = '100';
        $this->assertEquals($_COOKIE, $request->getCookies());
        $this->assertEquals(100, $request->getCookies('id'));
    }
}
