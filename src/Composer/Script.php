<?php

namespace nv\Simplex\Composer;

use Composer\Script\Event;
use nv\Simplex\Core\Setup\ProjectSetup;

/**
 * Class Script
 *
 * Handle Composer post-install event to initiate application installation.
 * Requires user attention to create the first user account.
 *
 * @package nv\Simplex\Composer
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Script
{
    /**
     * Handle Composer's postInstall event
     *
     * Configure application directories, rebuild the database
     * dump assets and load data fixtures, start create user
     * interactive command.
     *
     * @param Event $event
     */
    public static function postInstall(Event $event)
    {
        print "\n** Starting Simplex Installer **\n\n";
        passthru('php bin/console assets:clean-up');
        print "Setting up directories...\n";
        self::mkdir('var', 0777);
        self::mkdir('var/cache', 0777);
        self::mkdir('var/logs', 0777);
        self::mkdir('web/uploads', 0777);
        self::mkdir('web/uploads/thumbnails', 0777);
        self::mkdir('web/uploads/thumbnails/large', 0777);
        self::mkdir('web/uploads/thumbnails/medium', 0777);
        self::mkdir('web/uploads/thumbnails/small', 0777);
        self::mkdir('web/uploads/crops', 0777);
        self::mkdir('web/assets', 0777);
        /*
        print "Setting up permissions...\n";
        chown('web/uploads/thumbnails', 'www-data');
        chgrp('web/uploads/thumbnails', 'www-data');
        chown('web/uploads/thumbnails/large', 'www-data');
        chgrp('web/uploads/thumbnails/large', 'www-data');
        chown('web/uploads/thumbnails/medium', 'www-data');
        chgrp('web/uploads/thumbnails/medium', 'www-data');
        chown('web/uploads/thumbnails/small', 'www-data');
        chgrp('web/uploads/thumbnails/small', 'www-data');
        chown('web/uploads/crops', 'www-data');
        chgrp('web/uploads/crops', 'www-data');
        */
        print "\nIt is recommended to configure a system mailing account for\nsystem related email messages (logs, user account activation).";
        passthru('php bin/console mailing:configure');
        chmod('bin/console', 0500);
        print "\nRebuilding the database...";
        exec('bin/rebuild-database');
        print "\nDumping assets...";
        exec('php bin/console assetic:dump');
        print "\nLoading fixtures...";
        exec('php bin/console fixtures:load');
        print "\nInstallation complete. \n";
        print "\nTo use the application you should create the first user account now.\n";
        print "You can create the account later by using the security:create-user command.\n";
        passthru('php bin/console security:create-user');
        print "\nYour application is ready!\n\n";
    }

    /**
     * Handle pre-install event
     *
     * Init project setup to configure the database before installation
     *
     * @param Event $event
     */
    public static function preInstall(Event $event)
    {
        $setup = new ProjectSetup(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR);
        $setup->configure();
    }

    /**
     * Helper for making directories
     *
     * @param $path
     * @param $mode
     */
    private static function mkdir($path, $mode)
    {
        if (is_dir($path)) {
            return;
        }

        mkdir($path, $mode);
    }
}
