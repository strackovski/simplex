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

use Doctrine\DBAL\Query\QueryException;
use nv\Simplex\Model\Entity\Page;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PageController
 *
 * @package nv\Simplex\Controller\Site
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageController
{
    /**
     * Controller home
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return mixed
     */
    public function indexAction(Request $request, Application $app)
    {
        if (!$app['settings']->getLive()) {
            die('offline');
        }

        $settings = $app['repository.settings']->getPublicSettings();
        $page = $app['repository.page']->findOneBy(array('slug' => 'home'));
        $content = array();

        if ($page instanceof Page and $page->getQueries()) {
            foreach ($page->getQueries() as $query) {
                try{
                    if (!is_null($query->getOutputVariable())) {

                        if ($query->getLimitMax() == 1) {
                            try {
                                $content[$query->getOutputVariable()] =
                                    $query->getManager()->buildQuery2($app['orm.em'])->getOneOrNullResult();
                            } catch (\Exception $e) {
                                $content[$query->getOutputVariable()] = null;
                            }
                        } else {
                            $content[$query->getOutputVariable()] = $query->getManager()->buildQuery2($app['orm.em'])->getResult();
                        }

                    } else {
                        if ($query->getLimitMax() == 1) {
                            try {
                                $content[$query->getOutputVariable()] =
                                    $query->getManager()->buildQuery2($app['orm.em'])->getOneOrNullResult();
                            } catch (\Exception $e) {
                                $content[$query->getOutputVariable()] = null;
                            }
                        } else {
                            $content[] = $query->getManager()->buildQuery2($app['orm.em'])->getResult();
                        }
                    }
                } catch (QueryException $e) {
                    $app['monolog']->addError(
                        'Failed building query in Site\\PageController:viewAction: ' . $e->getMessage()
                    );
                }
            }

            return $app['twig']->render(
                'site/'.$app['settings']->getPublicTheme().'/views/'.$page->getView().'.html.twig',
                array(
                    'content' => $content,
                    'page' => $page,
                    'settings' => $settings,
                    'menu' => $app['repository.page']->getMenuPages()
                )
            );
        }

        return $app->abort(404, 'Page not found.');
    }

    /**
     * View single page
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function viewAction(Request $request, Application $app)
    {
        if (!$app['settings']->getLive()) {
            die('offline');
        }

        /** @var \nv\Simplex\Model\Entity\Page $page */
        $page = $app['repository.page']->findOneBy(array('slug' => $request->get('slug')));
        $content = array();

        if ($page instanceof Page and $page->getQueries()) {
            foreach ($page->getQueries() as $query) {
                try{
                    if (!is_null($query->getOutputVariable())) {

                        if ($query->getLimitMax() == 1) {
                            try {
                                $content[$query->getOutputVariable()] =
                                    $query->getManager()->buildQuery2($app['orm.em'])->getOneOrNullResult();
                            } catch (\Exception $e) {
                                $content[$query->getOutputVariable()] = null;
                            }
                        } else {
                            $content[$query->getOutputVariable()] = $query->getManager()->buildQuery2($app['orm.em'])->getResult();
                        }

                    } else {
                        if ($query->getLimitMax() == 1) {
                            try {
                                $content[$query->getOutputVariable()] =
                                    $query->getManager()->buildQuery2($app['orm.em'])->getOneOrNullResult();
                            } catch (\Exception $e) {
                                $content[$query->getOutputVariable()] = null;
                            }
                        } else {
                            $content[] = $query->getManager()->buildQuery2($app['orm.em'])->getResult();
                        }
                    }
                } catch (QueryException $e) {
                    $app['monolog']->addError(
                        'Failed building query in Site\\PageController:viewAction: ' . $e->getMessage()
                    );
                }
            }

            return $app['twig']->render(
                'site/'.$app['settings']->getPublicTheme().'/views/'.$page->getView().'.html.twig',
                array(
                    'content' => $content,
                    'page' => $page,
                    'settings' => $app['settings'],
                    'menu' => $app['repository.page']->getMenuPages()
                )
            );
        }

        return $app->abort(404, 'Page not found.');
    }

    /**
     * View single post
     *
     * @param Request     $request
     * @param Application $app
     * @return mixed
     */
    public function mediaViewAction(Request $request, Application $app)
    {
        if (!$app['settings']->getLive()) {
            die('offline');
        }

        $content = $app['repository.media']->findOneBy(array('id' => $request->get('id')));
        $menu = $app['repository.page']->getMenuPages();

        return $app['twig']->render(
            'site/'.$app['settings']->getPublicTheme().'/widgets/media.html.twig',
            array(
                'content' => $content,
                'menu' => $menu,
                'settings' => $app['settings']
            )
        );
    }
}
