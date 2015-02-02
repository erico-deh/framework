<?php

namespace Pagekit\Auth;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\Auth\Encoder\NativePasswordEncoder;
use Pagekit\Auth\Event\LoginEvent;
use Pagekit\Auth\Event\LogoutEvent;
use RandomLib\Factory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthServiceProvider implements ServiceProviderInterface, EventSubscriberInterface
{
    protected $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $app['auth'] = function($app) {
            return new Auth($app['events'], $app['session']);
        };

        $app['auth.password'] = function() {
            return new NativePasswordEncoder;
        };

        $app['auth.random'] = function() {
            return (new Factory())->getMediumStrengthGenerator();
        };
    }

    public function boot(Application $app)
    {
        $app->subscribe($this);
    }

    /**
     * Redirects a user after successful login.
     *
     * @param LoginEvent $event
     */
    public function onLogin(LoginEvent $event)
    {
        $event->setResponse(new RedirectResponse($this->app['request']->get(Auth::REDIRECT_PARAM)));
    }

    /**
     * Redirects a user after successful logout.
     *
     * @param LogoutEvent $event
     */
    public function onLogout(LogoutEvent $event)
    {
        $event->setResponse(new RedirectResponse($this->app['request']->get(Auth::REDIRECT_PARAM)));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthEvents::LOGIN  => ['onLogin', -32],
            AuthEvents::LOGOUT => ['onLogout', -32]
        ];
    }
}
