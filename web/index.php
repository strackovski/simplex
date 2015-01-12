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

ini_set('display_errors', 1);

define('APPLICATION_ENVIRONMENT', 'production');

if (!file_exists(dirname(__DIR__) . '/config/parameters.json')) {
    header('HTTP/1.0 404 Not found');
    exit('Invalid configuration. Check manual for more information.');
}

require __DIR__.'/../src/bootstrap.php';

$app->run();


