<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Controller\Admin;

use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\MediaRepository;
use nv\Simplex\Model\Repository\PostRepository;
use nv\Simplex\Model\Repository\TagRepository;
use nv\Simplex\Model\Repository\PageRepository;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
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
use nv\Simplex\Model\Entity\User;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class PostController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller\Admin
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PostController extends ActionControllerAbstract
{
    /** @var PostRepository  */
    private $posts;

    /** @var PageRepository  */
    private $pages;

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
        PageRepository $pageRepository,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        PostManager $postManager,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->posts = $postRepository;
        $this->media = $mediaRepository;
        $this->tags = $tagRepository;
        $this->manager = $postManager;
        $this->pages = $pageRepository;
    }

    /**
     * Index posts
     *
     * @param Request     $request
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        $data['posts'] = $this->posts->get();
        $data['post'] = $this->posts->getLatest();
        $data['request'] = $request;

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/posts.html.twig',
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
     * View single post
     *
     * @param Request     $request
     * @return mixed
     */
    public function getAction(Request $request)
    {
        $post = $this->posts->findOneBy(array('id' => $request->get('post')));
        $data = array(
            'post' => $post,
            'request' => $request
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/post-detail.html.twig', $data);
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

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->form->create(new PostType($this->media->getLibraryMedia(), $this->pages), $post);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $images = $form->get('media')->getData();
                $pages = $form->get('pages')->getData();
                $tags = $form->get('tags')->getData();

                if (count($tags) > 0) {
                    $this->manager->tag($post, $tags);
                }

                if (count($images) > 0) {
                    foreach ($images as $image) {
                        /** @var \nv\Simplex\Model\Entity\MediaItem $imageObj */
                        $imageObj = $this->media->findOneBy(array('id' => $image));
                        $post->addMediaItem($imageObj);
                    }
                }

                if (count($pages) > 0) {
                    foreach ($pages as $page) {
                        /** @var \nv\Simplex\Model\Entity\Page $pageObj */
                        $pageObj = $this->pages->findOneBy(array('id' => $page));
                        $post->addPage($pageObj);
                    }
                }

                if (null !== $token) {
                    $post->setAuthor($token->getUser());
                }

                /** @var \nv\Simplex\Core\Service\TwitterApiAccount $twitter */
                if ($twitter = $this->settings->getApiAccount('twitter', 1)) {
                    if (in_array('twitter', $form->get('channels')->getData())) {
                        $token = $twitter->getAccessToken();
                        $connection = new TwitterOAuth(
                            $twitter->getConsumerKey(),
                            $twitter->getConsumerSecret(),
                            $token['oauth_token'],
                            $token['oauth_token_secret']
                        );
                        $connection->get("oauth/authenticate");
                        // @todo Check if twitter auth failed (access revoked)

                        if ($post->getMediaItems()->count() > 0) {
                            $mediaIds = array();
                            foreach ($post->getMediaItems() as $media) {
                                $mediaUpload = $connection->upload('media/upload', array('media' => $media->getVariations()['small']));
                                $mediaIds[] = $mediaUpload->media_id_string;
                            }
                            $parameters = array(
                                'status' => substr($post->getSubtitle(), 0, 130),
                                'media_ids' => implode(',', $mediaIds)
                            );
                            $connection->post("statuses/update", $parameters);
                        } else {
                            $connection->post("statuses/update", array("status" => substr($post->getSubtitle(), 0, 130)));
                        }

                        $connection->post("statuses/update", array("status" => substr($post->getSubtitle(), 0, 130)));


                        if ($connection->lastHttpCode() == 200) {
                            $this->logger->addInfo('Tweeted about post #' . $post->getId());
                        } else {
                            $this->logger->addError('Failed tweeting about post #' . $post->getId() . ', c');
                        }
                    }
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
        $form = $this->form->create(new PostType($this->media->getLibraryMedia(), $this->pages), $post);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $images = $form->get('media')->getData();
                $tags = $form->get('tags')->getData();
                $pages = $form->get('pages')->getData();

                try {
                    $this->manager->tag($post, $tags);
                } catch (\Exception $e) {
                    $this->logger->addError(
                        'Failed tagging metadata for post #' . $post->getId() . ': ' . $e->getMessage()
                    );
                }

                if (count($images) > 0) {
                    $post->clearMedia();
                    foreach ($images as $image) {
                        $imageObj = $this->media->findOneBy(array('id' => $image));
                        $post->addMediaItem($imageObj);
                    }
                }


                if (count($pages) > 0) {
                    foreach ($pages as $page) {
                        /** @var \nv\Simplex\Model\Entity\Page $pageObj */
                        $pageObj = $this->pages->findOneBy(array('id' => $page));
                        $post->addPage($pageObj);
                    }
                }

                if (null !== $token) {
                    /** @var $user User */
                    $post->setEditor($token->getUser());
                }

                $this->posts->save($post);
                $message = 'Changes saved to post "' . $post->getTitle() . '"';
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
        }
        //$redirect = $this->url->generate('admin/posts');

        //return new RedirectResponse($redirect);

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/post-list.html.twig',
            array('posts' => $this->posts->get())
        );
    }

    public function toggleAction(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');
        $item = $this->posts->findOneBy(array('id' => $id));

        if ($item instanceof Post) {
            if (in_array($status, $array = array('published', 'exposed'))) {
                if ($status === 'published') {
                    if ($item->getPublished()) {
                        $item->setPublished(false);
                    } else {
                        $item->setPublished(true);
                    }
                } elseif ($status === 'exposed') {
                    if ($item->getExposed()) {
                        $item->setExposed(false);
                    } else {
                        $item->setExposed(true);
                    }
                }

                $this->posts->save($item);
                return new JsonResponse(true);
            }
        }

        return false;
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

    public function helpAction()
    {
        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/help-posts.html.twig');
    }
}
