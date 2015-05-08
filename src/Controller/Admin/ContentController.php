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
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\FormRepository;
use nv\Simplex\Model\Repository\MediaRepository;
use nv\Simplex\Model\Repository\PageRepository;
use nv\Simplex\Model\Repository\PostRepository;
use nv\Simplex\Model\Repository\TagRepository;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ContentController
 *
 * @package nv\Simplex\Controller\Admin
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ContentController extends ActionControllerAbstract
{
    /** @var PostRepository  */
    private $posts;

    /** @var PostRepository  */
    private $forms;

    /** @var MediaRepository  */
    private $media;

    /** @var PageRepository  */
    private $pages;

    /** @var TagRepository */
    private $tags;


    public function __construct(
        FormRepository $formRepository,
        PostRepository $postRepository,
        MediaRepository $mediaRepository,
        PageRepository $pageRepository,
        TagRepository $tagRepository,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->forms = $formRepository;
        $this->posts = $postRepository;
        $this->media = $mediaRepository;
        $this->pages = $pageRepository;
        $this->tags = $tagRepository;
    }

    /**
     * Index content items
     *
     * @param Request     $request
     * @return mixed
     */
    public function indexAction(Request $request)
    {

        $posts = $this->posts->findAll();
        $pages = $this->pages->findAll();
        $forms = $this->forms->findAll();
        $media = $this->media->getLibraryMedia();

        $data['content'] = array_merge($posts, $pages, $media, $forms);
        $data['request'] = $request;

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/content-list.html.twig',
            $data
        );
    }

    /**
     * Get single item
     *
     * @param Request     $request
     * @return mixed
     */
    public function getAction(Request $request)
    {
        $type = $request->get('type');
        $id = $request->get('id');

        if (in_array($type, $array = array('post', 'image', 'video', 'page', 'form'))) {
            $item = null;
            if ($type === 'post') {
                $item = $this->posts->findOneBy(array('id' => $id));
            } elseif ($type === 'image' or $type === 'video') {
                $item = $this->media->findOneBy(array('id' => $id));
            } elseif ($type === 'page') {
                $item = $this->pages->findOneBy(array('id' => $id));
            } elseif ($type === 'form') {
                $item = $this->forms->findOneBy(array('id' => $id));
            }

            $data = array(
                'item' => $item,
                'request' => $request
            );

            return $this->twig->render(
                'admin/'.$this->settings->getAdminTheme().'/widgets/content-detail.html.twig',
                $data
            );
        }

        return false;
    }

    public function helpAction()
    {
        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/help-content.html.twig');
    }
}
