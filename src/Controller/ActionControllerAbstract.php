<?php

namespace nv\Simplex\Controller;

use nv\Simplex\Model\Entity\Settings;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ActionControllerAbstract
 *
 * @package nv\Simplex\Controller
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
abstract class ActionControllerAbstract
{
    /** @var Settings */
    protected $settings;

    /** @var \Twig_Environment  */
    protected $twig;

    /** @var FormFactoryInterface  */
    protected $form;

    /** @var SecurityContext  */
    protected $security;

    /** @var Session  */
    protected $session;

    /** @var UrlGenerator */
    protected $url;

    /** @var Logger $logger */
    protected $logger;

    /**
     * @param Settings $settings
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param SecurityContext $security
     * @param Session $session
     * @param UrlGenerator $url
     * @param Logger $logger
     */
    public function __construct(
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        Logger $logger
    ) {
        $this->settings = $settings;
        $this->twig = $twig;
        $this->form = $formFactory;
        $this->security = $security;
        $this->session = $session;
        $this->url = $url;
        $this->logger = $logger;
    }

    protected function render($view, array $data = null)
    {
        return $this->twig->render(
            'admin/' . $this->settings->getAdminTheme(). '/' . $view,
            $data
        );
    }
}
