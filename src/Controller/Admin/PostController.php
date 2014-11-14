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

use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\MediaRepository;
use nv\Simplex\Model\Repository\PostRepository;
use nv\Simplex\Model\Repository\TagRepository;
use Silex\Application;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Core\Post\PostManager;
use nv\Simplex\Form\PostType;
use nv\Simplex\Model\Entity\Post;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class PostController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller\Admin
 */
class PostController extends ActionControllerAbstract
{
    /** @var PostRepository  */
    private $posts;

    /** @var MediaRepository  */
    private $media;

    /** @var TagRepository */
    private $tags;

    /** @var  PostManager */
    private $manager;

    public function __construct(
        PostRepository $postRepository,
        MediaRepository $mediaRepository,
        TagRepository $tagRepository,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        PostManager $postManager
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url);
        $this->posts = $postRepository;
        $this->media = $mediaRepository;
        $this->tags = $tagRepository;
        $this->manager = $postManager;
    }

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
     * @return bool|JsonResponse
     */
    public function tagsListAction()
    {
        $tags = $this->tags->findAll();
        return new JsonResponse($tags, 200);
    }


    /**
     * View single post
     *
     * @param Request     $request
     * @return mixed
     */
    public function viewAction(Request $request)
    {
        $post = $this->posts->findOneBy(array('id' => $request->get('post')));
        $data = array(
            'post' => $post,
            'request' => $request
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/post.html.twig', $data);
    }

    /**
     * Add new post
     *
     * @param Request     $request
     * @return mixed
     */
    public function addAction(Request $request)
    {
        $token = $this->security->getToken();
        $post = new Post(
            $request->request->get('title'),
            $request->request->get('body')
        );

        $form = $this->form->create(new PostType($this->media->getLibraryMedia()), $post);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $images = $form->get('media')->getData();
                $tags = $form->get('tags')->getData();

                if (count($tags) > 0) {
                    $this->manager->tag($post, $tags);
                }

                if (count($images) > 0) {
                    foreach ($images as $image) {
                        $imageObj = $this->media->findOneBy(array('id' => $image));
                        $post->addMediaItem($imageObj);
                    }
                }

                if (null !== $token) {
                    $post->setAuthor($token->getUser());
                }

                $this->manager->slug($post);

                if ($this->settings->getEnableAnnotations()) {
                    $this->manager->metadata($post);
                }

                $this->posts->save($post);
                $message = 'The post <strong>' . $post->getTitle() . '</strong> has been saved.';
                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/posts');

                return new RedirectResponse($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new post',
            'request' => $request
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/post-form.html.twig', $data);
    }

    /**
     * Edit post
     *
     * @param Request     $request
     * @return mixed
     */
    public function editAction(Request $request)
    {
        $token = $this->security->getToken();
        /** @var Post $post */
        $post = $this->posts->findOneBy(array('id' => $request->get('post')));
        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->form->create(new PostType($this->media->getLibraryMedia()), $post);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $images = $form->get('media')->getData();
                $tags = $form->get('tags')->getData();
                $this->manager->tag($post, $tags);

                if (count($images) > 0) {
                    $post->clearMedia();
                    foreach ($images as $image) {
                        $imageObj = $this->media->findOneBy(array('id' => $image));
                        $post->addMediaItem($imageObj);
                    }
                }

                if (null !== $token) {
                    $post->setEditor($token->getUser());
                }

                $this->manager->slug($post);

                if ($this->settings->getEnableAnnotations()) {
                    $this->manager->metadata($post);
                }

                $this->posts->save($post);
                $message = 'Changes saved to ' . $post->getTitle() . '.';
                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/posts');

                return new RedirectResponse($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'post' => $post,
            'request' => $request,
            'title' => 'Edit post',
        );

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/post-form.html.twig',
            $data
        );
    }

    /**
     * Delete post
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function deleteAction(Request $request)
    {
        $post = $this->posts->findOneBy(array('id' => $request->get('post')));
        if ($post instanceof Post) {
            $this->posts->delete($post);
            /*
            if ($post->getMetadata() instanceof Metadata) {
                $app['orm.em']->remove($post->getMetadata());
                $post->setMetadata(null);
            }
            $app['orm.em']->remove($post);
            $app['orm.em']->flush();
            */
        }
        $redirect = $this->url->generate('admin/posts');

        return new RedirectResponse($redirect);
    }

    /**
     * Filter posts
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function filterAction(Request $request)
    {
        $key = $request->get('filter_key');
        $val = $request->get('filter_val');
        $data['posts'] = $this->posts->get(array($key => $val));
        $data['request'] = $request;

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/posts.html.twig', $data);
    }
}
