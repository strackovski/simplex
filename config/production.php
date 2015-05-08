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
