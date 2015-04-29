<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Site as Site;
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
class SiteServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['post.site.controller'] = $app->share(function () use ($app) {
            return new Site\PostController();
        });

        $app['page.site.controller'] = $app->share(function () use ($app) {
            return new Site\PageController();
        });

        $app['form.site.controller'] = $app->share(function () use ($app) {
            return new Site\FormController();
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

        $app->get('/', 'page.site.controller:indexAction')
            ->bind('/');

        $app->match('/{slug}', 'page.site.controller:viewAction')
            ->bind('{slug}');

        $app->match('/post/{slug}', 'post.site.controller:viewAction')
            ->bind('post/{slug}');

        $app->match('/form/{formId}', 'form.site.controller:formAction')
            ->bind('form/{formId}');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
