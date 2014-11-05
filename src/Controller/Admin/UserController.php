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
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Core\User\UserManager;
use nv\Simplex\Form\UserCredentialsType;
use nv\Simplex\Form\UserProfileType;
use nv\Simplex\Form\UserType;
use nv\Simplex\Model\Entity\Image;
use nv\Simplex\Model\Entity\User;

/**
 * Class PostController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller\Admin
 */
class UserController
{
    /**
     * Index users
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        if ($app['security']->isGranted('ROLE_ADMIN')) {
            $data['users'] = $app['repository.user']->findAll();
            $data['request'] = $request;

            return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/users.html.twig', $data);
        }

        return false;
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return bool|JsonResponse
     */
    public function usersListAction(Request $request, Application $app)
    {
        if ($app['security']->isGranted('ROLE_ADMIN')) {
            $users = $app['repository.user']->getUsers(true);

            return new JsonResponse($users, 200);
        }

        return false;
    }

    /**
     * View single user
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function profileViewAction(Request $request, Application $app)
    {
        $token = $app['security']->getToken();
        if (null !== $token) {
            $user = $token->getUser();

            $data = array(
                'user' => $user,
                'request' => $request
            );

            return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/user-profile.html.twig', $data);
        }

        return 0;
    }

    /**
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function credentialsAction(Request $request, Application $app)
    {
        $token = $app['security']->getToken();

        if (null !== $token) {
            /** @var $user User */
            $user = $token->getUser();
            $currentEmail = $user->getEmail();
            $currentPwd = $user->getPassword();

            /** @var $form Form */
            $form = $app['form.factory']->create(new UserCredentialsType(), $user);

            if ($request->isMethod('POST')) {
                $files = $request->files;
                $form->bind($request);
                if ($form->isValid()) {
                    $um = new UserManager($user, $app);
                    $email = $form->get('email')->getData();
                    $password = $form->get('password')->getData();

                    // @todo Remove and test
                    foreach ($files as $uploadedFile) {
                        if ($files instanceof UploadedFile) {
                            $user->setAvatarFile($uploadedFile['avatarFile']);
                        }
                    }

                    $emailChanged = false;

                    if (!$app['security.encoder.digest']->isPasswordValid($currentPwd, $password, $user->getSalt())) {
                        $redirect = $app['url_generator']->generate('admin_logout');
                        return $app->redirect($redirect);
                    }

                    if ($currentEmail !== $user->getEmail()) {
                        $emailChanged = 1;
                    }

                    if ($emailChanged === 1) {
                        $um->changeEmail($email);
                    }

                    $app['repository.user']->save($user);
                    $message = 'The account for ' . $user->getUsername() . ' has been changed and must be reactivated.';
                    $app['session']->getFlashBag()->add('success', $message);

                    if ($emailChanged) {
                        $redirect = $app['url_generator']->generate('admin_logout');
                    } else {
                        $redirect = $app['url_generator']->generate('admin/users');
                    }

                    return $app->redirect($redirect);
                }
            }

            $data = array(
                'form' => $form->createView(),
                'user' => $user,
                'title' => 'Edit credentials',
                'request' => $request
            );
            return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/user-credentials.html.twig', $data);
        }

        return 0;
    }

    /**
     * Enable anonymous users to request a password change link if a valid
     * username/email is provided. The password link is sent to user's email.
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     * @throws \Exception When username/email not found
     */
    public function forgotPasswordAction(Request $request, Application $app)
    {
        $form = $app['form.factory']->createBuilder('form', array())
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

            if (is_null($user = $app['repository.user']->userExists($form->get('email')->getData()))) {
                throw new \Exception("The user with email {$form->get('email')->getData()} does not exist.");
            }

            $um = new UserManager($user, $app);
            $um->resetPassword();
            $app['repository.user']->save($user);

            return $app['twig']->render(
                'admin/'.$app['settings']->getAdminTheme().'/views/notification-public.html.twig',
                array('message' => 'WE SENT YOU YOUR PASSWORD IN PLAIN TEXT! CHECK YOUR MAIL!')
            );
        }

        return $app['twig']->render(
            'admin/'.$app['settings']->getAdminTheme().'/views/form-public.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Enables anonymous users to change account password by providing a valid
     * reset token.
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception If token is invalid, unmatched or missing
     */
    public function resetPasswordAction(Request $request, Application $app)
    {
        if (is_null($receivedToken = $request->query->get('token'))) {
            throw new \Exception('Missing parameter(s)');
        }

        if (is_null($user = $app['repository.user']->findOneBy(array('resetToken' => $receivedToken)))) {
            throw new \Exception('User not found');
        }

        if (!$app['repository.user']->validateResetToken($user)) {
            throw new \Exception('Old token eeeh');
        }

        /** @var $form Form */
        $form = $app['form.factory']->createBuilder('form', array(), array('method' => 'post'))
            ->add('password', 'password')
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $pass = $form->get('password')->getData();
            $user->setEncodedPassword($app, $pass);

            $um = new UserManager($user, $app);
            $um->activateAccount();
            $app['repository.user']->save($user);

            $redirect = $app['url_generator']->generate('login');
            return $app->redirect($redirect);
        }

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/form-public2.html.twig', array('form' => $form->createView()));
    }


    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return int|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editProfileAction(Request $request, Application $app)
    {
        $token = $app['security']->getToken();
        if (null !== $token) {
            /** @var $user User */
            $user = $token->getUser();
            /** @var $form Form */
            $form = $app['form.factory']->create(new UserProfileType($app['orm.em']), $user);

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
                                $avatar->setName($uploadedFile->getClientOriginalName());
                                $avatar->setInLibrary(false);
                                $avatar->setMediaCategory('avatar');
                                $user->setAvatar($avatar);
                            }
                        }
                    }
                    $app['repository.user']->save($user);

                    if(isset($avatar) and $avatar instanceof Image){
                        try{
                            $avatar->getManager()->thumbnail($app['imagine'], $app['settings']->getImageResizeDimensions());
                            $avatar->getManager()->autoCrop($app['imagine']);
                        } catch (\Exception $e) {
                            $app['repository.media']->delete($avatar);
                            $app['monolog']->addError(
                                get_class($this) . " caught exception \"{$e->getMessage()}\" from {$e->getFile()}:{$e->getLine()}"
                            );
                        }
                    }
                    $redirect = $app['url_generator']->generate('admin/user/profile');

                    return $app->redirect($redirect);
                }
            }

            $data = array(
                'form' => $form->createView(),
                'title' => 'Add new user',
                'request' => $request,
                'user' => $user
            );

            return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/user-profile.html.twig', $data);
        }

        return 0;
    }

    /**
     * Enables account activation by providing a valid reset token
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception If token is invalid, unmatched or missing
     */
    public function activateAccountAction(Request $request, Application $app)
    {
        if (is_null($receivedToken = $request->query->get('token'))) {
            throw new \Exception('Missing parameter(s)');
        }

        if (is_null($user = $app['repository.user']->findOneBy(array('resetToken' => $receivedToken)))) {
            throw new \Exception('Unmatched parameter(s)');
        }

        if (!$app['repository.user']->validateResetToken($user)) {
            throw new \Exception('Invalid parameter(s)');
        }

        /** @var $form Form */
        $form = $app['form.factory']->createBuilder('form', array(), array('method' => 'post'))
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
            $user->setEncodedPassword($app, $pass);

            $um = new UserManager($user, $app);
            $um->activateAccount();
            $app['repository.user']->save($user);
            $redirect = $app['url_generator']->generate('login');

            return $app->redirect($redirect);
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Activate your account',
            'request' => $request
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/form-public.html.twig', $data);
    }

    /**
     * Add new user
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function addAction(Request $request, Application $app)
    {
        /** @var $user User */
        $user = new User();
        /** @var $form Form */
        $form = $app['form.factory']->create(new UserType($app['orm.em']), $user);

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
                $app['repository.user']->save($user);

                if (isset($avatar) and $avatar instanceof Image){
                    $avatar->getManager()->thumbnail($app['imagine'], $app['settings']->getImageResizeDimensions('crop'));
                    $avatar->getManager()->autoCrop($app['imagine']);
                }

                $app['repository.user']->setResetToken($user);
                $message  = 'The account for ' . $user->getUsername() . ' has been created. ';
                $message .= 'It must be activated prior to login.';
                $app['session']->getFlashBag()->add('success', $message);
                $um = new UserManager($user, $app);
                $um->sendActivationNotification();
                $redirect = $app['url_generator']->generate('admin/users');

                return $app->redirect($redirect);
            }
        }

        $data = array(
            'form' => $form->createView(),
            'title' => 'Add new user'
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/user-form.html.twig', $data);
    }

    /**
     * Edit user
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function editAction(Request $request, Application $app)
    {
        /** @var $user User */
        $user = $app['repository.user']->findOneBy(array('id' => $request->get('user')));
        /** @var $form Form */
        $form = $app['form.factory']->create(new UserType($app['orm.em']), $user);
        $userEmail = $user->getEmail();

        if ($request->isMethod('POST')) {
            $files = $request->files;
            $form->bind($request);
            if ($form->isValid()) {
                $um = new UserManager($user, $app);
                $email = $form->get('email')->getData();
                $user->setRoles($form->get('roles')->getData());

                if ($email !== $userEmail) {
                    $um->changeEmail($email);
                }

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
                $app['repository.user']->save($user);

                if (isset($avatar) and $avatar instanceof Image) {
                    $avatar->getManager()->thumbnail($app['imagine'], $app['settings']->getImageResizeDimensions());
                    $avatar->getManager()->autoCrop($app['imagine']);
                }
                $message = 'The account for ' . $user->getUsername() . ' has been changed.';
                $app['session']->getFlashBag()->add('success', $message);
                $redirect = $app['url_generator']->generate('admin/users');

                return $app->redirect($redirect);
            }
        }
        $data = array(
            'form' => $form->createView(),
            'user' => $user,
            'request' => $request,
            'title' => 'Edit user',
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/user-form.html.twig', $data);
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
        $user = $app['repository.user']->findOneBy(array('id' => $request->get('user')));
        if ($user instanceof User) {
            $app['orm.em']->remove($user);
            $app['orm.em']->flush();
        }
        $redirect = $app['url_generator']->generate('admin/users');

        return $app->redirect($redirect);
    }
}