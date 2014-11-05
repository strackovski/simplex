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
if (!defined('APPLICATION_ROOT_PATH')) {
    define('APPLICATION_ROOT_PATH', __DIR__.'/..');
}

require_once APPLICATION_ROOT_PATH.'/vendor/autoload.php';
$app = new nv\Simplex\Core\Simplex();
try{
    require APPLICATION_ROOT_PATH.'/config/'.APPLICATION_ENVIRONMENT.'.php';
    $app->init();
}
catch(\Exception $e){
    return;
}