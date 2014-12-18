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
use nv\Simplex\Core\Media\VideoManager;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\MediaRepository;
use Silex\Application;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Model\Entity\Video;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class VideoController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class VideoController extends ActionControllerAbstract
{
    /** @var MediaRepository */
    private $media;

    /** @var  VideoManager */
    private $manager;

    public function __construct(
        MediaRepository $mediaRepository,
        VideoManager $manager,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url);
        $this->media = $mediaRepository;
        $this->manager = $manager;
    }

    /**
     * Index image items
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
        ->add('test', 'text', array(
            'required' => false,
            'attr' => array(
                'name' => 'test'
            )
        ))->getForm();

        $data['form'] = $form->createView();
        $data['videos'] = $this->media->getLibraryVideos();

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/videos.html.twig', $data);
    }

    /**
     * Index image items
     *
     * @param Request     $request
     * @return mixed
     */
    public function listVideoAction(Request $request)
    {
        $form = $this->form->createNamedBuilder(
            null,
            'form',
            array('test' => '')
        )
        ->add(
            'test',
            'text',
            array(
                'label' => 'Email',
                'required' => false,
                'attr' => array(
                    'name' => 'test'
                )
            )
        )->getForm();

        $data['form'] = $form->createView();
        $data['videos'] = $this->media->getVideos();

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/partials/video-list.html.twig',
            $data
        );
    }

    /**
     * Upload video
     *
     * @param Request     $request
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $files = $request->files;
        $token = $this->security->getToken();

        foreach ($files as $uploadedFile) {
            $video = new Video();
            if (null !== $token) {
                $video->setAuthor($token->getUser());
            }
            $video->setInLibrary(true);
            $video->setFile($uploadedFile);
            $video->setName($uploadedFile->getClientOriginalName());
            $video->setMetadata($metadata = new Metadata());
            $this->media->save($video);
            $video->setMetadata($metadata->setData($this->manager->metadata($video)));

            try {
                $this->media->save($video);
            } catch (\Exception $e) {
                $this->media->delete($video);
            }
        }

        return new JsonResponse([$files]);
    }
}
