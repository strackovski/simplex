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

namespace nv\Simplex\Core\User;

use Imagine\Image\Point;
use nv\Simplex\Core\Mailer\SystemMailer;
use nv\Simplex\Model\Entity\User;
use nv\Simplex\Model\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * User Manager
 *
 * @package nv\Simplex\Core\User
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class UserManager
{
    /** @var SystemMailer */
    private $mailer;

    /** @var UrlGenerator */
    private $url;

    /** @var UserRepository */
    private $users;

    /** @var MessageDigestPasswordEncoder */
    private $encoder;

    /**
     * @param UserRepository $users
     * @param UrlGenerator $url
     * @param SystemMailer $mailer
     * @param MessageDigestPasswordEncoder $encoder
     */
    public function __construct(
        UserRepository $users,
        UrlGenerator $url,
        SystemMailer $mailer,
        MessageDigestPasswordEncoder $encoder
    ) {
        $this->users = $users;
        $this->mailer = $mailer;
        $this->url = $url;
        $this->encoder = $encoder;
    }

    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param User $user
     * @param $password
     * @return bool
     */
    public function verifyCredentials(User $user, $password)
    {
        if (!$this->encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            return false;
        }

        return true;
    }

    /**
     * Reset password
     *
     * Reset user password by requiring account reactivation and therefor
     * the user can choose a new password upon activation. Activation link
     * is sent by email.
     * @param User $user
     */
    public function resetPassword(User $user)
    {
        $this->deactivateAccount($user);
        $notification = array(
            'title' => 'Account password change requested',
            'message' => 'You received this email because you requested to change '.
                         'your password. Please follow the link below to change your password.',
            'link' => array(
                'href' => $this->url->generate(
                    'help/reset',
                    array('token' => $user->getResetToken())
                ),
                'text' => 'Reset your password'
            )
        );

        $this->mailer->sendNotificationEmail($user->getEmail(), $notification);
    }

    /**
     * Change email
     *
     * Change user email, generate password reset token, send email with the
     * reset link to the new email
     *
     * @param User $user
     * @param $newEmail
     */
    public function changeEmail(User $user, $newEmail)
    {
        $user->setEmail($newEmail);
        $this->deactivateAccount($user);
        $notification = array(
            'title' => 'Activate your account',
            'message' => 'Reactivation is required because you changed your email. ' .
                'Please follow the link below to confirm your new email and activate your account.',
            'link' => array(
                'href' => $this->url->generate(
                    'help/reset',
                    array('token' => $user->getResetToken())
                ),
                'text' => 'Reset your password'
            )
        );

        $this->mailer->sendNotificationEmail($user->getEmail(), $notification);
    }

    /**
     * Send account activation email to user's email address
     * @param User $user
     */
    public function sendActivationNotification(User $user)
    {
        if ($user->getIsActive()) {
            $this->deactivateAccount($user);
        }

        $notification = array(
            'title' => 'Activate your account',
            'message' => 'Welcome. Activate your account.',
            'link' => array(
                'href' => $this->url->generate(
                    'help/reset',
                    array('token' => $user->getResetToken())
                ),
                'text' => 'Reset your password'
            )
        );

        $this->mailer->sendNotificationEmail($user->getEmail(), $notification);
    }

    /**
     * Deactivate account
     * @param User $user
     */
    public function deactivateAccount(User $user)
    {
        $user->setIsActive(false);
        $this->setResetToken($user);
    }

    /**
     * Activate account
     * @param User $user
     */
    public function activateAccount(User $user)
    {
        $this->invalidateResetToken($user);
        $user->setIsActive(true);
    }

    /**
     * Generate reset token
     *
     * @return string
     */
    private function generateResetToken()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * Set reset token to allow password reset
     * @param User $user
     */
    private function setResetToken(User $user)
    {
        if (!$this->validateResetToken($user)) {
            $token = $this->generateResetToken();

            while ($this->users->findOneBy(array('resetToken' => $token))) {
                $token = $this->generateResetToken();
            }

            $user->setResetTokenExpirationDate((new \DateTime())->add(new \DateInterval('P1D')));
            $user->setResetToken($token);
        }
    }

    /**
     * Validate reset token
     *
     * @param User $user
     * @return bool
     */
    public function validateResetToken(User $user)
    {
        if (is_null($user->getResetToken())) {
            return false;
        }

        if ($user->getResetTokenExpirationDate() < new \DateTime('now')) {
            return false;
        }

        return true;
    }

    /**
     * Invalidate reset token
     * @param User $user
     */
    public function invalidateResetToken(User $user)
    {
        if (!is_null($user->getResetToken())) {
            $user->setResetToken(null);
            $user->setResetTokenExpirationDate(null);
        }
    }
}
