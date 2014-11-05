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
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Form\PageType;
use nv\Simplex\Model\Entity\Page;

/**
 * Class PageController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller\Admin
 */
class PageController
{
    /**
     * Index pages
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        $data['pages'] = $app['repository.page']->findAll();
        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/pages.html.twig', $data);
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
        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/pages.html.twig');
    }

    /**
     * Add page
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, Application $app)
    {
        $page = new Page($request->request->get('title'), $request->request->get('slug'));
        $page->registerObserver($pm = new \nv\Simplex\Core\Page\PageManager($page, $app));
        $form = $app['form.factory']->create(new PageType($app['orm.em']), $page);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $pm->slug($form->get('slug')->getData());
                $app['repository.page']->save($page);
                $message = 'The page <strong>' . $page->getTitle() . '</strong> has been saved. <a href="' . $page->getSlug() . '" target="_blank">See it!</a>';
                $app['session']->getFlashBag()->add('success', $message);

                $redirect = $app['url_generator']->generate('admin/pages');
                return $app->redirect($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new page',
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/page-form.html.twig', $data);
    }

    /**
     * Edit page
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function editAction(Request $request, Application $app)
    {
        $page = $app['repository.page']->findOneBy(array('id' => $request->get('page')));
        $form = $app['form.factory']->create(new PageType($app['orm.em']), $page);

        $pm = new \nv\Simplex\Core\Page\PageManager($page, $app);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $pm->slug($form->get('slug')->getData());
                $app['repository.page']->save($page);
                $message = 'Changes to page <strong>' . $page->getTitle() . '</strong> have been saved. <a href="' . $page->getSlug() . '" target="_blank">See it!</a>';
                $app['session']->getFlashBag()->add('success', $message);
                $redirect = $app['url_generator']->generate('admin/pages');

                return $app->redirect($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'page'  => $page,
            'title' => 'Edit page',
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/page-form.html.twig', $data);
    }

    /**
     * Delete page
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function deleteAction(Request $request, Application $app)
    {
        $post = $app['repository.page']->findOneBy(array('id' => $request->get('page')));
        $app['orm.em']->remove($post);
        $app['orm.em']->flush();
        $redirect = $app['url_generator']->generate('admin/pages');

        return $app->redirect($redirect);
    }
}
