<?php

namespace nv\Simplex\Provider\Service;

use nv\Simplex\Controller\Admin\SettingsController;
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
class SettingsServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['settings.controller'] = $app->share(function () use ($app) {
            return new SettingsController(
                $app['settings'],
                $app['repository.settings'],
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

        $controllers->match('/dashboard', 'settings.controller:dashboardAction')
            ->bind('admin/dashboard');

        $controllers->match('/settings/snapshots', 'settings.controller:snapshotsIndexAction')
            ->bind('admin/settings/snapshots');

        $controllers->match('/settings', 'settings.controller:editAction')
            ->bind('admin/settings');

        $controllers->match('/settings/themes', 'settings.controller:themeSettingsAction')
            ->bind('admin/settings/themes');

        $controllers->match('/settings/mail', 'settings.controller:mailSettingsAction')
            ->bind('admin/settings/mail');

        $controllers->match('/settings/services', 'settings.controller:serviceSettingsAction')
            ->bind('admin/settings/services');

        $controllers->match('/settings/delete/{id}', 'settings.controller:deleteAction')
            ->bind('admin/settings/delete');

        $controllers->match('/settings/activate/{id}', 'settings.controller:activateAction')
            ->bind('admin/settings/activate');

        $controllers->match('/settings/export/{format}', 'settings.controller:exportAction')
            ->bind('admin/settings/export');

        $controllers->match('/settings/import', 'settings.controller:importAction')
            ->bind('admin/settings/import');

        $controllers->match('/settings/edit', 'settings.controller:editAction')
            ->bind('admin/settings/edit');

        $controllers->match('/settings/save', 'settings.controller:saveAction')
            ->bind('admin/settings/save');

        $controllers->match('/settings/themes/add', 'settings.controller:addThemeAction')
            ->bind('admin/settings/themes/add');

        $controllers->match('/settings/theme/upload/{type}', 'settings.controller:uploadThemeFile')
            ->bind('admin/settings/theme/upload');

        $controllers->match('/settings/stats/interactions', 'settings.controller:getInteractionStats')
            ->bind('admin/settings/stats/interactions');

        return $controllers;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
