<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\UserController;
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
class UserServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['user.controller'] = $app->share(function () use ($app) {
            return new UserController();
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

        $controllers->get('/users', 'user.controller:indexAction')
            ->bind('admin/users');

        $controllers->match('/user/add', 'user.controller:addAction')
            ->bind('admin/user/add');

        $controllers->match('/user/profile', 'user.controller:editProfileAction')
            ->bind('admin/user/profile');

        $controllers->match('/user/credentials', 'user.controller:credentialsAction')
            ->bind('admin/user/credentials');

        $controllers->match('/user/edit/{user}', 'user.controller:editAction')
            ->bind('admin/user/edit');

        $controllers->match('/user/delete/{user}', 'user.controller:deleteAction')
            ->bind('admin/user/delete');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
