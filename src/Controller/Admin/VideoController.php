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

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Video;

/**
 * Class ImageController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller
 */
class VideoController
{
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
        $form = $app['form.factory']->createNamedBuilder(null, 'form',
            array('test' => ''))
            ->add('test', 'text', array(
                'required' => false,
                'attr' => array(
                    'name' => 'test'
                )
            ))
            ->getForm();

        $data['form'] = $form->createView();
        $data['videos'] = $app['repository.media']->getLibraryVideos();

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/videos.html.twig', $data);
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
        $form = $app['form.factory']->createNamedBuilder(null, 'form',
            array('test' => ''))
            ->add('test', 'text', array(
                'label' => 'Email',
                'required' => false,
                'attr' => array(
                    'name' => 'test'
                )
            ))
            ->getForm();

        $data['form'] = $form->createView();
        $data['videos'] = $app['repository.media']->getVideos();

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/partials/video-list.html.twig', $data);
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
            $app['repository.media']->save($video);

            try{
               $video->setMetadata($metadata->setData($video->getManager()->metadata()));
               $app['orm.em']->flush();

                $video->getManager()->thumbnail($app['imagine'], $app['settings']->getImageResizeDimensions());
                $video->getManager()->autoCrop($app['imagine'], null);

                if (strtolower(pathinfo($video->getAbsolutePath(), PATHINFO_EXTENSION)) == 'avi') {
                    $video->getManager()->recode();
                }
            } catch (\Exception $e) {
                $app['repository.media']->delete($video);
                $app['monolog']->addError(
                    get_class($this) . " caught exception \"{$e->getMessage()}\" from {$e->getFile()}:{$e->getLine()}"
                );
            }
        }

        return new JsonResponse([$files]);
    }
}