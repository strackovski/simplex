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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('nv\Simplex', '1.0.0');
$console->getDefinition()->addOption(
    new InputOption(
        '--env',
        '-e',
        InputOption::VALUE_REQUIRED,
        'The Environment name.',
        'development'
    )
);
$console->setDispatcher($app['dispatcher']);

try {
    $console->setHelperSet(new Symfony\Component\Console\Helper\HelperSet(array(
        'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($app["db"]),
        'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($app["orm.em"]),
        'question' => new \Symfony\Component\Console\Helper\QuestionHelper(),
        'formatter' => new \Symfony\Component\Console\Helper\FormatterHelper()
    )));
} catch (\Exception $e) {

}

$console->addCommands(array(
    // Doctrine commands
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand,
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand,
    new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand,
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand,
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand,
    new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand,
    new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand,
    new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand,
    new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand,
    new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand,
    new \Doctrine\ORM\Tools\Console\Command\InfoCommand,
    new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand,
    new \Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand,
    new \Doctrine\DBAL\Tools\Console\Command\ImportCommand,
    new \Doctrine\DBAL\Tools\Console\Command\ReservedWordsCommand,
    new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand,

    // ContainerAware Commands
    new \nv\Simplex\Command\AsseticDumpCommand($app),
    new \nv\Simplex\Command\FixturesLoadCommand($app),
    new \nv\Simplex\Command\CreateUserCommand($app),
    new \nv\Simplex\Command\CacheClearCommand($app),
    new \nv\Simplex\Command\ConnectDatabaseCommand($app),
    new \nv\Simplex\Command\AuthenticateMailerCommand($app),
    new \nv\Simplex\Command\FileAssetCleanUpCommand($app),
    new \nv\Simplex\Command\StartWorkerCommand($app)

));

$app->boot();

return $console;
