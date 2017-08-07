<?php
/**
 * @author ueaner <ueaner@gmail.com>
 */
namespace Soli;

interface RouterInterface
{
    /**
     * Handles routing information received from the rewrite engine
     *
     * @param string $uri
     * @return void
     */
    public function handle($uri = null);

    /**
     * Adds a route to the router on any HTTP method
     *
     * @param string|string[] $httpMethods
     * @param string $pattern
     * @param string $handler
     */
    //public function map($httpMethods, $pattern, $handler);

    /**
     * Returns processed namespace name
     *
     * @return string
     */
    public function getNamespaceName();

    /**
     * Returns processed controller name
     *
     * @return string
     */
    public function getControllerName();

    /**
     * Returns processed action name
     *
     * @return string
     */
    public function getActionName();

    /**
     * Returns processed extra params
     *
     * @return array
     */
    public function getParams();
}
