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

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ImageController
 *
 * Defines actions to perform on requests regarding Image objects.
 *
 * @package nv\Simplex\Controller
 * @author Vladimir Stračkovski <vlado@nv3.org>
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

        return $app['twig']->render(
            'admin/'.$app['settings']->getAdminTheme().'/views/login.html.twig',
            $data
        );
    }
}
