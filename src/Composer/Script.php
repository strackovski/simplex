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
        passthru('php bin/console assets:clean-up');
        print "Setting up directories...";
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
        print "\033[32mDONE\033[0m\n";
        chmod('bin/console', 0500);
        print "(Re)building the database...";
        exec('bin/rebuild-database');
        print "\033[32mDONE\033[0m\n";
        print "Dumping assets...";
        exec('php bin/console assetic:dump');
        print "\033[32mDONE\033[0m\n";
        print "Loading fixtures...";
        exec('php bin/console fixtures:load');
        print "\033[32mDONE\033[0m\n";
        print "\n\033[32m*** USER ACCOUNT CREATION ***\033[0m\n";
        print "\nTo use the application you must create an account for the administrative user. ";
        print "If you do not wish to create an account now, ";
        print "you can create it later by running security:create-user.\n\n";
        passthru('php bin/console security:create-user');
        print "\n\033[44mYour application is ready!\033[0m\n\n";
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
        passthru('clear');
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
