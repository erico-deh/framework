<?php

namespace Pagekit\View;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\View\Asset\AssetInterface;
use Pagekit\View\Asset\AssetManager;
use Pagekit\View\Asset\FileAsset;

class AssetServiceProvider implements ServiceProviderInterface
{
    protected $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $app['styles'] = function($app) {
            return new AssetManager($app['config']['app.version']);
        };

        $app['scripts'] = function($app) {
            return new AssetManager($app['config']['app.version']);
        };
    }

    public function boot(Application $app)
    {
        $app['sections']->append('head', function() use ($app) {

            $result = [];

            foreach ($app['styles'] as $style) {

                $attributes = $this->getDataAttributes($style);

                if ($style instanceof FileAsset) {
                    $result[] = sprintf('        <link href="%s" rel="stylesheet"%s>', $style, $attributes);
                } else {
                    $result[] = sprintf('        <style%s>%s</style>', $attributes, $style);
                }
            }

            foreach ($app['scripts'] as $script) {

                $attributes = $this->getDataAttributes($script);

                if ($script instanceof FileAsset) {
                    $result[] = sprintf('        <script src="%s"%s></script>', $script, $attributes);
                } else {
                    $result[] = sprintf('        <script%s>%s</script>', $attributes, $script);
                }
            }

            return implode(PHP_EOL, $result);

        });
    }

    protected function getDataAttributes(AssetInterface $asset)
    {
        $attributes = '';

        foreach ($asset->getOptions() as $name => $value) {
            if ('data-' == substr($name, 0, 5)) {
                $attributes .= sprintf(' %s="%s"', $name, htmlspecialchars($value));
            }
        }

        return $attributes;
    }
}
