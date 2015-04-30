<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 NV3, Vladimir Stračkovski <vlado@nv3.org>
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Controller\Admin;

use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Form\MediaSettingsType;
use nv\Simplex\Form\MediaType;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\MediaRepository;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Model\Entity\Image;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class MediaController
 *
 * Defines actions to perform on requests regarding Media objects.
 *
 * @package nv\Simplex\Controller\Admin
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class MediaController extends ActionControllerAbstract
{
    /** @var MediaRepository */
    private $media;

    public function __construct(
        MediaRepository $mediaRepository,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->media = $mediaRepository;
    }

    /**
     * Index media items
     *
     * @param Request     $request
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        $form = $this->form->createNamedBuilder(
            null,
            'form',
            array('test' => '')
        )
        ->add('test', 'text')
        ->getForm();

        $token = $this->security->getToken();

        if (null !== $token) {
            $data['user'] = $token->getUser();
        }

        $data['images'] = $this->media->getLibraryImages();
        $data['form'] = $form->createView();

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/media.html.twig', $data);
    }

    /**
     * Delete media item
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $objectIds = array($request->get('id'));
        $type = '';

        if ($request->get('multi')) {
            $objectIds = json_decode($request->get('id'));
        }

        foreach ($objectIds as $objectId) {
            $item = $this->media->filter(array('id' => $objectId));
            $type = $item instanceof Image ? 'images' : 'videos';
            $this->media->delete($item);
        }
        $redirect = $this->url->generate("admin/media/{$type}");

        return new RedirectResponse($redirect);
    }

    /**
     * View/play media item
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function viewAction(Request $request)
    {
        $item = $this->media->filter(array('id' => $request->get('id')));
        $form = $this->form->create(new MediaType(), $item);

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/media-view.html.twig',
            array('item' => $item, 'form' => $form->createView())
        );
    }

    /**
     * Configure media settings
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function settingsAction(Request $request, Application $app)
    {
        $form = $this->form->create(new MediaSettingsType(), $this->settings);

        if ($request->isMethod('POST')) {
            $files = $request->files;
            $form->bind($request);
            if ($form->isValid()) {
                $dimensions = array(
                    'small' => array(
                        $form->get('image_resize_small_width')->getData(),
                        $form->get('image_resize_small_height')->getData()
                    ),
                    'medium' => array(
                        $form->get('image_resize_medium_width')->getData(),
                        $form->get('image_resize_medium_height')->getData()
                    ),
                    'large' => array(
                        $form->get('image_resize_large_width')->getData(),
                        $form->get('image_resize_large_height')->getData()
                    ),
                    'crop' => array(
                        $form->get('image_crop_width')->getData(),
                        $form->get('image_crop_height')->getData(),
                    )
                );

                $this->settings->setImageResizeDimensions($dimensions);

                foreach ($files as $uploadedFile) {
                    if (array_key_exists('watermark', $uploadedFile)) {
                        if ($uploadedFile['watermark'] instanceof UploadedFile) {
                            $wm = new Image();
                            $wm->setFile($uploadedFile['watermark']);
                            $wm->setInLibrary(false);
                            $wm->setMediaCategory('watermark');
                            $this->settings->setWatermark($wm);
                        }
                    }
                }
                $app['repository.settings']->save($this->settings);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'settings' => $this->settings,
            'title' => 'Edit settings',
        );

        if (!$request->isXmlHttpRequest()) {
            $data['tabs'] = array(
                'panels' => array(
                    'settings' => array(
                        'active' => false, 'url' => $this->url->generate('admin/settings')
                    ),
                    'media' => array(
                        'active' => true, 'url' => $this->url->generate('admin/media/settings')
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
            );
        }

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/media-settings.html.twig',
            $data
        );
    }

    /**
     * Resample existing media in library
     *
     * @param Request $request
     * @return mixed
     */
    public function resampleMediaLibraryAction(Request $request)
    {
        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/library-resample.html.twig',
            array('settings' => $this->settings)
        );
    }

    public function helpAction()
    {
        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/help-media.html.twig');
    }

    /**
     * Edit media
     *
     * @param Request     $request
     * @return mixed
     */
    public function editAction(Request $request)
    {
        $image = $this->media->findOneBy(array('id' => $request->get('id')));

        /** @var $form Form */
        $form = $this->form->create(new MediaType(), $image);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->media->save($image);
                return 1;
            }
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Edit image',
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/media-form.html.twig', $data);
    }
}
