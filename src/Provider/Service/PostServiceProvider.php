<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\PostController;
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
class PostServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app->register(new SemtoolsServiceProvider(), array(
            'semtools.classifier.provider' => 'uClassify',
            'semtools.classifier.api_key' => '0wYctA3XvxGfiH8xpFjigyPHkNs',
            'semtools.classifier.options' => array(),
            'semtools.annotator.provider' => 'OpenCalais',
            'semtools.annotator.api_key' => 'asubyt3ptak743yc8jq4hfn7',
            'semtools.annotator.options' => array(),
        ));

        $app['post.controller'] = $app->share(function () use ($app) {
            return new PostController();
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

        $controllers->get('/posts', 'post.controller:indexAction')
            ->bind('admin/posts');

        $controllers->match('/post/add', 'post.controller:addAction')
            ->bind('admin/post/add');

        $controllers->match('/post/edit/{post}', 'post.controller:editAction')
            ->bind('admin/post/edit');

        $controllers->match('/post/delete/{post}', 'post.controller:deleteAction')
            ->bind('admin/post/delete');

        $controllers->match('/post/view/{post}', 'post.controller:viewAction')
            ->bind('admin/post/view');

        $controllers->match('/posts/filter/{filter_key}/{filter_val}', 'post.controller:filterAction')
            ->bind('admin/posts/filter');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
