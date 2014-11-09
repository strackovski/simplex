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

use Imagine\Image\ImagineInterface;
use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\MediaRepository;
use Silex\Application;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Model\Entity\Metadata;
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
 */
class VideoController extends ActionControllerAbstract
{
    /** @var MediaRepository */
    private $media;

    /** @var  ImagineInterface */
    private $imageLib;

    public function __construct(
        MediaRepository $mediaRepository,
        ImagineInterface $imageLib,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url);
        $this->media = $mediaRepository;
        $this->imageLib = $imageLib;
    }

    /**
     * Index image items
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
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
     * @param Application $app
     *
     * @return mixed
     */
    public function listVideoAction(Request $request, Application $app)
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
     * @param Application $app
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request, Application $app)
    {
        $files = $request->files;
        foreach ($files as $uploadedFile) {
            $video = new Video();
            $video->setInLibrary(true);
            $video->setFile($uploadedFile);
            $video->setName($uploadedFile->getClientOriginalName());
            $metadata = new Metadata(array('vddata'));
            $this->media->save($video);

            try {
                $video->setMetadata($metadata->setData($video->getManager()->metadata()));
                $this->media->save($video);
                $video->getManager()->thumbnail($this->imageLib, $this->settings->getImageResizeDimensions());
                $video->getManager()->autoCrop($this->imageLib, null);

                if (strtolower(pathinfo($video->getAbsolutePath(), PATHINFO_EXTENSION)) == 'avi') {
                    $video->getManager()->recode();
                }
            } catch (\Exception $e) {
                $this->media->delete($video);
            }
        }

        return new JsonResponse([$files]);
    }
}
