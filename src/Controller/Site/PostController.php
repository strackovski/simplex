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

namespace nv\Simplex\Controller\Site;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PostController
 *
 * @package nv\Simplex\Controller\Site
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PostController
{
    /**
     * View post list
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        if (!$app['settings']->getLive()) {
            die('offline');
        }

        $content = $app['repository.post']->getPublished();
        $menu = $app['repository.page']->getMenuPages();

        return $app['twig']->render(
            'site/'.$app['settings']->getPublicTheme().'/views/posts.html.twig',
            array(
                'content' => $content,
                'menu' => $menu,
                'settings' => $app['settings']
            )
        );
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
        if (!$app['settings']->getLive()) {
            die('offline');
        }

        $content = $app['repository.post']->findOneBy(array('slug' => $request->get('slug')));
        $menu = $app['repository.page']->getMenuPages();

        return $app['twig']->render(
            'site/'.$app['settings']->getPublicTheme().'/views/post.html.twig',
            array(
                'content' => $content,
                'menu' => $menu,
                'settings' => $app['settings']
            )
        );
    }
}
