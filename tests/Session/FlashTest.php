<?php

namespace Soli\Tests;

use PHPUnit\Framework\TestCase;

use Soli\Di\Container;
use Soli\Session;
use Soli\Session\Flash;

class FlashTest extends TestCase
{
    /**
     * @var \Soli\Session\Flash
     */
    protected $flash;

    protected function setUp()
    {
        $container = new Container();
        $container->clear();

        $container->set('session', function () {
            $session = new Session();
            $session->start();
            return $session;
        });

        $this->flash = $container->getShared(Flash::class, [$this->setProvider()]);
    }

    protected function tearDown()
    {
        $this->flash = null;
    }

    public function setProvider()
    {
        return [
            'error'   => 'error',
            'notice'  => 'notice',
            'warning' => 'warning',
            'success' => 'success'
        ];
    }

    public function testGetCssClasses()
    {
        $this->assertEquals('error', $this->flash->getCssClasses()['error']);
    }

    public function testError()
    {
        $this->myExpectOutputString('error', 'error');
    }

    public function testNotice()
    {
        $this->myExpectOutputString('notice', 'notice');
    }

    public function testWarning()
    {
        $this->myExpectOutputString('warning', 'warning');
    }

    public function testSuccess()
    {
        $this->myExpectOutputString('success', 'success');
    }

    public function myExpectOutputString($key, $value)
    {
        $this->expectOutputString(sprintf('<div class="%s">%s</div>', $key, $value));

        $this->flash->{$key}($value);
        $this->flash->output();
    }
}
