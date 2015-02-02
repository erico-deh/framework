<?php

namespace Pagekit\Routing\Controller;

use Pagekit\Routing\Event\GetControllerEvent;
use Pagekit\Routing\Event\RouteCollectionEvent;
use Pagekit\Routing\Event\RouteResourcesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CallbackCollection implements EventSubscriberInterface
{
    protected $routes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->routes = new RouteCollection;
    }

    /**
     * Maps a GET request to a callable.
     *
     * @param  string $path
     * @param  string $name
     * @param  mixed  $callback
     * @return Route
     */
    public function get($path, $name, $callback)
    {
        return $this->map($path, $name, $callback)->setMethods('GET');
    }

    /**
     * Maps a POST request to a callable.
     *
     * @param  string $path
     * @param  string $name
     * @param  mixed  $callback
     * @return Route
     */
    public function post($path, $name, $callback)
    {
        return $this->map($path, $name, $callback)->setMethods('POST');
    }

    /**
     * Maps a path to a callable.
     *
     * @param  string $path
     * @param  string $name
     * @param  mixed  $callback
     * @return Route
     */
    public function map($path, $name, $callback)
    {
        $route = (new Route($path))->setOption('__callback', $callback);

        $this->routes->add($name, $route);

        return $route;
    }

    /**
     * Adds this instances routes to the collection.
     *
     * @param RouteCollectionEvent $event
     */
    public function getRoutes(RouteCollectionEvent $event)
    {
        $event->addRoutes($this->routes);
    }

    /**
     * Adds this instances resources to the collection
     *
     * @param RouteResourcesEvent $event
     */
    public function getResources(RouteResourcesEvent $event)
    {
        $resources = [];
        foreach ($this->routes as $name => $route) {
            $resources[] = ['callback' => $name.$route->getPath()];
        }
        $event->addResources($resources);
    }

    /**
     * Resolves the callback as controller.
     *
     * @param GetControllerEvent $event
     */
    public function getController(GetControllerEvent $event)
    {
        $name = $event->getRequest()->attributes->get('_route', '');

        if ($route = $this->routes->get($name) and $callback = $route->getOption('__callback')) {
            $event->setController($callback);
        };
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'route.collection' => ['getRoutes', -8],
            'route.resources' => 'getResources',
            'controller.resolve' => 'getController'
        ];
    }
}
