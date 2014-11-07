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
 * Class PageController
 *
 * @package nv\Simplex\Controller\Site
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
        $data['pages'] = $app['repository.page']->findAll();
        $data['settings'] = $app['repository.settings']->getPublicSettings();

        return $app['twig']->render('site/'.$data['settings']->getPublicTheme().'/views/index.html.twig', $data);

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

        /** @var  \nv\Simplex\Model\Entity\Settings $settings */
        $settings = $app['settings'];

        $content = array();

        if ($page->getQueries()) {
            foreach ($page->getQueries() as $query) {
                $content = $query->getManager()->buildQuery($app['orm.em'])->getResult();
            }
        }

        return $app['twig']->render(
            'site/'.$settings->getPublicTheme().'/views/'.$page->getView().'.html.twig',
            array(
                'content' => $content,
                'page' => $page,
                'settings' => $settings,
                'menu' => $app['repository.page']->getMenuPages()
            )
        );
    }
}
