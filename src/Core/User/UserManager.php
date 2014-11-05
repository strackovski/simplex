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

namespace nv\Simplex\Core\User;

use Imagine\Image\Point;
use nv\Simplex\Core\Mailer\SystemMailer;
use nv\Simplex\Core\Simplex;
use nv\Simplex\Model\Entity\User;

/**
 * User Manager
 *
 * @package nv\Simplex\Core\User
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class UserManager
{
    /** @var \nv\Simplex\Model\Entity\User $user */
    private $user;

    /** @var \nv\Simplex\Core\Simplex $app */
    private $app;

    /**
     * @param User    $user
     * @param Simplex $app
     */
    public function __construct(User $user, Simplex $app)
    {
        $this->user = $user;
        $this->app = $app;
    }

    /**
     * Reset password
     *
     * Reset user password by requiring account reactivation and therefor
     * the user can choose a new password upon activation. Activation link
     * is sent by email.
     */
    public function resetPassword()
    {
        $this->deactivateAccount();
        $notification = array(
            'title' => 'Account password change requested',
            'message' => 'You received this email because you requested to change your password. Please follow the link below to change your password.',
            'link' => array(
                'href' => $this->app['url_generator']->generate('help/reset', array('token' => $this->user->getResetToken())),
                'text' => 'Reset your password'
            )
        );

        $this->app['system.mailer']->sendNotificationEmail($this->user->getEmail(), $notification);
    }

    /**
     * Change email
     *
     * Change user email, generate password reset token, send email with the
     * reset link to the new email
     *
     * @param $newEmail
     */
    public function changeEmail($newEmail)
    {
        $this->user->setEmail($newEmail);
        $this->deactivateAccount();
        $notification = array(
            'title' => 'Activate your account',
            'message' => 'Reactivation is required because you changed your email. Please follow the link below to confirm your new email and activate your account.',
            'link' => array(
                'href' => $this->app['url_generator']->generate('help/reset', array('token' => $this->user->getResetToken())),
                'text' => 'Reset your password'
            )
        );

        $this->app['system.mailer']->sendNotificationEmail($this->user->getEmail(), $notification);
    }

    /**
     * Send account activation email to user's email address
     */
    public function sendActivationNotification()
    {
        if ($this->user->getIsActive()) {
            $this->deactivateAccount();
        }

        $notification = array(
            'title' => 'Activate your account',
            'message' => 'Welcome. Activate your account.',
            'link' => array(
                'href' => $this->app['url_generator']->generate('help/reset', array('token' => $this->user->getResetToken())),
                'text' => 'Reset your password'
            )
        );

        $this->app['system.mailer']->sendNotificationEmail($this->user->getEmail(), $notification);
    }

    /**
     * Deactivate account
     */
    public function deactivateAccount()
    {
        $this->user->setIsActive(false);
        $this->setResetToken();
    }

    /**
     * Activate account
     */
    public function activateAccount()
    {
        $this->invalidateResetToken();
        $this->user->setIsActive(true);
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
     */
    private function setResetToken()
    {
        if (!$this->validateResetToken()) {
            $token = $this->generateResetToken();

            while ($this->app['repository.user']->findOneBy(array('resetToken' => $token))) {
                $token = $this->generateResetToken();
            }

            $this->user->setResetTokenExpirationDate((new \DateTime())->add(new \DateInterval('P1D')));
            $this->user->setResetToken($token);
        }
    }

    /**
     * Validate reset token
     *
     * @return bool
     */
    public function validateResetToken()
    {
        if (is_null($this->user->getResetToken())) {
            return false;
        }

        if ($this->user->getResetTokenExpirationDate() < new \DateTime('now')) {
            return false;
        }

        return true;
    }

    /**
     * Invalidate reset token
     */
    public function invalidateResetToken()
    {
        if (!is_null($this->user->getResetToken())) {
            $this->user->setResetToken(null);
            $this->user->setResetTokenExpirationDate(null);
        }
    }
}
