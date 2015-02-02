<?php

namespace Pagekit\Filter;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;

class FilterServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['filter'] = function($app) {
            return new FilterManager($app['filter.defaults']);
        };

        $app['filter.defaults'] = null;
    }

    public function boot(Application $app)
    {
    }
}
