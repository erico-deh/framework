<?php

namespace Pagekit\Database;

use Doctrine\DBAL\DriverManager;
use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\Database\Logging\DebugStack;
use Pagekit\Database\ORM\EntityManager;
use Pagekit\Database\ORM\Loader\AnnotationLoader;
use Pagekit\Database\ORM\MetadataManager;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $default = [
            'wrapperClass' => 'Pagekit\Database\Connection'
        ];

        $app['dbs'] = function($app) use ($default) {

            $dbs = [];

            foreach ($app['config']['database.connections'] as $name => $params) {

                $params = array_replace($default, $params);

                if ($app['config']['database.default'] === $name) {
                    $params['events'] = $app['events'];
                }

                $dbs[$name] = DriverManager::getConnection($params);
            }

            return $dbs;
        };

        $app['db'] = function ($app) {
            return $app['dbs'][$app['config']['database.default']];
        };

        $app['db.em'] = function($app) {
            return new EntityManager($app['db'], $app['db.metas']);
        };

        $app['db.metas'] = function($app) {

            $manager = new MetadataManager($app['db']);
            $manager->setLoader(new AnnotationLoader);
            $manager->setCache($app['cache.phpfile']);

            return $manager;
        };

        $app['db.debug_stack'] = function($app) {
            return new DebugStack($app['profiler.stopwatch']);
        };
    }

    public function boot(Application $app)
    {
    }
}
