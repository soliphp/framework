<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Config;

class ConfigTest extends TestCase
{
    public function testInitializeWithData()
    {
        $data = ['foo' => 'bar'];
        $config = new Config($data);

        $this->assertEquals($data, $config->toArray());
    }

    public function testSet()
    {
        $config = new Config();

        $config->set('database', [
            'master' => [
                'host' => '127.0.0.1',
                'port' => '3306',
            ],
        ]);
        $this->assertEquals('127.0.0.1', $config['database']['master']['host']);
        $this->assertEquals('3306', $config->database->master->port);

        $config->set('database.master', [
            'host' => '192.168.1.100',
            'port' => '3100',
        ]);
        $this->assertEquals('192.168.1.100', $config['database']['master']['host']);
        $this->assertEquals('3100', $config->database->master->port);

        $config->set('database.master.host', '192.168.1.101');
        $config->set('database.master.dbname', 'demo');
        $this->assertEquals('192.168.1.101', $config->database->master->host);
        $this->assertEquals('demo', $config->database->master->dbname);

        $config->set('database.slave', [
            'host' => '192.168.1.200',
            'port' => '3200',
        ]);
        $this->assertEquals('192.168.1.200', $config['database']['slave']['host']);
        $this->assertEquals('3200', $config->database->slave->port);

        $config->set('database', 'just a string');
        $this->assertEquals('just a string', $config->database);

        $this->assertArrayHasKey('database', $config);
    }

    public function testOffsetSet()
    {
        $config = new Config();
        $config['foo'] = 'bar';
        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('bar', $config['foo']);
    }

    public function testGet()
    {
        $config = new Config();
        $config->set('foo.bar', 'baz');
        $this->assertEquals('baz', $config->get('foo.bar'));

        $foo = $config->get('foo');
        $this->assertInstanceOf(Config::class, $foo);
        $this->assertEquals('baz', $foo->bar);

        $config['foo.bar'] = 'baz2000';
        $this->assertEquals('baz2000', $config->get('foo.bar'));

        $this->assertEquals('baz2000', $config->get('foo.bar2000', 'baz2000'));

        $config->set('foooooo.bar', '');
        $this->assertEquals('baz2000', $config->get('foooooo.bar.baz', 'baz2000'));

        $this->assertEquals(null, $config->get('unknown.path'));
    }

    public function testOffsetGet()
    {
        $config = new Config();
        $config['foo'] = 'bar';
        $this->assertEquals('bar', $config['foo']);
    }

    public function testGetWithDefault()
    {
        $config = new Config();
        $config['foo'] = 'bar';
        $this->assertEquals('default', $config->get('unknown.path', 'default'));
    }

    public function testOffsetExists()
    {
        $config = new Config();
        $config['foo'] = 'bar';
        $this->assertTrue(isset($config['foo']));
    }

    public function testOffsetUnset()
    {
        $data = [
            'abc' => '123',
            'foo' => 'bar',
        ];
        $config = new Config($data);

        unset($config['foo']);
        $this->assertNull($config['foo']);
    }

    public function testNumeric()
    {
        $config = new Config(['abc']);
        $this->assertEquals('abc', $config->{0});
        $this->assertEquals('abc', $config[0]);
        $this->assertEquals('abc', $config['0']);
    }

    public function testCount()
    {
        $data = [
            'abc' => '123',
            'foo' => 'bar',
        ];
        $config = new Config($data);
        $this->assertEquals(2, $config->count());
    }

    public function testToArray()
    {
        $config = Config::__set_state([
            'database' => Config::__set_state([
                'master' => [
                    'host' => '127.0.0.1',
                    'port' => '3306',
                ],
                'charset' => 'utf8',
            ])
        ]);

        $data = $config->toArray();

        $this->assertArrayHasKey('database', $data);
        $this->assertEquals('3306', $data['database']['master']['port']);
        $this->assertEquals('utf8', $data['database']['charset']);
    }
}
