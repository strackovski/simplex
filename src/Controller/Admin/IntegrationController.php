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

use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Core\Analytics\GoogleAnalyticsReader;
use nv\Simplex\Core\Service\GoogleApiAccount;
use nv\Simplex\Core\Service\GoogleServiceConnector;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\SettingsRepository;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;
use nv\Simplex\Core\Service\TwitterApiAccount;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class IntegrationController
 *
 * @todo Rename to AnalyticsController
 *
 * @package nv\Simplex\Controller
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class IntegrationController extends ActionControllerAbstract
{
    /** @var SettingsRepository */
    private $settingsRepository;

    public function __construct(
        SettingsRepository $settingsRepository,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->settingsRepository = $settingsRepository;
    }

    public function analyticsTest()
    {
        $g = $this->settings->getServiceConnection('google', 1);
        $analytics = new GoogleAnalyticsReader($g->connect());

        $ares = $analytics->getSortedResults(
            $analytics->getFirstprofileId(),
            array('ga:sessions', 'ga:pageviews'),
            array('ga:date')
        );

        return new JsonResponse($ares, 200);
    }

    public function getBrowserAnalytics()
    {
        $g = $this->settings->getServiceConnection('google', 1);
        $analytics = new GoogleAnalyticsReader($g->connect());

        $results = $analytics->getAnalytics()->data_ga->get(
            'ga:' . $analytics->getFirstprofileId(),
            '7daysAgo',
            'today',
            'ga:sessions',
            array('dimensions' => 'ga:browser')
        );

        $data = array();
        $dataResults = array();
        $data['datasets'] = array();

        foreach ($results['query']['metrics'] as $key => $metric) {
            // $data['datasets'][]['label'] = $metric;
            $data['datasets'][] = array(
                'label' => $metric,
                'fillColor' => "rgba(220,220,220,0.2)",
                'strokeColor' => "rgba(220,220,220,1)",
                'pointHighlightFill' => "#fff",
                'pointHighlightStroke' => "rgba(220,220,220,1)"
            );
            $dataResults[$key] = array();
        }

        foreach ($results['rows'] as $key => $row) {
            $data['labels'][] = $row[0];
            foreach ($dataResults as $index => $item) {
                $data['datasets'][$index]['data'][] = $row[$index+1];
            }
        }

        return new JsonResponse($data, 200);

    }

    public function getOsAnalytics()
    {
        $g = $this->settings->getServiceConnection('google', 1);
        $analytics = new GoogleAnalyticsReader($g->connect());

        $results = $analytics->getAnalytics()->data_ga->get(
            'ga:' . $analytics->getFirstprofileId(),
            '7daysAgo',
            'today',
            'ga:sessions',
            array('dimensions' => 'ga:operatingSystem,ga:operatingSystemVersion')
        );

        $data = array();
        $dataResults = array();
        $data['datasets'] = array();

        foreach ($results['query']['metrics'] as $key => $metric) {
            // $data['datasets'][]['label'] = $metric;
            $data['datasets'][] = array(
                'label' => $metric,
                'fillColor' => 'rgba(220,220,220,0.4)',
                'strokeColor' => 'rgba(220,220,220,1)',
                'pointHighlightFill' => 'rgba(220,220,220,0.4)',
                'pointHighlightStroke' => 'rgba(220,220,220,1)',
            );
            $dataResults[$key] = array();
        }

        foreach ($results['rows'] as $key => $row) {
            $data['labels'][] = $row[0];
            foreach ($dataResults as $index => $item) {
                $data['datasets'][$index]['data'][] = $row[$index+2];
            }
        }

        return new JsonResponse($data, 200);

    }
}
