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
