<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\IntegrationController;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Integration Service Provider
 *
 * Provides integration services
 *
 * @todo Rename to AnalyticsServiceProvider
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class IntegrationServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['integration.controller'] = $app->share(function () use ($app) {
            return new IntegrationController(
                $app['repository.settings'],
                $app['settings'],
                $app['twig'],
                $app['form.factory'],
                $app['security'],
                $app['session'],
                $app['url_generator'],
                $app['monolog']
            );
        });

        $app['google'] = $app->share(function () use ($app) {
            return $app['integration.controller']->googleConnectAction(new Request());
        });

        $app['yt'] = $app->share(function () use ($app) {
            try {
                $youtube = new \Google_Service_YouTube($app['google']);
                return $youtube;
            } catch (\Google_Service_Exception $e) {
                return 'A service error occurred: ' . htmlspecialchars($e->getMessage()) . '<br>';
            } catch (\Google_Exception $e) {
                return 'A client error occurred: ' . htmlspecialchars($e->getMessage()) . '<br>';
            }
        });
    }

    /**
     * @param Application $app
     *
     * @return ControllerCollection
     */
    public function connect(Application $app)
    {
        /** @var $controllers ControllerCollection */
        $controllers = $app['controllers_factory'];

        $controllers->match('/service/analytics/test', 'integration.controller:analyticsTest')
            ->bind('admin/service/analytics/test');

        $controllers->match('/service/analytics/os', 'integration.controller:getOsAnalytics')
            ->bind('admin/service/analytics/os');

        $controllers->match('/service/analytics/browser', 'integration.controller:getBrowserAnalytics')
            ->bind('admin/service/analytics/browser');
        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
