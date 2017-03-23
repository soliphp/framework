<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

/**
 * 控制器基类
 *
 * @property \Soli\Dispatcher $dispatcher
 * @property \Soli\Http\Request $request
 * @property \Soli\Http\Response $response
 * @property \Soli\Session $session
 * @property \Soli\Session\Flash $flash
 */
class Controller extends Component
{
    /**
     * Controller constructor.
     */
    final public function __construct()
    {
    }
}
