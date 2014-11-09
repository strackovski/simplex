<?php

namespace nv\Simplex\Provider\Service;

use Silex\Application;
use Silex\ServiceProviderInterface;
use nv\semtools\Factory\SemtoolsFactory;

/**
 * Semtools service provider for Silex applications
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class SemtoolsServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $this->registerSemtoolsBundle($app);
        $this->registerFactory($app);
    }

    /**
     * Register Semtools with provided options
     *
     * @param Application $app
     */
    protected function registerSemtoolsBundle(Application $app)
    {
        $app['semtools.classifier'] = $app->share(function ($app) {
            try {
                return SemtoolsFactory::create(
                    array(
                        'type' => 'classifier',
                        'provider' => $app['semtools.classifier.provider'],
                        'api_key' => $app['semtools.classifier.api_key'],
                        'options' => $app['semtools.classifier.options']
                    )
                );
            } catch (\Exception $e) {

            }

            return false;
        });

        $app['semtools.annotator'] = $app->share(function ($app) {
            try {
                return SemtoolsFactory::create(
                    array(
                        'type' => 'annotator',
                        'provider' => $app['semtools.annotator.provider'],
                        'api_key' => $app['semtools.annotator.api_key'],
                        'options' => $app['semtools.annotator.options']
                    )
                );
            } catch (\Exception $e) {

            }

            return false;
        });

        $app['semtools'] = $app->share(function ($app) {
            try {
                return new Semtools($app['semtools.classifier'], $app['semtools.annotator']);
            } catch (\Exception $e) {

            }

            return false;
        });
    }

    /**
     * Register Semtools Factory
     *
     * The factory allows manual instantiation of Semtools modules.
     *
     * @param Application $app
     */
    protected function registerFactory(Application $app)
    {
        $app['semtools.factory'] = $app->share(function ($app) {
            return new SemtoolsFactory();
        });
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {

    }
}
