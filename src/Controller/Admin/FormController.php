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
use nv\Simplex\Form\FormType;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\FormRepository;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class FormController
 *
 * Defines actions to perform on requests regarding Form objects.
 *
 * @package nv\Simplex\Controller\Admin
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormController extends ActionControllerAbstract
{
    /** @var FormRepository  */
    private $forms;

    public function __construct(
        FormRepository $formRepository,
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
    }

    /**
     * Index forms
     *
     * @param Request     $request
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        $data['forms'] = $this->forms->get();
        $data['request'] = $request;

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/forms.html.twig',
            $data
        );
    }

    /**
     * View single form
     *
     * @param Request     $request
     * @return mixed
     */
    public function getAction(Request $request)
    {
        $post = $this->forms->findOneBy(array('id' => $request->get('form')));
        $data = array(
            'user_form' => $post,
            'request' => $request
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/widgets/form-detail.html.twig', $data);
    }

    /**
     * Add new form
     *
     * @param Request     $request
     * @return mixed
     */
    public function addAction(Request $request)
    {
        $token = $this->security->getToken();
        $user_form = new \nv\Simplex\Model\Entity\Form();

        $form = $this->form->create(new FormType(), $user_form);
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->forms->save($user_form);
                $message = 'The form <strong>' . $user_form->getId() . '</strong> has been saved.';
                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/forms');

                return new RedirectResponse($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new post',
            'request' => $request
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/form-form.html.twig', $data);
    }


    /**
     * Edit form
     *
     * @param Request     $request
     * @return mixed
     */
    public function editAction(Request $request)
    {
        $token = $this->security->getToken();
        /** @var Form $form */
        $user_form = $this->forms->findOneBy(array('id' => $request->get('form')));
        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->form->create(new FormType(), $user_form);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $this->forms->save($user_form);
                $message = 'Changes saved to ' . $user_form->getTitle() . '.';
                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/forms');

                return new RedirectResponse($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'user_form' => $user_form,
            'request' => $request,
            'title' => 'Edit form',
        );

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/form-form.html.twig',
            $data
        );
    }

    /**
     * Delete form
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function deleteAction(Request $request)
    {
        $form = $this->forms->findOneBy(array('id' => $request->get('form')));
        if ($form instanceof \nv\Simplex\Model\Entity\Form) {
            $this->forms->delete($form);
        }

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/widgets/form-list.html.twig',
            array('forms' => $this->forms->get())
        );
    }
}
