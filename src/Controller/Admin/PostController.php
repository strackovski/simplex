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
use nv\Simplex\Core\Post\PostManager;
use nv\Simplex\Form\PostType;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Post;

/**
 * Class PostController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller\Admin
 */
class PostController
{
    /**
     * Index posts
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        $data['posts'] = $app['repository.post']->get();
        $data['post'] = $app['repository.post']->getLatest();
        $data['request'] = $request;

        return $app['twig']->render(
            'admin/'.$app['settings']->getAdminTheme().'/views/posts.html.twig',
            $data
        );
    }

    /**
     * List tags in json format
     *
     * @param Request $request
     * @param Application $app
     * @return bool|JsonResponse
     */
    public function tagsListAction(Request $request, Application $app)
    {
        if ($app['security']->isGranted('ROLE_ADMIN')) {
            $users = $app['repository.post']->getTags(true);

            return new JsonResponse($users, 200);
        }

        return false;
    }


    /**
     * View single post
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function viewAction(Request $request, Application $app)
    {
        $post = $app['repository.post']->findOneBy(array('id' => $request->get('post')));
        $data = array(
            'post' => $post,
            'request' => $request
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/post.html.twig', $data);
    }

    /**
     * Add new post
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function addAction(Request $request, Application $app)
    {
        $token = $app['security']->getToken();
        $post = new Post(
            $request->request->get('title'),
            $request->request->get('body')
        );
        $post->registerObserver(
            $pm = new PostManager($post, $app)
        );
        $form = $app['form.factory']->create(new PostType($app['orm.em']), $post);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $images = $form->get('media')->getData();
                $tags = $form->get('tags')->getData();

                if (count($tags) > 0) {
                    $pm->tag($tags);
                }

                if (count($images) > 0) {
                    foreach ($images as $image) {
                        $imageObj = $app['repository.media']->findOneBy(array('id' => $image));
                        $post->addMediaItem($imageObj);
                    }
                }

                if (null !== $token) {
                    $post->setAuthor($token->getUser());
                }

                $pm->slug();
                $app['repository.post']->save($post);
                $message = 'The post <strong>' . $post->getTitle() . '</strong> has been saved.';
                $app['session']->getFlashBag()->add('success', $message);
                $redirect = $app['url_generator']->generate('admin/posts');

                return $app->redirect($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new post',
            'request' => $request
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/post-form.html.twig', $data);
    }

    /**
     * Edit post
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function editAction(Request $request, Application $app)
    {
        $token = $app['security']->getToken();
        /** @var Post $post */
        $post = $app['repository.post']->findOneBy(array('id' => $request->get('post')));
        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $app['form.factory']->create(new PostType($app['orm.em']), $post);

        $pm = new PostManager($post, $app);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $images = $form->get('media')->getData();
                $tags = $form->get('tags')->getData();
                $pm->tag($tags);

                if (count($images) > 0) {
                    $post->clearMedia();
                    foreach ($images as $image) {
                        $imageObj = $app['repository.media']->findOneBy(array('id' => $image));
                        $post->addMediaItem($imageObj);
                    }
                }

                if (null !== $token) {
                    $post->setEditor($token->getUser());
                }

                $pm->slug();
                $app['repository.post']->save($post);
                $message = 'Changes saved to ' . $post->getTitle() . '.';
                $app['session']->getFlashBag()->add('success', $message);
                $redirect = $app['url_generator']->generate('admin/posts');

                return $app->redirect($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'post' => $post,
            'request' => $request,
            'title' => 'Edit post',
        );

        return $app['twig']->render(
            'admin/'.$app['settings']->getAdminTheme().'/views/post-form.html.twig',
            $data
        );
    }

    /**
     * Delete post
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function deleteAction(Request $request, Application $app)
    {
        $post = $app['repository.post']->findOneBy(array('id' => $request->get('post')));
        if ($post instanceof Post) {
            if ($post->getMetadata() instanceof Metadata) {
                $app['orm.em']->remove($post->getMetadata());
                $post->setMetadata(null);
            }
            $app['orm.em']->remove($post);
            $app['orm.em']->flush();
        }
        $redirect = $app['url_generator']->generate('admin/posts');

        return $app->redirect($redirect);
    }

    /**
     * Filter posts
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function filterAction(Request $request, Application $app)
    {
        $key = $request->get('filter_key');
        $val = $request->get('filter_val');
        $data['posts'] = $app['repository.post']->get(array($key => $val));
        $data['request'] = $request;

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/posts.html.twig', $data);
    }
}
