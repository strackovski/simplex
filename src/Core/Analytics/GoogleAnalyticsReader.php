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

namespace nv\Simplex\Core\Analytics;

/**
 * GoogleAnalyticsReader
 *
 * @package nv\Simplex\Core\Analytics
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class GoogleAnalyticsReader
{
    /** @var \Google_Client $client */
    protected $client;

    /** @var \Google_Service_Analytics $analytics */
    protected $analytics;

    public function __construct(\Google_Client $client)
    {
        $this->client = $client;
        $this->analytics = new \Google_Service_Analytics($this->client);
    }

    public function getAnalytics()
    {
        return $this->analytics;
    }

    public function getBarChartResults(array $metrics = null, array $dimensions = null)
    {
        $os = $this->analytics->data_ga->get(
            'ga:' . $this->getFirstprofileId(),
            '7daysAgo',
            'today',
            'ga:sessions',
            array('dimensions' => 'ga:operatingSystem,ga:operatingSystemVersion,ga:browser,ga:browserVersion')
        );

        echo '<pre>';
        print_r($os);
        echo '</pre>';

    }

    public function getSortedResults($profileId, array $metrics, array $dimensions)
    {
        $results = $this->getResults($profileId, $metrics, $dimensions);

        $data = array();
        $dataResults = array();
        $data['datasets'] = array();

        foreach ($results['query']['metrics'] as $key => $metric) {
            // $data['datasets'][]['label'] = $metric;
            $data['datasets'][] = array(
                'label' => $metric,
                'fillColor' => "rgba(220,220,220,0.2)",
                'strokeColor' => "rgba(220,220,220,1)",
                'pointColor' => "rgba(220,220,220,1)",
                'pointStrokeColor' => "#fff",
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

        return json_encode($data);
    }

    public function getResults($profileId, array $metrics, array $dimensions) {
        // Calls the Core Reporting API and queries for the number of sessions
        // for the last seven days.
        return $this->analytics->data_ga->get(
            'ga:' . $profileId,
            '7daysAgo',
            'today',
            implode(',', $metrics),
            array('dimensions' => implode(',', $dimensions)));
            //'ga:sessions,ga:pageviews',
            //array('dimensions' => 'ga:date'));
    }

    public function getFirstprofileId() {
        // Get the user's first view (profile) ID.
        // Get the list of accounts for the authorized user.
        $accounts = $this->analytics->management_accounts->listManagementAccounts();

        if (count($accounts->getItems()) > 0) {
            $items = $accounts->getItems();
            $firstAccountId = $items[0]->getId();

            // Get the list of properties for the authorized user.
            $properties = $this->analytics->management_webproperties
                ->listManagementWebproperties($firstAccountId);

            if (count($properties->getItems()) > 0) {
                $items = $properties->getItems();
                $firstPropertyId = $items[0]->getId();

                // Get the list of views (profiles) for the authorized user.
                $profiles = $this->analytics->management_profiles
                    ->listManagementProfiles($firstAccountId, $firstPropertyId);

                if (count($profiles->getItems()) > 0) {
                    $items = $profiles->getItems();

                    // Return the first view (profile) ID.
                    return $items[0]->getId();

                } else {
                    return 'No views (profiles) found for this user.';
                }
            } else {
                return 'No properties found for this user.';
            }
        } else {
            return 'No accounts found for this user.';
        }
    }


}
