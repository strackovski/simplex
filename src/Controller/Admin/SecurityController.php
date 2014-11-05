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
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ImageController
 *
 * Defines actions to perform on requests regarding Image objects.
 *
 * @package nv\Simplex\Controller
 */
class SecurityController
{
    /**
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function loginAction(Request $request, Application $app)
    {
        $data = array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username')
        );

        return $app['twig']->render('admin/'.$app['settings']->getAdminTheme().'/views/login.html.twig', $data);
    }
}
