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

namespace nv\Simplex\Controller\Site;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PostController
 *
 * @package nv\Simplex\Controller\Site
 */
class PostController
{
    /**
     * Controller home
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        $data['posts'] = $app['repository.post']->findAll();
        $data['request'] = $request;

        return $app['twig']->render('public/posts.html.twig', $data);
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
        $data['post'] = $app['repository.post']->findOneBy(array('id' => $request->get('id')));

        return $app['twig']->render('public/post.html.twig', $data);
    }
}
