<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\FormController;
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
class FormServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['form.controller'] = $app->share(function () use ($app) {
            return new FormController(
                $app['repository.form'],
                $app['settings'],
                $app['twig'],
                $app['form.factory'],
                $app['security'],
                $app['session'],
                $app['url_generator'],
                $app['monolog']
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

        $controllers->get('/forms', 'form.controller:indexAction')
            ->bind('admin/forms');

        $controllers->match('/form/add', 'form.controller:addAction')
            ->bind('admin/form/add');

        $controllers->get('/form/get/{form}', 'form.controller:getAction')
            ->bind('admin/form/get');

        $controllers->get('/form/delete/{form}', 'form.controller:deleteAction')
            ->bind('admin/form/delete');

        $controllers->match('/form/edit/{form}', 'form.controller:editAction')
            ->bind('admin/form/edit');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
