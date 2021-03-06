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

use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Core\Media\ImageManager;
use nv\Simplex\Form\MediaType;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Model\Entity\Image;
use nv\Simplex\Model\Entity\Metadata;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\MediaRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ImageController
 *
 * Defines actions to perform on requests regarding Image objects.
 *
 * @package nv\Simplex\Controller
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ImageController extends ActionControllerAbstract
{
    /** @var MediaRepository */
    private $media;

    /** @var ImageManager */
    private $manager;

    public function __construct(
        MediaRepository $mediaRepository,
        ImageManager $manager,
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
        $this->manager = $manager;
    }

    /**
     * Index image items: display all images with images template
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        /** @var $form Form */
        $form = $this->form->createNamedBuilder(
            null,
            'form',
            array('test' => '')
        )
        ->add('test', 'text')
        ->getForm();

        $data['form'] = $form->createView();
        $data['images'] = $this->media->getLibraryImages();

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/images.html.twig', $data);

    }

    /**
     * View single image item
     *
     * @param Request     $request
     * @return mixed
     */
    public function viewAction(Request $request)
    {
        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/image.html.twig');
    }


    /**
     * Upload image
     *
     * @param Request     $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $files = $request->files;
        $token = $this->security->getToken();
        /** @var UploadedFile $uploadedFile */
        foreach ($files as $uploadedFile) {
            $image = new Image();
            if (null !== $token) {
                $image->setAuthor($token->getUser());
            }
            $image->setPublished(true);
            $image->setInLibrary(true);
            $image->setFile($uploadedFile);
            $image->setName($uploadedFile->getClientOriginalName());
            $image->setMetadata($metadata = new Metadata());
            $this->media->save($image);
            $image->setMetadata($metadata->setData($this->manager->metadata($image)));

            try {
                $this->media->save($image);
            } catch (\Exception $e) {
                $this->media->delete($image);
            }
        }

        return new JsonResponse([$files]);
    }

    /**
     * Add image
     *
     * @param Request     $request
     * @return mixed
     */
    public function addAction(Request $request)
    {
        $image = new Image();
        /** @var $form Form */
        $form = $this->form->create(new MediaType(), $image);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $files = $request->files->get($form->getName());
                $image->setFile($files['file']);
                $this->media->save($image);
                $message = 'The post ' . $image->getTitle() . ' has been saved.';
                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/posts', array('new_post' => $image->getId()));

                return new RedirectResponse($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new image',
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/form.html.twig', $data);
    }
}
