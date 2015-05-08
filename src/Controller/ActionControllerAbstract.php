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
