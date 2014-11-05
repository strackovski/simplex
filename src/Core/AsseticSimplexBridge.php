<?php

namespace nv\Simplex\Core;

use Assetic\Asset\AssetCache;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\JSMinFilter;

/**
 * Class AsseticSimplexBridge
 *
 * @package nv\Simplex\Core
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class AsseticSimplexBridge
{
    /**
     * @var Simplex
     */
    private $app;

    /**
     * @param Simplex $app
     */
    public function __construct(Simplex $app)
    {
        $this->app = $app;
        $this->register();
    }

    /**
     * Assetic configuration
     */
    private function register()
    {
        $this->app['assetic.path_to_web'] = dirname(__FILE__) . '/../../web/assets/build';
        $this->app['assetic.options'] = array(
            'formulae_cache_dir' => __DIR__ . '/../../var/cache/assetic',
            'debug' => $this->app['debug']
        );
        $this->app['assetic.filter_manager'] = $this->app['assetic.filter_manager'] = $this->app->share(
            $this->app->extend('assetic.filter_manager', function ($fm, $app) {
                $fm->set('css_min', new \Assetic\Filter\CssMinFilter());
                $fm->set('js_min', new \Assetic\Filter\JSMinFilter());

                return $fm;
            })
        );

        $this->app['assetic.asset_manager'] = $this->app->share(
            $this->app->extend('assetic.asset_manager', function ($am, $app) {
                try {
                    $adminTheme = $app['settings']->getAdminTheme();
                } catch (\Exception $e) {
                    $adminTheme = 'default';
                }

                $am->set('styles', new AssetCache(
                    new GlobAsset(
                        array(
                            dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/styles/vendor/*.css',
                            dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/styles/*.css'
                        ),
                        array($app['assetic.filter_manager']->get('css_min'))
                    ),
                    new \Assetic\Cache\FilesystemCache(dirname(__DIR__) . '/../var/cache/assetic')
                ));

                $am->get('styles')->setTargetPath('styles.css');
                $am->set(
                    'jquery',
                    new FileAsset(
                        dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/jquery.js'
                    )
                );
                $am->set('scripts', new AssetCache(
                    new GlobAsset(
                        array(
                            dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/scripts/vendor/*.js',
                            dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/scripts/*.js'
                        ),
                        array($app['assetic.filter_manager']->get('js_min'))
                    ),
                    new \Assetic\Cache\FilesystemCache(__DIR__ . '/../../var/cache/assetic')
                ));
                $am->get('scripts')->setTargetPath('scripts.js');
                $am->get('jquery')->setTargetPath('jquery.js');

                return $am;
            })
        );
    }
}
