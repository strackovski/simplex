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

use nv\Simplex\Model\Entity\Page;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PageController
 *
 * @package nv\Simplex\Controller\Site
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageController
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
        $pages = $app['repository.page']->findAll();
        $settings = $app['repository.settings']->getPublicSettings();

        return $app['twig']->render(
            'site/'.$settings->getPublicTheme().'/views/index.html.twig',
            array(
                'pages' => $pages,
                'settings' => $settings
            )
        );
    }

    /**
     * View single page
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function viewAction(Request $request, Application $app)
    {
        /** @var \nv\Simplex\Model\Entity\Page $page */
        $page = $app['repository.page']->findOneBy(array('slug' => $request->get('slug')));
        $content = array();

        if ($page instanceof Page and $page->getQueries()) {
            foreach ($page->getQueries() as $query) {
                $content = $query->getManager()->buildQuery($app['orm.em'])->getResult();
            }

            return $app['twig']->render(
                'site/'.$app['settings']->getPublicTheme().'/views/'.$page->getView().'.html.twig',
                array(
                    'content' => $content,
                    'page' => $page,
                    'settings' => $app['settings'],
                    'menu' => $app['repository.page']->getMenuPages()
                )
            );
        }

        return $app->abort(404, 'Page not found.');
    }
}
