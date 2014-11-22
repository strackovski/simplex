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

namespace nv\Simplex\Controller\Admin;

use nv\Simplex\Form\MailSettingsType;
use nv\Simplex\Form\ThemeSettingsType;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\SettingsRepository;
use PhpAmqpLib\Connection\AMQPConnection;
use Silex\Application;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use mjohnson\utility\TypeConverter;
use nv\Simplex\Form\SettingsType;
use nv\Simplex\Model\Entity\Image;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class SettingsController
 *
 * Defines actions to perform on requests regarding Settings objects.
 *
 * @package nv\Simplex\Controller\Admin
 */
class SettingsController
{
    /** @var Settings */
    private $settings;

    /** @var SettingsRepository */
    private $settingsRepository;

    /** @var \Twig_Environment  */
    private $twig;

    /** @var FormFactoryInterface  */
    private $form;

    /** @var SecurityContext  */
    private $security;

    /** @var Session  */
    private $session;

    /** @var UrlGenerator */
    private $url;

    public function __construct(
        Settings $settings,
        SettingsRepository $settingsRepository,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url
    ) {
        $this->settings = $settings;
        $this->settingsRepository = $settingsRepository;
        $this->twig = $twig;
        $this->form = $formFactory;
        $this->security = $security;
        $this->session = $session;
        $this->url = $url;
    }

    /**
     * Administration panel home
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function dashboardAction(Request $request, Application $app)
    {
        $posts = $app['repository.post']->findAll();
        $latest['posts'] = $app['repository.post']->getLatest(5);
        $latest['media'] = $app['repository.media']->getLatest(5);
        $latest['pages'] = $app['repository.page']->getLatest(5);
        $settings = $this->settingsRepository->getCurrent();

        $published = '';
        $exposed = '';

        /** @var \nv\Simplex\Model\Entity\Post $post */
        foreach ($posts as $post) {
            if ($post->getExposed()) {
                $exposed[] = $post;
            }

            if ($post->getPublished()) {
                $published[] = $post;
            }
        }

        $data = array(
            'posts_select' => array(
                'published' => $exposed,
                'exposed' => $published,
            ),
            'posts' => $posts,
            'latest' => $latest,
            'settings' => $settings
        );

        if (null !== $app['security']->getToken()) {
            $data['user'] = $app['security']->getToken()->getUser();
        }

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/dashboard.html.twig', $data);
    }

    /**
     * Settings index
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/settings.html.twig',
            array(
                'settings' => $this->settings,
                'title' => 'Settings',
            )
        );

    }

    /**
     * Settings snapshots
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function snapshotsIndexAction(Request $request)
    {
        $settings = $this->settingsRepository->getSnapshots();

        $data = array(
            'snapshots' => $settings,
            'title' => 'Snapshots',
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/snapshots.html.twig', $data);
    }

    /**
     * Export settings
     *
     * @todo Fix error generating XML
     *
     * @param Request     $request
     * @return Response
     */
    public function exportAction(Request $request)
    {
        $settingsArray = $this->settings->getSettings();
        $exportFilename = 'settings-export_' . time();
        $response = new Response();

        switch (strtolower($request->get('format'))) {
            case 'xml':
                $response->setContent(TypeConverter::toXml($settingsArray));
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'text/xml');
                $response->headers->set(
                    'Content-Disposition',
                    'attachment; filename="'.$exportFilename.'.xml"'
                );
                break;

            case 'json':
                $response->setContent(json_encode($settingsArray));
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/json');
                $response->headers->set(
                    'Content-Disposition',
                    'attachment; filename="'.$exportFilename.'.json"'
                );
                break;

            case 'pdf':
                $response->setContent('pdf :>');
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'text/raw');
                break;

            default:
                $response->setContent('Invalid or no format.');
                $response->setStatusCode(400);
                break;
        }

        return $response;
    }

    /**
     * Import settings from file
     *
     * @param Request     $request
     * @return string
     */
    public function importAction(Request $request)
    {
        // @next Settings import implement (file upload, read, persist)
        return false;
    }

   /**
     * Save settings
     *
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function saveAction(Request $request)
    {
        /** @var \nv\Simplex\Model\Entity\Settings $archive */
        $archive = clone $this->settingsRepository->getCurrent();
        $archive->setCurrent(false);
        $this->settingsRepository->save($archive);

        $redirect = $this->url->generate('admin/settings');
        return new RedirectResponse($redirect);
    }

    /**
     * Configure theme settings
     *
     * @param Request $request
     * @return mixed
     */
    public function themeSettingsAction(Request $request)
    {
        /** @var \nv\Simplex\Model\Entity\Settings $settings */
        $settings = $this->settingsRepository->getCurrent();
        $form = $this->form->create(new ThemeSettingsType($this->settingsRepository), $settings);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {

                $this->settingsRepository->save($settings);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'settings' => $settings,
            'title' => 'Edit settings',
        );

        if (!$request->isXmlHttpRequest()) {
            $data['tabs'] = array(
                'panels' => array(
                    'settings' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings')
                    ),
                    'media' => array(
                        'active' => false, 'url' => $this->url->generate('admin/media/settings')
                    ),
                    'themes' => array(
                        'active' => true, 'url' => $this->url->generate('admin/settings/themes')
                    ),
                    'mailing' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings/mail')
                    )
                )
            );
        }

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/theme-settings.html.twig',
            $data
        );
    }

    /**
     * Configure system mailing settings
     *
     * @param Request $request
     * @return mixed
     */
    public function mailSettingsAction(Request $request)
    {
        /** @var \nv\Simplex\Model\Entity\Settings $settings */
        $settings = $this->settingsRepository->getCurrent();
        $form = $this->form->create(new MailSettingsType($this->settingsRepository), $settings);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->settingsRepository->save($settings);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'settings' => $settings,
            'title' => 'Edit settings'
        );

        if (!$request->isXmlHttpRequest()) {
            $data['tabs'] = array(
                'panels' => array(
                    'settings' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings')
                    ),
                    'media' => array(
                        'active' => false, 'url' => $this->url->generate('admin/media/settings')
                    ),
                    'themes' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings/themes')
                    ),
                    'mailing' => array(
                        'active' => true, 'url' => $this->url->generate('admin/settings/mail')
                    )
                )
            );
        }

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/mail-settings.html.twig',
            $data
        );
    }

    /**
     * Edit current settings
     *
     * @param Request     $request
     * @return mixed
     */
    public function editAction(Request $request)
    {
        /** @var \nv\Simplex\Model\Entity\Settings $settings */
        $settings = $this->settingsRepository->getCurrent();
        $form = $this->form->create(new SettingsType($this->settingsRepository), $settings);

        if ($request->isMethod('POST')) {
            $files = $request->files;
            $form->bind($request);
            if ($form->isValid()) {
                foreach ($files as $uploadedFile) {
                    if (array_key_exists('siteLogo', $uploadedFile)) {
                        if ($uploadedFile['siteLogo'] instanceof UploadedFile) {
                            $logo = new Image();
                            $logo->setFile($uploadedFile['siteLogo']);
                            $logo->setInLibrary(false);
                            $logo->setMediaCategory('logo');
                            $settings->setSiteLogo($logo);
                        }
                    }
                }
                $this->settingsRepository->save($settings);

                return new RedirectResponse($this->url->generate('admin/settings'));
            }
        }

        $data = array(
            'form' => $form->createView(),
            'settings' => $settings,
            'title' => 'Edit settings',
            'tabs' => array(
                'panels' => array(
                    'settings' => array(
                        'active' => true, 'url' => $this->url->generate('admin/settings')
                    ),
                    'media' => array(
                        'active' => false, 'url' => $this->url->generate('admin/media/settings')
                    ),
                    'themes' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings/themes')
                    ),
                    'mailing' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings/mail')
                    )
                )
            )
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/settings.html.twig', $data);
    }

    /**
     * Delete settings instance
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        /** @var \nv\Simplex\Model\Entity\Settings $set */
        $set = $this->settingsRepository->findOneBy(array('id' => $request->get('id')));
        $this->settingsRepository->delete($set);

        $redirect = $this->url->generate('admin/settings');
        return new RedirectResponse($redirect);
    }

    /**
     * Active instance of settings
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateAction(Request $request, Application $app)
    {
        /** @var \nv\Simplex\Model\Entity\Settings $set */
        $set = $this->settingsRepository->findOneBy(array('id' => $request->get('id')));
        $current = $this->settingsRepository->getCurrent();
        $current->setCurrent(false);
        $set->setCurrent(true);

        $this->settingsRepository->save($current);
        $this->settingsRepository->save($set);

        /*
        $app['orm.em']->persist($current);
        $app['orm.em']->persist($set);
        $app['orm.em']->flush();
        */

        $redirect = $this->url->generate('admin/settings');
        return new RedirectResponse($redirect);
    }

    /**
     * Theme file uploader
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadThemeFile(Request $request)
    {
        $files = $request->files;
        $type = $request->get('type');
        foreach ($files as $uploadedFile) {
            if ($uploadedFile instanceof UploadedFile) {
                $dir = dirname(dirname(dirname(__DIR__))) . '/web/uploads/';
                $uploadedFile->move($dir, $uploadedFile->getClientOriginalName());

                $zip = new \ZipArchive();
                $zip->open($dir . $uploadedFile->getClientOriginalName());
                $zip->extractTo(dirname(dirname(dirname(__DIR__))) . '/web/templates/' . $type . '/');
                $zip->close();
            }
        }

        return new JsonResponse([$files]);
    }

    /**
     * Add new themes form
     *
     * @return mixed
     */
    public function addThemeAction()
    {
        $form = $this->form->createNamedBuilder(null, 'form', array())->getForm();
        $data['form'] = $form->createView();

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/upload-theme-form.html.twig',
            $data
        );
    }

    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return JsonResponse
     */
    public function analyzePostsAction(Request $request, Application $app)
    {
        $posts = $app['repository.post']->get();
        $images = $app['repository.media']->getImages();
        $videos = $app['repository.media']->getVideos();
        $exposed = array();
        $published = array();
        $tagged = array();
        $creationTimes = array('MON' => 0, 'TUE' => 0, 'WED' => 0, 'THU' => 0, 'FRI' => 0, 'SAT' => 0, 'SUN' => 0);
        $imgCreationTimes = array('MON' => 0, 'TUE' => 0, 'WED' => 0, 'THU' => 0, 'FRI' => 0, 'SAT' => 0, 'SUN' => 0);
        $vidCreationTimes = array('MON' => 0, 'TUE' => 0, 'WED' => 0, 'THU' => 0, 'FRI' => 0, 'SAT' => 0, 'SUN' => 0);

        foreach ($posts as $post) {
            /** @var $post \nv\Simplex\Model\Entity\Post */
            if ($post->getExposed()) {
                $exposed[] = $post;
            }

            if ($post->getPublished()) {
                $published[] = $post;
            }

            if (count($post->getTags()) > 0) {
                $tagged[] = $post;
            }

            $postList[] = $post->getId();
            $ct = strtoupper($post->getCreatedAt()->format('D'));
            $creationTimes[$ct]++;
        }

        /** @var \nv\Simplex\Model\Entity\Image $image */
        foreach ($images as $image) {
            $ct = strtoupper($image->getCreatedAt()->format('D'));

            $imgCreationTimes[$ct]++;
        }

        /** @var \nv\Simplex\Model\Entity\Video $video */
        foreach ($videos as $video) {
            $ct = strtoupper($video->getCreatedAt()->format('D'));

            $vidCreationTimes[$ct]++;
        }

        $lineChartData = array(
            'labels' => array('MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'),
            'datasets' => array(
                array(
                    'label' => 'Posts',
                    'fillColor' => 'rgba(220,220,220,0.2)',
                    'strokeColor' => 'rgba(220,220,220,1)',
                    'pointColor' => 'rgba(220,220,220,1)',
                    'pointStrokeColor' => '#fff',
                    'pointHighlightFill' => '#fff',
                    'pointHighlightStroke' => 'rgba(220,220,220,1)',
                    'data' => array_values($creationTimes)
                ),
                array(
                    'label' => 'Images',
                    'fillColor' => 'rgba(151,187,205,0.2)',
                    'strokeColor' => 'rgba(151,187,205,1)',
                    'pointColor' => 'rgba(151,187,205,1)',
                    'pointStrokeColor' => '#fff',
                    'pointHighlightFill' => '#fff',
                    'pointHighlightStroke' => 'rgba(151,187,205,1)',
                    'data' => array_values($imgCreationTimes)
                ),
                array(
                    'label' => 'Videos',
                    'fillColor' => 'rgba(80,187,205,0.2)',
                    'strokeColor' => 'rgba(80,187,205,1)',
                    'pointColor' => 'rgba(80,187,205,1)',
                    'pointStrokeColor' => '#F7464A',
                    'pointHighlightFill' => '#F7464A',
                    'pointHighlightStroke' => 'rgba(151,187,205,1)',
                    'data' => array_values($vidCreationTimes)
                )
            )
        );

        return new JsonResponse($lineChartData, 200);
    }
}
