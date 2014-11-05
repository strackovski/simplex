<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 Vladimir Stračkovski <vlado@nv3.org>
 * The MIT License <http://choosealicense.com/licenses/mit/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit the link above.
 */

namespace nv\Simplex\Core;

use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapBadgeExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapFormExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapIconExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapLabelExtension;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
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
use Symfony\Component\BrowserKit\Response;
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
        $this->registerDynamicRoutes();
    }

    /**
     * Register service providers, mount controllers
     */
    public function registerProviders()
    {
        $self = $this;
        $this->register(new HttpCacheServiceProvider(),
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

        /*
         * Asset: resolve asset path by asset name and type (image, font, ...)
         * Return empty placeholder image when not found
         *
         * @todo Fix & enable twig 'asset'
         */
        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
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
         * @todo Fix & enable twig 'display'
         */
        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twig->addFunction(new \Twig_SimpleFunction('display', function ($asset) use ($app) {
                $basePath = dirname(dirname(dirname(__FILE__))) . '/web/uploads/';
                $baseUrl = $app['url_generator']->getContext()->getBaseUrl()  . '/uploads/';

                if (strpos($asset, ":") !== false) {
                    list($type, $name) = explode(":", $asset);
                    if (file_exists($file = $basePath . $type . '/' . $name)) {
                        return $baseUrl . $type . '/' . $name;
                    }
                }

                return $app['url_generator']->getContext()->getBaseUrl() . '/assets/images/empty.png';
            }));

            return $twig;
        }));

        $this->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => array('en')
        ));

        $app['user.provider'] = $this->share(function($app) {
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
                    'key'                => 'SDFSDFDS34343sFDDSFSFS',
                    'always_remember_me' => false,
                ),
                'users' => $app['user.provider']
            ),
        );

        $this['security.access_rules'] = array(
            array('^/admin/pages', 'ROLE_ADMIN'),
            array('^/admin/settings', 'ROLE_ADMIN'),
            array('^/admin/users', 'ROLE_ADMIN'),
            array('^/admin/user*', array('ROLE_EDITOR', 'ROLE_ADMIN')),
            array('^/admin/posts', array('ROLE_EDITOR', 'ROLE_ADMIN')),
            array('^/admin/media', array('ROLE_EDITOR', 'ROLE_ADMIN')),
        );

        $this->register(new FormServiceProvider());
        $this->register(new DoctrineServiceProvider(), $this['orm.options']);
        $this->register(new DoctrineOrmServiceProvider());

        if ($this['debug']) {
            $this->register(new AsseticServiceProvider());
            new AsseticSimplexBridge($this);
        }

        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile' => APPLICATION_ROOT_PATH.'/var/logs/app.log',
            'monolog.name'    => 'app',
            'monolog.level'   => $this['debug'] ? Logger::DEBUG : Logger::WARNING
        ));

        $this['monolog'] = $this->share($this->extend('monolog', function($monolog, $app) {
            $monolog->pushHandler(new RotatingFileHandler(
                $app['monolog.logfile'],
                2,
                $this['debug'] ? Logger::DEBUG : Logger::WARNING)
            );

            return $monolog;
        }));

        if ($this['debug']) {
            $this->register(new WebProfilerServiceProvider(), array(
                'profiler.cache_dir' => $this['root_cache_dir'].'/profiler'
            ));
        }

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

        $this->register($settingsProvider = new SettingsServiceProvider());
        $this->mount('/admin', $settingsProvider);

        $this->register($userProvider = new UserServiceProvider());
        $this->mount('/admin', $userProvider);

        $this->register($mediaProvider = new MediaServiceProvider());
        $this->mount('/admin', $mediaProvider);

        $this->register($postProvider = new PostServiceProvider());
        $this->mount('/admin', $postProvider);

        $this->register($pageProvider = new PageServiceProvider());
        $this->mount('/admin', $pageProvider);
    }

    /**
     * Register error handlers
     */
    public function registerErrorRoutes()
    {
        $self = $this;
        $this->error(function (\Exception $e, $code) use ($self) {
            if ($self['debug']) {
                return;
            }

            $templates = array(
                'errors/'.$code.'.html.twig',
                'errors/'.substr($code, 0, 2).'x.html.twig',
                'errors/'.substr($code, 0, 1).'xx.html.twig',
                'errors/default.html.twig'
            );

            return new Response($this['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
        });
    }

    /**
     * Register dynamic site routes
     *
     * Binds public site routing
     */
    public function registerDynamicRoutes()
    {
        /*
         * Home route
         */
        $self = $this;
        if (is_null($self['repository.page']->findOneBy(array('slug' => '/')))) {
            $this->get('/', 'nv\Simplex\Controller\Site\PageController::indexAction')
            ->bind('/');
        }

        /*
         * @todo Post routes to Site/PostController (event. SiteService)
         */
        foreach ($posts = $self['repository.post']->getPublished() as $post) {
            $this->get('/post/' . $post->getSlug(), function () use ($self, $post) {

                $data['content'] = $post;
                $data['menu'] = $self['repository.page']->getMenuPages();
                $data['settings'] = $self['settings'];

                return $self['twig']->render(
                    'site/'.$data['settings']->getPublicTheme().'/views/post.html.twig',
                    $data
                );
            })->bind('/post/'.$post->getSlug());
        }

        /*
         * Dynamic page routes and data retrieval
         */
        foreach ($pages = $self['repository.page']->findAll() as $page) {
            /** @var \nv\Simplex\Model\Entity\Page $page */
            if ($page->getQueries()) {
                $this->get('/' . $page->getSlug(), function () use ($self, $page) {
                    $content = array();
                    foreach ($page->getQueries() as $query) {
                        // @todo check operator defaults for page queries
                        if (($op = $query->getOperator()) == 'between') {
                            $op = 'eq';
                        }
                        $content = $self['repository.post']->getPostsBy(
                            $query->getColumn(),
                            $query->getValue(),
                            $op,
                            $query->getSortBy(),
                            $query->getLimitMax() ? $query->getLimitMax() : false
                        );
                    }

                    $data['content'] = $content;
                    $data['page'] = $page;
                    $data['menu'] = $self['repository.page']->getMenuPages();
                    $data['settings'] = $self['settings'];

                    return $self['twig']->render(
                        'site/'.$data['settings']->getPublicTheme().'/views/'.$page->getView().'.html.twig',
                        $data
                    );
                })->bind('/'.$page->getSlug());
            }
        }
    }
}
