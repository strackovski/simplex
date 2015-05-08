<?php

/*
 * This software is licensed under the Apache 2 license, quoted below.
 *
 * Copyright 2015 NV3
 * Copyright 2015 Vladimir Stračkovski <vlado@nv3.org>

 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace nv\Simplex\Controller\Admin;

use nv\Simplex\Core\Service\GoogleApiAccount;
use nv\Simplex\Core\Service\TwitterApiAccount;
use nv\Simplex\Form\ApiSettingsType;
use nv\Simplex\Form\MailSettingsType;
use nv\Simplex\Form\ThemeSettingsType;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\SettingsRepository;
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
 * @author Vladimir Stračkovski <vlado@nv3.org>
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

    private $gclient;

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
        $latest['posts'] = $app['repository.post']->getLatest(5);
        $latest['media'] = $app['repository.media']->getLatest(5);
        $latest['pages'] = $app['repository.page']->getLatest(5);
        $settings = $this->settingsRepository->getCurrent();

        $data = array(
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
     * @todo Fix error when generating Settings XML
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
        /** @var \Symfony\Component\Form\Form $form */
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
                    ),
                    'integration_services' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings/api')
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
     * Integration services section
     *
     * @param Request $request
     * @return string
     */
    public function apiSettingsAction(Request $request)
    {
        /** @var \nv\Simplex\Model\Entity\Settings $settings */
        $settings = $this->settingsRepository->getCurrent();
        $form = $this->form->create(new ApiSettingsType($this->settingsRepository), $settings);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $params = $request->request->get('settings');
            if ($form->isValid()) {
                $ga = new GoogleApiAccount($params['clientId'], $params['clientSecret']);
                if (array_key_exists('enableGoogleApi', $params)) {
                    $ga->setEnabled($params['enableGoogleApi']);
                } else {
                    $ga->setEnabled(false);
                }

                $ga->setAppName($params['appName']);
                $ga->setRedirectUri($params['redirectUri']);
                $ga->setAccountLogin($params['accountLogin']);
                $ga->setApiKey($params['apiKey']);

                if ($existing_ga = $this->settings->getApiAccount('google', 1)) {
                    $ga->setAccessToken($existing_ga->getAccessToken());
                    $ga->setRefreshToken($existing_ga->getRefreshToken());
                }

                $settings->addApiAccount($ga);

                $ta = new TwitterApiAccount($params['twitter_ConsumerKey'], $params['twitter_ConsumerSecret']);
                if (array_key_exists('enableTwitterApi', $params)) {
                    $ta->setEnabled($params['enableTwitterApi']);
                } else {
                    $ta->setEnabled(false);
                }

                $ta->setAccountLogin($params['twitter_AccountLogin']);
                $ta->setOauthCallback($params['twitter_OauthCallback']);

                if ($existing_ta = $this->settings->getApiAccount('twitter', 1)) {
                    $ta->setAccessToken($existing_ta->getAccessToken());
                }

                $settings->addApiAccount($ta);
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
                        'active' => false, 'url' => $this->url->generate('admin/settings/mail')
                    ),
                    'integration_services' => array(
                        'active' => true, 'url' => $this->url->generate('admin/settings/api')
                    )
                )
            );
        }

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/api-settings.html.twig',
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
        /** @var \Symfony\Component\Form\Form $form */
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
                    ),
                    'integration_services' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings/api')
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
        /** @var \Symfony\Component\Form\Form $form */
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
                    ),
                    'integration_services' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings/api')
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
}
