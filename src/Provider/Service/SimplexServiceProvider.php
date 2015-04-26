<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Model\Repository\FormRepository;
use Silex\Application;
use Silex\ServiceProviderInterface;
use nv\Simplex\Model\Repository\MediaRepository;
use nv\Simplex\Model\Repository\PageRepository;
use nv\Simplex\Model\Repository\PostRepository;
use nv\Simplex\Model\Repository\SettingsRepository;
use nv\Simplex\Model\Repository\UserRepository;

/**
 * Simplex Service Provider
 *
 * Provides Simplex functionality to Silex applications
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class SimplexServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['repository.post'] = $app->share(function ($app) {
            return new PostRepository(
                $app['orm.em'],
                $app['orm.em']->getClassMetadata('nv\Simplex\Model\Entity\Post')
            );
        });

        $app['repository.media'] = $app->share(function ($app) {
            return new MediaRepository(
                $app['orm.em'],
                $app['orm.em']->getClassMetadata('nv\Simplex\Model\Entity\MediaItem')
            );
        });

        $app['repository.page'] = $app->share(function ($app) {
            return new PageRepository(
                $app['orm.em'],
                $app['orm.em']->getClassMetadata('nv\Simplex\Model\Entity\Page')
            );
        });

        $app['repository.user'] = $app->share(function ($app) {
            return new UserRepository(
                $app['orm.em'],
                $app['orm.em']->getClassMetadata('nv\Simplex\Model\Entity\User')
            );
        });

        $app['repository.form'] = $app->share(function ($app) {
            return new FormRepository(
                $app['orm.em'],
                $app['orm.em']->getClassMetadata('nv\Simplex\Model\Entity\Form')
            );
        });

        $app['repository.settings'] = $app->share(function ($app) {
            return new SettingsRepository(
                $app['orm.em'],
                $app['orm.em']->getClassMetadata('nv\Simplex\Model\Entity\Settings')
            );
        });

        $app['settings'] = $app->share(function ($app) {
            return $app['repository.settings']->getCurrent();
        });
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
