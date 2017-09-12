<?php

namespace Soli\Tests\Logger\Adapter;

use Soli\Tests\TestCase;
use Soli\Logger\Adapter\File as Logger;

class FileTest extends TestCase
{
    public function testLog()
    {
        $logger = new Logger('/tmp/test.log');

        // string | 标量
        $str = '记录字符串类型日志内容';
        $logger->debug($str);

        // array
        $data = ['aa' => '中文', 'bb' => 222, 22 => 'bbb'];
        $logger->info($data);

        // object
        $obj = new \ArrayObject($data);
        $logger->info($obj);

        // exception
        $e = new \Exception('throw custom exception.');
        $logger->error($e);
    }
}
