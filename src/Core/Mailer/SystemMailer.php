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

namespace nv\Simplex\Core\Mailer;

/**
 * Class SystemMailer
 * @package nv\Simplex\Core\Mailer
 */
class SystemMailer
{
    /** @var \Swift_Mailer $mailer */
    private $mailer;

    /** @var \Twig_Environment $renderer */
    private $renderer;

    /** @var \nv\Simplex\Model\Entity\Settings $settings */
    private $settings;

    /**
     * @param $mailer
     * @param $renderer
     * @param $settings
     */
    public function __construct($mailer, $renderer, $settings)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
        $this->settings = $settings;
    }

    /**
     * Send a notification message to a specified address
     *
     * @param string $address           Email address to send to
     * @param array  $notificationData  Notification template data array
     */
    public function sendNotificationEmail($address, array $notificationData)
    {
        try {
            $emailMessage = \Swift_Message::newInstance()
                ->setSubject('[Simplex] Feedback')
                ->setFrom($this->settings->getAdminEmail())
                ->setTo($address)
                ->setBody(
                    $this->renderer->render(
                        'admin/'.$this->settings->getAdminTheme().'/email/notification.html.twig',
                        $notificationData
                    ),
                    'text/html'
                );
            $this->mailer->send($emailMessage);
        } catch (\Exception $e) {

        }
    }

    public function sendClientConfirmationEmail($address, array $confirmationData) {
        // @todo Implement client notification email
    }
}
