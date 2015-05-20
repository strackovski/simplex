<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\UserController;
use nv\Simplex\Core\User\UserManager;
use nv\Simplex\Model\Listener\UserListener;
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
        $app['user.manager'] = $app->share(function () use ($app) {
            return new UserManager(
                $app['repository.user'],
                $app['url_generator'],
                $app['system.mailer'],
                $app['security.encoder.digest']
            );
        });

        $app['user.listener'] = $app->share(function ($app) {
            return new UserListener(
                $app['user.manager'],
                $app['settings'],
                $app['monolog']
            );
        });

        $app['user.controller'] = $app->share(function () use ($app) {
            return new UserController(
                $app['repository.user'],
                $app['settings'],
                $app['twig'],
                $app['form.factory'],
                $app['security'],
                $app['session'],
                $app['url_generator'],
                $app['system.mailer'],
                $app['imagine'],
                $app['user.manager'],
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

        $controllers->get('/user/get/{user}', 'user.controller:getAction')
            ->bind('admin/user/get');

        $controllers->match('/user/delete/{user}', 'user.controller:deleteAction')
            ->bind('user/delete');

        $controllers->match('/help/password', 'user.controller:forgotPasswordAction')
            ->bind('help/password');

        $controllers->match('/help/reset', 'user.controller:resetPasswordAction')
            ->bind('help/reset');

        $controllers->match('/account/activate', 'user.controller:activateAccountAction')
            ->bind('account/activate');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
