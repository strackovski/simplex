<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\PageController;
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
class PageServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['page.controller'] = $app->share(function () use ($app) {
            return new PageController();
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

        $controllers->get('/pages', 'page.controller:indexAction')
            ->bind('admin/pages');

        $controllers->match('/page/add', 'page.controller:addAction')
            ->bind('admin/page/add');

        $controllers->match('/page/edit/{page}', 'page.controller:editAction')
            ->bind('admin/page/edit');

        $controllers->match('/page/delete/{page}', 'page.controller:deleteAction')
            ->bind('admin/page/delete');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
