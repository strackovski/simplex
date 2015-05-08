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

use Imagine\Image\ImagineInterface;
use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Core\Mailer\SystemMailer;
use nv\Simplex\Model\Entity\Settings;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Core\User\UserManager;
use nv\Simplex\Form\UserCredentialsType;
use nv\Simplex\Form\UserProfileType;
use nv\Simplex\Form\UserType;
use nv\Simplex\Model\Entity\Image;
use nv\Simplex\Model\Entity\User;
use nv\Simplex\Model\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class User Controller
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller\Admin
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class UserController extends ActionControllerAbstract
{
    /** @var UserRepository */
    private $users;

    /** @var SystemMailer */
    private $mailer;

    /** @var ImagineInterface */
    private $imagine;

    /** @var UserManager */
    private $manager;

    /**
     * @param UserRepository $users
     * @param Settings $settings
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param SecurityContext $security
     * @param Session $session
     * @param UrlGenerator $url
     * @param SystemMailer $mailer
     * @param ImagineInterface $imagine
     * @param UserManager $manager
     * @param Logger $logger
     */
    public function __construct(
        UserRepository $users,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        SystemMailer $mailer,
        ImagineInterface $imagine,
        UserManager $manager,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->users = $users;
        $this->mailer = $mailer;
        $this->imagine = $imagine;
        $this->manager = $manager;
    }

    /**
     * Index users
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->twig->render(
                'admin/'.$this->settings->getAdminTheme().'/views/users.html.twig',
                array(
                    'users' => $this->users->findAll(),
                    'request' => $request
                )
            );
        }

        return false;
    }

    /**
     * @param Request $request
     * @return bool|JsonResponse
     */
    public function usersListAction(Request $request)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(
                $this->users->getUsers(true),
                200
            );
        }

        return false;
    }

    /**
     * Get single user
     *
     * @param Request     $request
     * @return mixed
     */
    public function getAction(Request $request)
    {
        $view = $request->get('view');
        $user = $this->users->findOneBy(array('id' => $request->get('user')));

        return $this->twig->render(
            $view ?
            'admin/'.$this->settings->getAdminTheme().'/widgets/user-card.html.twig' :
            'admin/'.$this->settings->getAdminTheme().'/widgets/user-detail.html.twig',
            array(
                'user' => $user,
                'request' => $request
            )
        );
    }


    /**
     * View single user
     *
     * @param Request     $request
     * @return mixed
     */
    public function profileViewAction(Request $request)
    {
        $token = $this->security->getToken();
        if (null !== $token) {
            $user = $token->getUser();

            return $this->twig->render(
                'admin/'.$this->settings->getAdminTheme().'/views/user-profile.html.twig',
                array(
                    'user' => $user,
                    'request' => $request
                )
            );
        }

        return 0;
    }

    /**
     *
     * @param Request     $request
     * @return mixed
     */
    public function credentialsAction(Request $request)
    {
        $token = $this->security->getToken();

        if (null !== $token) {
            /** @var $user User */
            $user = $token->getUser();
            $currentEmail = $user->getEmail();
            $currentPwd = $user->getPassword();

            /** @var $form Form */
            $form = $this->form->create(new UserCredentialsType(), $user);

            if ($request->isMethod('POST')) {
                $files = $request->files;
                $form->bind($request);
                if ($form->isValid()) {
                    // $um = new UserManager($user, $this->users, $this->url, $this->mailer);
                    $email = $form->get('email')->getData();
                    $password = $form->get('password')->getData();
                    $emailChanged = false;

                    if (!$this->manager->verifyCredentials($user, $password)) {
                        return new RedirectResponse($this->url->generate('admin_logout'));
                    }


                    if ($currentEmail !== $user->getEmail()) {
                        $emailChanged = 1;
                    }

                    $this->users->save($user);
                    $message = 'The account for ' . $user->getUsername() . ' has been changed and must be reactivated.';
                    $this->session->getFlashBag()->add('success', $message);

                    if ($emailChanged) {
                        $redirect = $this->url->generate('admin_logout');
                    } else {
                        $redirect = $this->url->generate('admin/users');
                    }

                    return new RedirectResponse($redirect);
                }
            }

            $data = array(
                'form' => $form->createView(),
                'user' => $user,
                'title' => 'Edit credentials',
                'request' => $request
            );
            return $this->twig->render(
                'admin/'.$this->settings->getAdminTheme().'/views/user-credentials.html.twig',
                $data
            );
        }

        return 0;
    }

    /**
     * Enable anonymous users to request a password change link if a valid
     * username/email is provided. The password link is sent to user's email.
     *
     * @param Request     $request
     *
     * @return mixed
     * @throws \Exception When username/email not found
     */
    public function forgotPasswordAction(Request $request)
    {
        $form = $this->form->createBuilder('form', array())
            ->add('email')
            ->add('save', 'submit', array(
                'label' =>  'Send'
            ))
            ->add('cancel', 'button', array(
                'attr' => array(
                    'class' => 'btn-cmd cmd-cancel btn-cancel'
                )
            ))->getForm();

        /** @var $form Form */
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            /** @var $user User */
            if (is_null($user = $this->users->userExists($form->get('email')->getData()))) {
                throw new \Exception("The user with email {$form->get('email')->getData()} does not exist.");
            }

            $this->manager->resetPassword($user);
            $this->users->save($user);

            return $this->twig->render(
                'admin/'.$this->settings->getAdminTheme().'/views/notification-public.html.twig',
                array('message' => 'Password reset instructions sent to your email.')
            );
        }

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/form-public.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Enables anonymous users to change account password by providing a valid
     * reset token.
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception If token is invalid, unmatched or missing
     */
    public function resetPasswordAction(Request $request)
    {
        if (is_null($receivedToken = $request->query->get('token'))) {
            throw new \Exception('Missing parameter(s)');
        }

        /** @var $user User */
        if (is_null($user = $this->users->findOneBy(array('resetToken' => $receivedToken)))) {
            throw new \Exception('User not found');
        }

        if (!$this->users->validateResetToken($user)) {
            throw new \Exception('Old token eeeh');
        }

        /** @var $form Form */
        $form = $this->form->createBuilder('form', array(), array('method' => 'post'))
            ->add('password', 'password')
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $pass = $form->get('password')->getData();
            $user->setEncodedPassword($this->manager->getEncoder(), $pass);
            $this->manager->activateAccount($user);
            $this->users->save($user);

            return new RedirectResponse($this->url->generate('login'));
        }

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/form-public2.html.twig',
            array('form' => $form->createView())
        );
    }


    /**
     * @param Request     $request
     *
     * @return int|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editProfileAction(Request $request)
    {
        $token = $this->security->getToken();
        if (null !== $token) {
            /** @var $user User */
            $user = $token->getUser();
            /** @var $form Form */
            $form = $this->form->create(new UserProfileType(), $user);

            if ($request->isMethod('POST')) {
                $files = $request->files;
                $form->bind($request);
                if ($form->isValid()) {
                    $user->setFirstName($form->get('first_name')->getData());
                    $user->setLastName($form->get('last_name')->getData());
                    $user->setDescription($form->get('description')->getData());
                    $user->setUpdatedAt(new \DateTime('now'));

                    foreach ($files as $uploadedFile) {
                        if (array_key_exists('avatarFile', $uploadedFile)) {
                            if ($uploadedFile['avatarFile'] instanceof UploadedFile) {
                                $avatar = new Image();
                                $avatar->setFile($uploadedFile['avatarFile']);
                                $avatar->setName($uploadedFile['avatarFile']->getClientOriginalName());
                                $avatar->setInLibrary(false);
                                $avatar->setMediaCategory('avatar');
                                $user->setAvatar($avatar);
                            }
                        }
                    }
                    $this->users->save($user);
                    $redirect = $this->url->generate('admin/user/profile');

                    return new RedirectResponse($redirect);
                }
            }

            $data = array(
                'form' => $form->createView(),
                'title' => 'Add new user',
                'request' => $request,
                'user' => $user
            );

            return $this->twig->render(
                'admin/'.$this->settings->getAdminTheme().'/views/user-profile.html.twig',
                $data
            );
        }

        return 0;
    }

    /**
     * Enables account activation by providing a valid reset token
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception If token is invalid, unmatched or missing
     */
    public function activateAccountAction(Request $request)
    {
        if (is_null($receivedToken = $request->query->get('token'))) {
            throw new \Exception('Missing parameter(s)');
        }

        /** @var $user User */
        if (is_null($user = $this->users->findOneBy(array('resetToken' => $receivedToken)))) {
            throw new \Exception('Unmatched parameter(s)');
        }

        if (!$this->users->validateResetToken($user)) {
            throw new \Exception('Invalid parameter(s)');
        }

        /** @var $form Form */
        $form = $this->form->createBuilder('form', array(), array('method' => 'post'))
            ->add('password')
            ->add('save', 'submit')
            ->add('cancel', 'button', array(
                'attr' => array(
                    'class' => 'btn-cmd cmd-cancel btn-cancel'
                )
            ))->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $pass = $form->get('password')->getData();
            $user->setEncodedPassword($this->manager->getEncoder(), $pass);


            //$um = new UserManager($user, $this->users, $this->url, $this->mailer);
            // $um->activateAccount();

            $this->manager->activateAccount($user);
            $this->users->save($user);

            return new RedirectResponse($this->url->generate('login'));
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Activate your account',
            'request' => $request
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/form-public.html.twig', $data);
    }

    /**
     * Add new user
     *
     * @param Request     $request
     * @return mixed
     */
    public function addAction(Request $request)
    {
        /** @var $user User */
        $user = new User();
        /** @var $form Form */
        $form = $this->form->create(new UserType(), $user);

        if ($request->isMethod('POST')) {
            $files = $request->files;
            $form->bind($request);
            if ($form->isValid()) {
                $user->setFirstName($form->get('firstName')->getData());
                $user->setLastName($form->get('lastName')->getData());
                $user->setEmail($form->get('email')->getData());
                $user->setRoles($form->get('roles')->getData());
                $user->setSalt($user->getEmail());
                $user->setIsActive(0);
                $user->setCreatedAt(new \DateTime('now'));
                $user->setUpdatedAt($user->getCreatedAt());

                foreach ($files as $uploadedFile) {
                    if (array_key_exists('avatarFile', $uploadedFile)) {
                        if ($uploadedFile['avatarFile'] instanceof UploadedFile) {
                            $avatar = new Image();
                            $avatar->setFile($uploadedFile['avatarFile']);
                            $avatar->setName($uploadedFile['avatarFile']->getClientOriginalName());
                            $avatar->setInLibrary(false);
                            $avatar->setMediaCategory('avatar');
                            $user->setAvatar($avatar);
                        }
                    }
                }
                $this->users->save($user);

                $this->users->setResetToken($user);
                $message  = 'The account for ' . $user->getUsername() . ' has been created. ';
                $message .= 'It must be activated prior to login.';
                $this->session->getFlashBag()->add('success', $message);
                $this->manager->sendActivationNotification($user);
                $redirect = $this->url->generate('admin/users');

                return new RedirectResponse($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new user'
        );

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/user-form.html.twig', $data);
    }

    /**
     * Edit user
     *
     * @param Request     $request
     * @return mixed
     */
    public function editAction(Request $request)
    {
        /** @var $user User */
        $user = $this->users->findOneBy(array('id' => $request->get('user')));
        /** @var $form Form */
        $form = $this->form->create(new UserType(), $user);
        $userEmail = $user->getEmail();

        if ($request->isMethod('POST')) {
            $files = $request->files;
            $form->bind($request);
            if ($form->isValid()) {
                $user->setRoles($form->get('roles')->getData());

                foreach ($files as $uploadedFile) {
                    if (array_key_exists('avatarFile', $uploadedFile)) {
                        if ($uploadedFile['avatarFile'] instanceof UploadedFile) {
                            $avatar = new Image();
                            $avatar->setFile($uploadedFile['avatarFile']);
                            $avatar->setName($uploadedFile['avatarFile']->getClientOriginalName());
                            $avatar->setInLibrary(false);
                            $avatar->setMediaCategory('avatar');
                            $user->setAvatar($avatar);
                        }
                    }
                }
                $this->users->save($user);

                $message = 'The account for ' . $user->getUsername() . ' has been changed.';
                $this->session->getFlashBag()->add('success', $message);
                $redirect = $this->url->generate('admin/users');

                return new RedirectResponse($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'user' => $user,
            'request' => $request,
            'title' => 'Edit user',
        );

        return $this->twig->render(
            'admin/'.$this->settings->getAdminTheme().'/views/user-form.html.twig',
            $data
        );
    }

    /**
     * Delete user
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function deleteAction(Request $request, Application $app)
    {
        $user = $this->users->findOneBy(array('id' => $request->get('user')));
        if ($user instanceof User) {
            $app['orm.em']->remove($user);
            $app['orm.em']->flush();
        }
        $redirect = $this->url->generate('admin/users');

        return new RedirectResponse($redirect);
    }
}
