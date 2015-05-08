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

/*
 * CHECK FOR PHP VERSION
 */
const REQUIRED_PHP_VERSION = '5.3.3';
$errors = 0;
$fatals = 0;

$formats = array(
    'red' => "\033[0;31m%s\033[0m",
    'light-red' => "\033[1;31m%s\033[0m",
    'green' => "\033[0;32m%s\033[0m",
    'blue' => "\033[0;34m%s\033[0m",
    'purple' => "\033[0;35m%s\033[0m",
    'error' => "\033[41m%s\033[0m",
    'success' => "\033[42m%s\033[0m",
    'warning' => "\033[30m\033[43m%s\033[0m",
    'info' => "\033[44m%s\033[0m",
    'prompt' => "\033[34m\033[1;34m%s\033[0m",
);

print "\n";
print sprintf(
    $formats['green'],
    'Checking if server meets Simplex system requirements...'
). PHP_EOL;
print "\n";

if (phpversion() < REQUIRED_PHP_VERSION) {
    $fatals++;
    print sprintf(
        $formats['error'],
        '[FATAL]: Inadequate PHP version: PHP version > 5.3.3 required.'
    ). PHP_EOL . PHP_EOL;
} else {
    print sprintf(
        $formats['info'],
        '[PASS]: PHP version OK ' . phpversion()
    ). PHP_EOL . PHP_EOL;
}

if (!extension_loaded('mysql') and !extension_loaded('mysqli') and !extension_loaded('mysqlnd')) {
    $fatals++;
    print sprintf(
        $formats['error'],
        "[FATAL]: MySQL driver not found: mysql/mysqli/mysqlnd required."
    ). PHP_EOL . PHP_EOL;
} else {
    print sprintf(
        $formats['info'],
        '[PASS]: MySQL driver installed'
    ). PHP_EOL . PHP_EOL;
}

/*
 * CHECK IF REQUIRED EXTENSIONS ARE LOADED
 */
if (phpversion() < '5.5.0') {
    if (!extension_loaded('apc') or !ini_get('apc.enabled')) {
        $errors++;
        print sprintf(
            $formats['warning'],
            "[ERROR]: APC cache not enabled."
        ) . PHP_EOL;

        print "\nIt is recommended that you install APC or memcached. \n\n";
    }

    print sprintf(
        $formats['info'],
        "[PASS]: In-memory cache is enabled (APC)."
    ). PHP_EOL . PHP_EOL;
}

if (!extension_loaded('curl')) {
    $errors++;
    print sprintf(
        $formats['warning'],
        "[ERROR]: cURL library not found."
    ) . PHP_EOL;

    print "\nIt is recommended that you install the cURL library for PHP. \n\n";
} else {
    print sprintf(
        $formats['info'],
        '[PASS]: cURL library is installed'
    ). PHP_EOL . PHP_EOL;
}

if (!extension_loaded('gd') and !extension_loaded('imagemagick')) {
    $errors++;
    print sprintf(
        $formats['warning'],
        "[ERROR]: No image library installed."
    ) . PHP_EOL;

    print "\nSimplex will not be able to process any images.\n";
    print "It is recommended that you install gd or imagemagick. \n\n";
} else {
    print sprintf(
        $formats['info'],
        '[PASS]: Image library is installed.'
    ). PHP_EOL . PHP_EOL;
}

if (!extension_loaded('gearman')) {
    $errors++;
    print sprintf(
        $formats['warning'],
        "[ERROR]: Gearman job server not installed!"
    ) . PHP_EOL;

    print "\nSimplex will not be able to run time-consuming background tasks.\n";
    print "It is recommended that you install the Gearman extension \n";
    print "and the Gearman job server.\n\n";
} else {
    print sprintf(
        $formats['info'],
        '[PASS]: Gearman is installed.'
    ). PHP_EOL . PHP_EOL;
}

if ($fatals > 0) {
    print sprintf(
        $formats['error'],
        "Encountered fatal error(s)."
    ). PHP_EOL;

    print "Fix the errors labeled as [FATAL] to meet minimum requirements." . PHP_EOL . PHP_EOL;
}

if ($errors > 0) {
    print sprintf(
        $formats['warning'],
        "Encountered " . $errors . " error(s)."
    ). PHP_EOL;

    print  "For optimal performance it is recommended to fix the errors labeled as [ERROR]." . PHP_EOL . PHP_EOL;
}

if ($errors === 0 and $fatals === 0) {
    print sprintf(
        $formats['green'],
        'This server meets all requirements, check passed.'
    ). PHP_EOL . PHP_EOL;
}

if ($errors > 0 and $fatals === 0) {
    print sprintf(
        $formats['green'],
        'This server meets minimum requirements, but fails to meet the recommendations.'
    ). PHP_EOL . PHP_EOL;
}

if ($fatals > 0) {
    print sprintf(
        $formats['red'],
        'This server does not meet the minimum requirements, check failed.'
    ). PHP_EOL . PHP_EOL;
}