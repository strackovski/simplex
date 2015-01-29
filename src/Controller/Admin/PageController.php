<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 NV3, Vladimir Stračkovski <vlado@nv3.org>
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Controller\Admin;

use nv\Simplex\Controller\ActionControllerAbstract;
use Silex\Application;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Entity\Page;
use nv\Simplex\Form\PageType;
use nv\Simplex\Core\Page\PageManager;
use nv\Simplex\Model\Repository\PageRepository;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class PageController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller\Admin
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageController extends ActionControllerAbstract
{
    /** @var PageRepository */
    private $pages;

    /** @var PageManager */
    private $manager;

    /**
     * @param PageRepository $pageRepository
     * @param Settings $settings
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param SecurityContext $security
     * @param Session $session
     * @param UrlGenerator $url
     * @param PageManager $pageManager
     */
    public function __construct(
        PageRepository $pageRepository,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        PageManager $pageManager,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->pages = $pageRepository;
        $this->manager = $pageManager;
    }

    /**
     * Index pages
     *
     * @return mixed
     */
    public function indexAction()
    {
        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/pages.html.twig',
            array('pages' => $this->pages->findAll())
        );
    }

   /**
     * View single page
     *
     * @return mixed
     */
    public function viewAction()
    {
        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/pages.html.twig');
    }

    /**
     * Get single page
     *
     * @param Request     $request
     * @return mixed
     */
    public function getAction(Request $request)
    {
        $page = $this->pages->findOneBy(array('id' => $request->get('page')));
        $data = array(
            'page' => $page,
            'request' => $request
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/page-detail.html.twig', $data);
    }

    /**
     * Add page
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request)
    {
        $page = new Page($request->request->get('title'), $request->request->get('slug'));
        $form = $this->form->create(new PageType($this->pages, $this->settings), $page);
        $token = $this->security->getToken();

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                if (null !== $token) {
                    $page->setAuthor($token->getUser());
                }
                $this->manager->slug($page, $form->get('slug')->getData());
                $this->pages->save($page);

                $message = 'The page <strong>' . $page->getTitle() .
                    '</strong> has been saved. <a href="' . $page->getSlug() . '" target="_blank">See it!</a>';

                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/pages');
                return new RedirectResponse($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new page',
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/page-form.html.twig', $data);
    }

    /**
     * Edit page
     *
     * @param Request     $request
     * @return mixed
     */
    public function editAction(Request $request)
    {
        $page = $this->pages->findOneBy(array('id' => $request->get('page')));
        $form = $this->form->create(new PageType($this->pages, $this->settings), $page);
        $token = $this->security->getToken();


        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                if (null !== $token) {
                    $page->setAuthor($token->getUser());
                }
                $this->manager->slug($page, $form->get('slug')->getData());
                $this->pages->save($page);
                $message = 'Changes to page <strong>' . $page->getTitle() . '</strong> have been saved.';
                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/pages');

                return new RedirectResponse($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'page'  => $page,
            'title' => 'Edit page',
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/page-form.html.twig', $data);
    }

    /**
     * Delete page
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function deleteAction(Request $request)
    {
        $page = $this->pages->findOneBy(array('id' => $request->get('page')));
        if ($page instanceof Page) {
            $this->pages->delete($page);
        }
        $redirect = $this->url->generate('admin/pages');

        return new RedirectResponse($redirect);
    }

    public function helpAction()
    {
        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/help-pages.html.twig');
    }
}
