<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\PostController;
use nv\Simplex\Core\Post\PostManager;
use nv\Simplex\Model\Listener\PostListener;
use nv\Simplex\Model\Repository\TagRepository;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Silex\ControllerCollection;

/**
 * Simplex Service Provider
 *
 * Provides Post service to Simplex application
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

        $app['repository.tag'] = $app->share(function ($app) {
            return new TagRepository(
                $app['orm.em'],
                $app['orm.em']->getClassMetadata('nv\Simplex\Model\Entity\Tag')
            );
        });

        $app['post.manager'] = $app->share(function ($app) {
            return new PostManager(
                $app['semtools'],
                $app['repository.post'],
                $app['repository.tag']
            );
        });

        $app['post.listener'] = $app->share(function ($app) {
            return new PostListener($app['post.manager'], $app['settings']);
        });

        $app['post.controller'] = $app->share(function () use ($app) {
            return new PostController(
                $app['repository.post'],
                $app['repository.media'],
                $app['repository.tag'],
                $app['settings'],
                $app['twig'],
                $app['form.factory'],
                $app['security'],
                $app['session'],
                $app['url_generator'],
                $app['post.manager']
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

        $controllers->get('/post/get/{post}', 'post.controller:getAction')
            ->bind('admin/post/get');

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
