<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Core;

use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapBadgeExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapFormExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapIconExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapLabelExtension;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use nv\Simplex\Core\Form\FormBuilder;
use nv\Simplex\Model\Listener\EntityListenerResolver;
use nv\Simplex\Provider\Service\ContentServiceProvider;
use nv\Simplex\Provider\Service\IntegrationServiceProvider;
use nv\Simplex\Provider\Service\SiteServiceProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use SilexAssetic\AsseticServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use nv\Simplex\Core\Mailer\SystemMailer;
use nv\Simplex\Provider\Service\MediaServiceProvider;
use nv\Simplex\Provider\Service\PageServiceProvider;
use nv\Simplex\Provider\Service\PostServiceProvider;
use nv\Simplex\Provider\Service\SettingsServiceProvider;
use nv\Simplex\Provider\Service\SimplexServiceProvider;
use nv\Simplex\Provider\Service\UserServiceProvider;
use nv\Simplex\Provider\UserProvider;
use nv\Simplex\Controller;

/**
 * Class Simplex
 *
 * A Simplex Application instance
 *
 * @package nv\Simplex\Core
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Simplex extends Application
{
    /**
     * Application initialization
     */
    public function init()
    {
        $this->registerProviders();
        $this->registerErrorRoutes();
    }

    /**
     * Register service providers, mount controllers
     */
    public function registerProviders()
    {
        $self = $this;
        $this->register(
            new HttpCacheServiceProvider(),
            array(
                'http_cache.cache_dir' => APPLICATION_ROOT_PATH.'/var/cache/http'
            )
        );

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new ServiceControllerServiceProvider());
        $this->register(new SessionServiceProvider());
        $this->register(new SwiftmailerServiceProvider());
        $this->register(new TwigServiceProvider(), array(
            'twig.options'        => array(
                'cache'            => APPLICATION_ROOT_PATH.'/var/cache/twig'
            ),
            'twig.path'           => array(
                __DIR__.'/../../web/templates',
                __DIR__.'/../../web/templates/admin/',
                __DIR__.'/../../web/templates/site',
                __DIR__.'/../../vendor/braincrafted/bootstrap-bundle/Braincrafted/Bundle/BootstrapBundle/Resources/views/Form'
            )
        ));

        $this->extend('twig', function (\Twig_Environment $twig) use ($self) {
            $twig->addExtension(new BootstrapIconExtension);
            $twig->addExtension(new BootstrapLabelExtension);
            $twig->addExtension(new BootstrapBadgeExtension);
            $twig->addExtension(new BootstrapFormExtension);

            return $twig;
        });

        /**
         * Time-ago extension for Twig
         */
        $this['twig'] = $this->share($this->extend('twig', function ($twig, $app) {
            $twig->addFilter(new \Twig_SimpleFilter('timeago', function ($datetime) use ($app) {
                $time = time() - strtotime($datetime);
                $units = array (
                    31536000 => 'year',
                    2592000 => 'month',
                    604800 => 'week',
                    86400 => 'day',
                    3600 => 'hour',
                    60 => 'minute',
                    1 => 'second'
                );

                foreach ($units as $unit => $val) {
                    if ($time < $unit) continue;
                    $numberOfUnits = floor($time / $unit);
                    return ($val == 'second')? 'a few seconds ago' :
                        (($numberOfUnits>1) ? $numberOfUnits : 'a')
                        .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
                }
            }));

            return $twig;
        }));

        /**
         * FormBuilder Twig Extension
         */
        $this['twig'] = $this->share($this->extend('twig', function ($twig, $app) {
            $twig->addFunction(new \Twig_SimpleFunction('buildForm', function ($form, $format = false) use ($app) {
                return $app['form.builder']->getForm($form, $format);
            }));

            return $twig;
        }));

        /*
         * Asset: resolve asset path by asset name and type (image, font, ...)
         * Return empty placeholder image when not found
         *
         * @todo Fix twig 'asset', use it
         */
        $this['twig'] = $this->share($this->extend('twig', function ($twig, $app) {
            $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
                $basePath = dirname(dirname(dirname(__FILE__))) . '/web/assets/';
                $baseUrl = $app['url_generator']->getContext()->getBaseUrl()  . '/assets/';

                if (strpos($asset, ":") !== false) {
                    list($type, $name) = explode(":", $asset);
                    if (file_exists($file = $basePath . $type . 's/' . $name)) {
                        return $baseUrl . $type . 's/' . $name;
                    }
                }

                return $baseUrl . 'images/empty.png';
            }));

            return $twig;
        }));

        /*
         * Display: use {{ display('size:mediaId') }} to resolve to actual media path
         * Size can be any valid media size identifier (cropped, small, large, medium, original)
         * Return empty placeholder image when not found
         *
         * example display('crops:mediaId')
         *
         * @todo Fix twig 'display', use it
         */
        $this['twig'] = $this->share($this->extend('twig', function ($twig, $app) {
            $twig->addFunction(new \Twig_SimpleFunction('display', function ($asset) use ($app) {
                $basePath = dirname(dirname(dirname(__FILE__))) . '/web/uploads/';
                $baseUrl = $app['url_generator']->getContext()->getBaseUrl();
                if (strpos($asset, ":") !== false) {
                    list($type, $name) = explode(":", $asset);
                    if (file_exists($file = $basePath . $type . '/' . $name)) {
                        return $baseUrl . 'uploads/' . $type . '/' . $name;
                    }
                }
                return $baseUrl . 'assets/images/empty.png';
            }));

            return $twig;
        }));

        $this->register(new TranslationServiceProvider(), array('locale_fallbacks' => array('en')));

        $app['user.provider'] = $this->share(function ($app) {
            return new UserProvider($app['orm.em']->getConnection(), $app);
        });

        $this->register(new SecurityServiceProvider());
        $this->register(new RememberMeServiceProvider());

        $this['security.firewalls'] = array(
            'admin' => array(
                'pattern' => '^/admin*',
                'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
                'logout' => array(
                    'logout_path' => '/admin/logout',
                    'target_url' => '/login'
                ),
                'remember_me' => array(
                    'key'                => 'S9pp1cD2Ax64g1VxZ8Yl6K5IWkY5rSOB',
                    'always_remember_me' => false,
                ),
                'users' => $app['user.provider']
            ),
            'api' => array(
                'pattern' => '^/api*',
                'http' => true,
                'stateless' => true,
                'users' => $app['user.provider']
            )
        );

        // Simple role access rules, will be replaced by ACL
        $this['security.access_rules'] = array(
            array('^/admin/pages', 'ROLE_ADMIN'),
            array('^/admin/settings', 'ROLE_ADMIN'),
            array('^/admin/media/settings', 'ROLE_ADMIN'),
            array('^/admin/users', 'ROLE_ADMIN'),
            array('^/admin/user*', array('ROLE_EDITOR', 'ROLE_ADMIN')),
            array('^/admin/posts', array('ROLE_EDITOR', 'ROLE_ADMIN')),
            array('^/admin/media', array('ROLE_EDITOR', 'ROLE_ADMIN')),
        );

        $this->register(new FormServiceProvider());

        $this['listener.resolver'] = $this->share(function ($app) {
            return new EntityListenerResolver($app);
        });

        $this->register(new DoctrineServiceProvider(), $this['orm.options']);
        $this->register(
            new DoctrineOrmServiceProvider(),
            array('orm.entity_listener_resolver' => $this['listener.resolver'])
        );

        if ($this['debug']) {
            $this->register(new AsseticServiceProvider());
            new AsseticSimplexBridge($this);
        }

        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile' => APPLICATION_ROOT_PATH.'/var/logs/app.log',
            'monolog.name'    => 'app',
            'monolog.level'   => $this['debug'] ? Logger::DEBUG : Logger::WARNING
        ));

        $this['monolog'] = $this->share($this->extend('monolog', function ($monolog, $app) {
            $monolog->pushHandler(new RotatingFileHandler(
                $app['monolog.logfile'],
                2,
                $this['debug'] ? Logger::DEBUG : Logger::WARNING
            ));

            return $monolog;
        }));

        if ($this['debug']) {
            $this->register(new WebProfilerServiceProvider(), array(
                'profiler.cache_dir' => $this['root_cache_dir'].'/profiler'
            ));
        }

        $this['form.builder'] = $this->share(function () {
            return new FormBuilder();
        });

        $this->register(new SimplexServiceProvider());

        $this['swiftmailer.options'] = $this['settings']->getMailConfig();
        $this['system.mailer'] = $this->share(function ($app) {
            return new SystemMailer(
                $app['mailer'],
                $app['twig'],
                $app['settings']
            );
        });

        $this->match('/login', 'nv\Simplex\Controller\Admin\SecurityController::loginAction')
            ->bind('login');

        $this->match('/help/password', 'nv\Simplex\Controller\Admin\UserController::forgotPasswordAction')
            ->bind('help/password');

        $this->match('/help/reset', 'nv\Simplex\Controller\Admin\UserController::resetPasswordAction')
            ->bind('help/reset');

        $this->match('/account/activate', 'nv\Simplex\Controller\Admin\UserController::activateAccountAction')
            ->bind('account/activate');

        $this->register($siteProvider = new SiteServiceProvider());
        $this->mount('/', $siteProvider);

        $this->register($integrationsProvider = new IntegrationServiceProvider());
        $this->mount('/admin', $integrationsProvider);

        $this->register($settingsProvider = new SettingsServiceProvider());
        $this->mount('/admin', $settingsProvider);

        $this->register($userProvider = new UserServiceProvider());
        $this->mount('/admin', $userProvider);

        $this->register($mediaProvider = new MediaServiceProvider());
        $this->mount('/admin', $mediaProvider);

        $this->register($postProvider = new PostServiceProvider());
        $this->mount('/admin', $postProvider);

        $this->register($formProvider = new \nv\Simplex\Provider\Service\FormServiceProvider());
        $this->mount('/admin', $formProvider);

        $this->register($pageProvider = new PageServiceProvider());
        $this->mount('/admin', $pageProvider);

        $this->register($contentProvider = new ContentServiceProvider());
        $this->mount('/admin', $contentProvider);
    }

    /**
     * Register error handlers
     */
    public function registerErrorRoutes()
    {
        $self = $this;
        $this->error(function (\Exception $e, $code) use ($self) {
            $templates = array(
                'errors/'.$code.'.html.twig',
                'errors/'.substr($code, 0, 2).'x.html.twig',
                'errors/'.substr($code, 0, 1).'xx.html.twig',
                'errors/default.html.twig'
            );

            return new Response($this['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
        });
    }
}
