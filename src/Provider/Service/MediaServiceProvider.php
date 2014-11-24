<?php

namespace nv\Simplex\Provider\Service;

use Neutron\Silex\Provider\ImagineServiceProvider;
use nv\Simplex\Controller\Admin\ImageController;
use nv\Simplex\Controller\Admin\MediaController;
use nv\Simplex\Controller\Admin\VideoController;
use nv\Simplex\Core\Media\FaceDetector;
use nv\Simplex\Core\Media\ImageManager;
use nv\Simplex\Core\Media\VideoManager;
use nv\Simplex\Model\Listener\MediaListener;
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
class MediaServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app->register(new ImagineServiceProvider());

        $app['image.face.detector'] = $app->share(function () use ($app) {
            if (file_exists($file = dirname(dirname(dirname(__DIR__))) . '/config/facedata.dat')) {
                return new FaceDetector(dirname(dirname(dirname(__DIR__))) . '/config/facedata.dat');
            }
            return false;
        });

        $app['image.manager'] = $app->share(function () use ($app) {
            return new ImageManager(
                $app['imagine'],
                $app['image.face.detector']
            );
        });

        $app['video.manager'] = $app->share(function () use ($app) {
            return new VideoManager($app['imagine']);
        });

        $app['media.listener'] = $app->share(function ($app) {
            return new MediaListener(
                $app['image.manager'],
                $app['video.manager'],
                $app['settings']
            );
        });

        $app['media.controller'] = $app->share(function () use ($app) {
            return new MediaController(
                $app['repository.media'],
                $app['settings'],
                $app['twig'],
                $app['form.factory'],
                $app['security'],
                $app['session'],
                $app['url_generator']
            );
        });

        $app['image.controller'] = $app->share(function () use ($app) {
            return new ImageController(
                $app['repository.media'],
                $app['image.manager'],
                $app['settings'],
                $app['twig'],
                $app['form.factory'],
                $app['security'],
                $app['session'],
                $app['url_generator']
            );
        });

        $app['video.controller'] = $app->share(function () use ($app) {
            return new VideoController(
                $app['repository.media'],
                $app['video.manager'],
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

        $controllers->get('/media', 'media.controller:indexAction')
            ->bind('admin/media');

        $controllers->get('/videos/list', 'video.controller:listVideoAction')
            ->bind('admin/videos/list');

        $controllers->get('/media/images', 'image.controller:indexAction')
            ->bind('admin/media/images');

        $controllers->match('/media/videos', 'video.controller:indexAction')
            ->bind('admin/media/videos');

        $controllers->match('/media/upload', 'image.controller:uploadAction')
            ->bind('admin/media/upload');

        $controllers->match('/video/upload', 'video.controller:uploadAction')
            ->bind('admin/video/upload');

        $controllers->match('/media/view/{id}', 'media.controller:viewAction')
            ->bind('admin/media/view');

        $controllers->match('/media/delete/{id}', 'media.controller:deleteAction')
            ->bind('admin/media/delete');

        $controllers->match('/media/delete', 'media.controller:deleteAction')
            ->bind('admin/media/delete');

        $controllers->match('/media/help', 'media.controller:helpAction')
            ->bind('admin/media/help');

        $controllers->match('/media/edit/{id}', 'media.controller:editAction')
            ->bind('admin/media/edit');

        $controllers->match('/media/settings', 'media.controller:settingsAction')
            ->bind('admin/media/settings');

        $controllers->match('/media/resample', 'media.controller:resampleMediaLibraryAction')
            ->bind('admin/media/resample');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
