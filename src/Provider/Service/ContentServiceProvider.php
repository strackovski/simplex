<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\ContentController;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Silex\ControllerCollection;

/**
 * Simplex Service Provider
 *
 * Provides Simplex functionality to Silex applications
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ContentServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['content.controller'] = $app->share(function () use ($app) {
            return new ContentController(
                $app['repository.post'],
                $app['repository.media'],
                $app['repository.page'],
                $app['repository.tag'],
                $app['settings'],
                $app['twig'],
                $app['form.factory'],
                $app['security'],
                $app['session'],
                $app['url_generator']
            );
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

        $controllers->match('/content', 'content.controller:indexAction')
            ->bind('admin/content');

        $controllers->match('/content/help', 'content.controller:helpAction')
            ->bind('admin/content/help');

        $controllers->get('/content/get/{type}/{id}', 'content.controller:getAction')
            ->bind('admin/content/get');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
