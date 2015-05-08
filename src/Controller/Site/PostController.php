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
