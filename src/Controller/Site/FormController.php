<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
