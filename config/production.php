<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 NV3, Vladimir Stračkovski <vlado@nv3.org>
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

ini_set('display_errors', '0');
$app['debug'] = false;

$app['root_cache_dir'] = APPLICATION_ROOT_PATH.'/var/cache';
$app['http_cache.cache_dir'] = APPLICATION_ROOT_PATH.'/var/cache/http';
$app['assetic.path_to_cache'] = APPLICATION_ROOT_PATH.'/var/cache/assetic';
$app['assetic.path_to_web'] = APPLICATION_ROOT_PATH.'/web/assets';

$config = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .'parameters.json'), 1);

$app['db.options'] = $config['database'];
$app['orm.options'] = array(
    'orm.proxies_dir' => APPLICATION_ROOT_PATH.'/var/cache/doctrine/proxy',
    //'orm.default_cache' => 'memcache',
    'orm.em.options' => array(
        "mappings" => array(
            array(
                "type"      => "annotation",
                "namespace" => "nv\\Simplex\\Model\\Entity",
                "path"      => APPLICATION_ROOT_PATH."/src/Model/Entity"
            ),
        ),
    )
);
