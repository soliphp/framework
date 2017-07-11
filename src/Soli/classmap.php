<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
// classmap
return [
    'Soli\\Application'                        => __DIR__ . '/Application.php',
    'Soli\\BaseApplication'                    => __DIR__ . '/BaseApplication.php',
    'Soli\\BaseDispatcher'                     => __DIR__ . '/BaseDispatcher.php',
    'Soli\\Component'                          => __DIR__ . '/Component.php',
    'Soli\\Controller'                         => __DIR__ . '/Controller.php',
    'Soli\\Cli\\Application'                   => __DIR__ . '/Cli/Application.php',
    'Soli\\Cli\\Dispatcher'                    => __DIR__ . '/Cli/Dispatcher.php',
    'Soli\\Cli\\Task'                          => __DIR__ . '/Cli/Task.php',
    'Soli\\Db'                                 => __DIR__ . '/Db.php',
    'Soli\\Di\\Container'                      => __DIR__ . '/Di/Container.php',
    'Soli\\Di\\ContainerAwareInterface'        => __DIR__ . '/Di/ContainerAwareInterface.php',
    'Soli\\Di\\ContainerAwareTrait'            => __DIR__ . '/Di/ContainerAwareTrait.php',
    'Soli\\Di\\ContainerInterface'             => __DIR__ . '/Di/ContainerInterface.php',
    'Soli\\Di\\Service'                        => __DIR__ . '/Di/Service.php',
    'Soli\\Di\\ServiceInterface'               => __DIR__ . '/Di/ServiceInterface.php',
    'Soli\\Dispatcher'                         => __DIR__ . '/Dispatcher.php',
    'Soli\\Exception'                          => __DIR__ . '/Exception.php',
    'Soli\\Events\\Event'                      => __DIR__ . '/Events/Event.php',
    'Soli\\Events\\EventManager'               => __DIR__ . '/Events/EventManager.php',
    'Soli\\Events\\EventManagerAwareInterface' => __DIR__ . '/Events/EventManagerAwareInterface.php',
    'Soli\\Events\\EventManagerAwareTrait'     => __DIR__ . '/Events/EventManagerAwareTrait.php',
    'Soli\\Events\\EventManagerInterface'      => __DIR__ . '/Events/EventManagerInterface.php',
    'Soli\\Filter'                             => __DIR__ . '/Filter.php',
    'Soli\\Http\\Request'                      => __DIR__ . '/Http/Request.php',
    'Soli\\Http\\Response'                     => __DIR__ . '/Http/Response.php',
    'Soli\\Loader'                             => __DIR__ . '/Loader.php',
    'Soli\\Logger\\Adapter\\File'              => __DIR__ . '/Logger/Adapter/File.php',
    'Soli\\Model'                              => __DIR__ . '/Model.php',
    'Soli\\Session'                            => __DIR__ . '/Session.php',
    'Soli\\Session\\Flash'                     => __DIR__ . '/Session/Flash.php',
    'Soli\\View'                               => __DIR__ . '/View.php',
    'Soli\\ViewInterface'                      => __DIR__ . '/ViewInterface.php',
    'Soli\\View\\Engine'                       => __DIR__ . '/View/Engine.php',
    'Soli\\View\\EngineInterface'              => __DIR__ . '/View/EngineInterface.php',
    'Soli\\View\\Engine\\Simple'               => __DIR__ . '/View/Engine/Simple.php',
    'Soli\\View\\Engine\\Smarty'               => __DIR__ . '/View/Engine/Smarty.php',
    'Soli\\View\\Engine\\Twig'                 => __DIR__ . '/View/Engine/Twig.php',
];
