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

namespace nv\Simplex\Controller\Site;

use nv\Simplex\Model\Entity\Form;
use nv\Simplex\Model\Entity\FormResult;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FormController
 *
 * @package nv\Simplex\Controller\Site
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormController
{
    /**
     * Form processing
     *
     * @param Request $request
     * @param Application $app
     * @return int
     */
    public function formAction(Request $request, Application $app) {
        if (!$app['settings']->getLive()) {
            die('offline');
        }

        $formData = $request->request->all();
        $form = $app['repository.form']->findOneBy(array('id' => $request->get('formId')));

        if (!$form instanceof Form) {
            $app->abort('500', 'Error: invalid form.');
        }

        $result = new FormResult($formData, $request->getClientIp());
        $result->setForm($form);
        $form->addResult($result);
        $app['orm.em']->persist($result);
        $app['orm.em']->flush();

        $notification = array(
            'title' => 'New form result',
            'message' => 'Form #' . $form->getId() . ' was posted on your site, result #'
                . $result->getId() . ' was saved.'
        );

        $app['system.mailer']->sendNotificationEmail($app['settings']->getAdminEmail(), $notification);

        return 1;
    }
}
