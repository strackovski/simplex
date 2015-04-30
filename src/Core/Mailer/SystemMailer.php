<?php

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
                ->setTo(array($address))
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
