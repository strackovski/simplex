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

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1', '192.168.178.102', '192.168.178.106', '192.168.178.201', '86.61.77.139'))
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