<?php

namespace nv\Simplex\Core\Setup;

/**
 * Class ProjectSetup
 *
 * Enables project configuration from command line
 *
 * @package nv\Simplex\Core\Setup
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ProjectSetup extends SetupAbstract
{
    /**
     * Pre-Configure application
     *
     * @todo Improve data collection
     * Collect and save database configuration parameters
     */
    public function configure()
    {
        print "\n\n*** WELCOME TO SIMPLEX ***\n";
        print "\nThis will pre-configure your Simplex application instance.";
        print "\nIn case you already configured it by hand, you can skip this step.";
        print "\nEnter 'n(o)' to skip this step if application is already configured.";
        $value = $this->promptUser("\nWould you like to continue? ");
        if ($value !== 'y' and $value !== 'yes') {
            $this->verify();
            return;
        }

        print "\nPlease provide the following database server parameters:";
        $dbOptions = array(
            'driver' => 'Database driver (mysqli)',
            'host' => 'SQL server hostname',
            'user' => 'Username',
            'password' => 'Password',
            'dbname' => 'Schema (database) name'
        );
        $dbConfig = array();
        foreach ($dbOptions as $key => $name) {
            $dbConfig[$key] = $this->promptUser($name.": ");
        }
        $file = 'database.json';
        $this->writeConfigFile($file, $dbConfig);
        $this->verify();

        return;
    }

    /**
     * Verify application configuration
     *
     * @return bool True if configuration is valid, false otherwise
     */
    public function verify()
    {
        if( ! $this->verifyDatabaseConfiguration('database.json')) {
            return false;
        }

        return true;
    }
}
