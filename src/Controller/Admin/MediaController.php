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

use nv\Simplex\Form\MediaSettingsType;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Model\Entity\Image;

/**
 * Class MediaController
 *
 * Defines actions to perform on requests regarding Media objects.
 *
 * @package nv\Simplex\Controller\Admin
 */
class MediaController
{
    /**
     * Index media items
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        $form = $app['form.factory']->createNamedBuilder(
            null,
            'form',
            array('test' => '')
        )
        ->add('test', 'text')
        ->getForm();

        $token = $app['security']->getToken();

        if (null !== $token) {
            $data['user'] = $token->getUser();
        }

        $data['images'] = $app['repository.media']->getLibraryImages();
        $data['form'] = $form->createView();

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/media.html.twig', $data);
    }

    /**
     * Delete media item
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Application $app)
    {
        $objectIds = array($request->get('id'));
        $type = '';

        if ($request->get('multi')) {
            $objectIds = json_decode($request->get('id'));
        }

        foreach ($objectIds as $objectId) {
            $item = $app['repository.media']->filter(array('id' => $objectId));
            $type = $item instanceof Image ? 'images' : 'videos';
            $app['repository.media']->delete($item);
        }
        $redirect = $app['url_generator']->generate("admin/media/{$type}");

        return $app->redirect($redirect);
    }

    /**
     * View/play media item
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function viewAction(Request $request, Application $app)
    {
        $item = $app['repository.media']->filter(array('id' => $request->get('id')));

        return $app['twig']->render(
            'admin/'.$app['settings']->getAdminTheme().'/views/media-view.html.twig',
            array('item' => $item)
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
        $settings = $app['repository.settings']->getCurrent();
        $form = $app['form.factory']->create(new MediaSettingsType($app['repository.settings']), $settings);

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

                $settings->setImageResizeDimensions($dimensions);

                foreach ($files as $uploadedFile) {
                    if (array_key_exists('watermark', $uploadedFile)) {
                        if ($uploadedFile['watermark'] instanceof UploadedFile) {
                            $wm = new Image();
                            $wm->setFile($uploadedFile['watermark']);
                            $wm->setInLibrary(false);
                            $wm->setMediaCategory('watermark');
                            $settings->setWatermark($wm);
                        }
                    }
                }
                $app['repository.settings']->saves($settings);
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
                        'active' => false, 'url' => $app['url_generator']->generate('admin/settings')
                    ),
                    'media' => array(
                        'active' => true, 'url' => $app['url_generator']->generate('admin/media/settings')
                    ),
                    'themes' => array(
                        'active' => false, 'url' => $app['url_generator']->generate('admin/settings/themes')
                    ),
                    'mailing' => array(
                        'active' => false, 'url' => $app['url_generator']->generate('admin/settings/mail')
                    )
                )
            );
        }

        return $app['twig']->render(
            'admin/'.$app['settings']->getAdminTheme().'/widgets/media-settings.html.twig',
            $data
        );
    }

    /**
     * Resample existing media in library
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function resampleMediaLibraryAction(Request $request, Application $app)
    {
        $current = $app['repository.settings']->getCurrent();

        return $app['twig']->render(
            'admin/'.$app['settings']->getAdminTheme().'/widgets/library-resample.html.twig',
            array('settings' => $current)
        );
    }
}
