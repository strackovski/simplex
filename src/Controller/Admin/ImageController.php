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
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Form\ImageType;
use nv\Simplex\Model\Entity\Image;
use nv\Simplex\Model\Entity\Metadata;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImageController
 *
 * Defines actions to perform on requests regarding Image objects.
 *
 * @package nv\Simplex\Controller
 */
class ImageController
{
    /**
     * Index image items: display all images with images template
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        /** @var $form Form */
        $form = $app['form.factory']->createNamedBuilder(null, 'form',
            array('test' => ''))
            ->add('test', 'text')
            ->getForm();

        $data['form'] = $form->createView();
        $data['images'] = $app['repository.media']->getLibraryImages();

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/images.html.twig', $data);

    }

    /**
     * View single image item
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function viewAction(Request $request, Application $app)
    {
        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/image.html.twig');
    }


    /**
     * Upload image
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request, Application $app)
    {
        $files = $request->files;
        /** @var UploadedFile $uploadedFile */
        foreach ($files as $uploadedFile) {
            $image = new Image();
            $image->setInLibrary(true);
            $image->setFile($uploadedFile);
            $image->setName($uploadedFile->getClientOriginalName());
            $image->setMetadata($metadata = new Metadata());
            $app['repository.media']->save($image);
            $image->setMetadata($metadata->setData($image->getManager()->metadata()));
            $app['orm.em']->flush();

            try{
                $image->getManager()->thumbnail($app['imagine'], $app['settings']->getImageResizeDimensions());

                $app['settings']->getImageAutoCrop() ?
                    $image->getManager()->autoCrop($app['imagine'], null) :
                    $image->getManager()->crop($app['imagine'], $app['settings']->getImageResizeDimensions('crop'));

                if ($app['settings']->getWatermarkMedia() and $app['settings']->getWatermark()) {
                    $image->getManager()->watermark(
                        $app['imagine'],
                        APPLICATION_ROOT_PATH . '/web/uploads/' . $app['settings']->getWatermark(),
                        $app['settings']->getWatermarkPosition()
                    );
                }
                $image->getManager()->cleanUp($app['settings']->getImageKeepOriginal());
            } catch (\Exception $e) {
                $app['repository.media']->delete($image);
                $app['monolog']->addError(
                    get_class($this) . " caught exception \"{$e->getMessage()}\" from {$e->getFile()}:{$e->getLine()}"
                );
            }
        }

        return new JsonResponse([$files]);
    }

    /**
     * Add image
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function addAction(Request $request, Application $app)
    {
        $image = new Image();
        /** @var $form Form */
        $form = $app['form.factory']->create(new ImageType(), $image);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $files = $request->files->get($form->getName());
                $image->setFile($files['file']);
                $app['repository.media']->save($image);
                $message = 'The post ' . $image->getTitle() . ' has been saved.';
                $app['session']->getFlashBag()->add('success', $message);
                $redirect = $app['url_generator']->generate('admin/posts', array('new_post' => $image->getId()));

                return $app->redirect($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new image',
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/form.html.twig', $data);
    }
}