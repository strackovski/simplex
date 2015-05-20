<?php

namespace nv\Simplex\Core;

use Assetic\Asset\AssetCache;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\AssetManager;
use Assetic\Factory\AssetFactory;
use Assetic\Filter\JSMinFilter;
use Assetic\FilterManager;

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
            'debug' => $this->app['debug'],
            'auto_dump_assets' => false
        );

        $this->app['assetic.filter_manager'] = $this->app['assetic.filter_manager'] = $this->app->share(
            $this->app->extend('assetic.filter_manager', function ($fm, $app) {
                /** @var FilterManager $fm */
                $fm->set(
                    'css_min',
                    new \Assetic\Filter\Yui\CssCompressorFilter(
                        dirname(__FILE__) . '/../../bin/yuicompressor-2.4.7.jar'
                    )
                );
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

                /*
                 * Check if theme has a vendors.json dependency definition file
                 */
                if (file_exists($f = dirname(__DIR__) . '/../web/templates/admin/'  . $adminTheme . '/vendors.json')) {
                    // Process vendors.json
                    $vendors = json_decode(file_get_contents($f), 1);
                    $scripts = array();
                    $styles = array();
                    $dir = dirname(__DIR__) . '/../web/templates/admin/'.$adminTheme.'/assets/';

                    foreach ($vendors['scripts'] as $name => $path) {
                        $scripts[] = $dir . 'scripts/' . $path;
                    }

                    foreach ($vendors['styles'] as $name => $path) {
                        $styles[] = $dir . 'styles/' . $path;
                    }

                    // Build according to vendors.json
                    /** @var AssetManager $am */
                    $am->set('styles', new AssetCache(
                        new GlobAsset(
                            $styles,
                            array($app['assetic.filter_manager']->get('css_min'))
                        ),
                        new \Assetic\Cache\FilesystemCache(dirname(__DIR__) . '/../var/cache/assetic')
                    ));

                    $am->set('scripts', new AssetCache(
                        new GlobAsset(
                            $scripts,
                            array($app['assetic.filter_manager']->get('js_min'))
                        ),
                        new \Assetic\Cache\FilesystemCache(dirname(__DIR__) . '/../var/cache/assetic')
                    ));

                } else {
                    // Build according to theme directory structure
                    /** @var AssetManager $am */
                    $am->set('styles', new AssetCache(
                        new GlobAsset(
                            array(
                                dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/vendor/*.css',
                                dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/styles/*.css'
                            ),
                            array($app['assetic.filter_manager']->get('css_min'))
                        ),
                        new \Assetic\Cache\FilesystemCache(dirname(__DIR__) . '/../var/cache/assetic')
                    ));

                    $am->set('scripts', new AssetCache(
                        new GlobAsset(
                            array(
                                dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/vendor/*.js',
                                dirname(__DIR__) . '/../web/templates/admin/' . $adminTheme . '/assets/scripts/*.js'
                            ),
                            array($app['assetic.filter_manager']->get('js_min'))
                        ),
                        new \Assetic\Cache\FilesystemCache(__DIR__ . '/../../var/cache/assetic')
                    ));
                }

                $am->get('styles')->setTargetPath('styles.css');
                $am->get('scripts')->setTargetPath('scripts.js');

                return $am;
            })
        );
    }
}
