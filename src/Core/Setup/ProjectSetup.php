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
     * Collect and save database configuration parameters
     */
    public function configure()
    {
        $this->writeLine('*** WELCOME TO SIMPLEX ***', 'info');

        $this->writeLine('Checking application configuration...');
        if (!$this->verify()) {
            $this->writeLine('Some parameters are missing or invalid, please provide them.', 'info');
            $this->writeLine('Enter database connection parameters.');
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
            $this->writeLine('You can set this later in the web interface.');
            $mailingOptions = array(
                'mail_host' => 'Mail server host address',
                'mail_port' => 'Mail server port',
                'mail_username' => 'Mail account username',
                'mail_password' => 'Mail account username',
                'mail_auth_mode' => 'Authentication mode',
                'mail_encryption' => 'Authentication encryption mode'
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
