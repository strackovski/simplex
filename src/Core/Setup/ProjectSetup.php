<?php

namespace nv\Simplex\Core\Setup;

/**
 * Class ProjectSetup
 *
 * Manages CLI project configuration (pre-installation)
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
        $this->writeLine('*** WELCOME TO SIMPLEX ***', 'info');

        $this->writeLine('Checking application configuration...');
        if (!$this->verify()) {
            $this->writeLine('Some parameters are missing or invalid, please provide them.', 'info');
            $this->writeLine('Enter the database connection parameters.', '');
            $dbOptions = array(
                'driver' => 'Database driver',
                'host' => 'SQL server hostname',
                'user' => 'Username',
                'password' => 'Password',
                'dbname' => 'Schema (database) name'
            );
            $dbConfig = array();
            foreach ($dbOptions as $key => $name) {
                $dbConfig[$key] = $this->promptUser($name.": ");
            }
            $this->setParameters($dbConfig, 'database');

            $this->writeLine('Enter connection parameters for the system mailing account.');
            $mailingOptions = array(
                'mail_host' => 'host',
                'mail_port' => 'port',
                'mail_username' => 'Username',
                'mail_password' => 'Password',
                'mail_auth_mode' => 'auth',
                'mail_encryption' => 'Enc'
            );
            $mailingCfg = array();
            foreach ($mailingOptions as $key => $name) {
                $mailingCfg[$key] = $this->promptUser($name.": ");
            }
            $this->setParameters($mailingCfg, 'mailing');

            return $this->verify();
        }

        return;
    }

    /**
     * Verify application configuration
     *
     * @return bool True if configuration is valid, false otherwise
     */
    public function verify()
    {
        if (!$this->verifyDatabaseConfiguration()) {
            return false;
        }

        return true;
    }
}
