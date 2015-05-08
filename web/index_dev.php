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

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1', '93.103.107.253'))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('Authorized access only.');
}

ini_set('display_errors', 1);

if (!file_exists(dirname(__DIR__) . '/config/parameters.json')) {
    header('HTTP/1.0 404 Not found');
    exit('Invalid configuration. Check manual for more information.');
}

define('APPLICATION_ENVIRONMENT', 'development');

require __DIR__.'/../src/bootstrap.php';

$app->run();