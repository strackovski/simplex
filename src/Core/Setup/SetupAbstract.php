<?php

namespace nv\Simplex\Core\Setup;

/**
 * Class SetupAbstract
 *
 * Provides a base class for concrete setup implementations
 *
 * @package nv\Simplex\Core\Setup
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
abstract class SetupAbstract
{
    /** @var null|string $path Path to configuration directory */
    protected $path;

    /**
     * @param null $configPath
     */
    public function __construct($configPath = null)
    {
        $this->path = $configPath;
    }

    /**
     * Custom application configuration
     *
     * Implement this function in concrete setup class to encapsulate the
     * specifics of project configuration.
     *
     * @return mixed
     */
    abstract public function configure();

    /**
     * Custom application verification
     *
     * Implement this function in concrete setup class to encapsulate the
     * specifics of project verification.
     *
     * @return mixed
     */
    abstract public function verify();

    /**
     * Prompt the user and return user's input
     *
     * @next Improve
     *
     * @param string $message Prompt message
     * @return string
     */
    protected function promptUser($message)
    {
        print "\n".$message;
        $handle = fopen("php://stdin", "r");

        return trim(fgets($handle));
    }

    /**
     * Write JSON configuration file
     *
     * @param $fileName string Name of config file including extension
     * @param $content array The content to be saved
     */
    protected function writeConfigFile($fileName, $content)
    {
        if (!file_exists($this->path . $fileName)) {
            fopen($this->path . $fileName, 'w');
        }

        try{
            file_put_contents($this->path . $fileName, json_encode($content), LOCK_EX);
            print "\nConfiguration saved.";
        } catch (\Exception $e) {
            print "\nAn error occured while writing configuration file.";
            return;
        }
    }

    /**
     * Verifies database connection configuration
     *
     * @param $configFile
     * @return bool|array Array of database connection parameters or false on error
     */
    protected function verifyDatabaseConfiguration($configFile)
    {
        if (!file_exists($this->path . $configFile)) {
            trigger_error("Irrecoverable error: Required configuration file $configFile not found.");
        }

        $db = json_decode(file_get_contents($this->path . $configFile), 1);

        if (isset($db) and is_array($db)) {

            foreach (array('driver', 'host', 'user', 'password', 'dbname') as $key) {
                if (!array_key_exists($key, $db)) {
                    trigger_error("Irrecoverable error: Invalid database configuration file.\n");
                }
            }
            print "\nVerifying database configuration...";

            switch ($db['driver']) {
                case 'mysqli':
                    if (class_exists('mysqli')) {
                        try {
                            $conn = new \mysqli(
                                $db['host'],
                                $db['user'],
                                $db['password']
                            );
                           print "\nConnection to MySQL Server successful.";

                            if (!mysqli_select_db($conn, $db['dbname'])) {

                                print "\nSchema {$db['dbname']} not found, trying to create it now...";

                                if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS {$db['dbname']};")) {
                                    trigger_error('Irrecoverable error: Schema creation failed.');
                                }
                                print "\nSchema {$db['dbname']} created successfully.";
                            }

                            print "\nDatabase configuration OK";

                        } catch (\ErrorException $e) {
                            trigger_error("\nError: " . $e->getMessage());
                        }
                    }
                    break;
                // @next Test PGSQL support
                case 'postgresql':
                    if (function_exists("pg_connect")) {
                        pg_connect(
                            "host={$db['host']} ".
                            "dbname={$db['dbname']} ".
                            "user={$db['user']} ".
                            "password={$db['password']}"
                        )
                        or trigger_error("\nError: ".pg_last_error());
                    }
                    break;
                // @next Test MSSQL support
                case 'mssql':
                    if (function_exists("mssql_connect")) {
                        $server = "{$db['host']}{$db['dbname']}";
                        $connection = mssql_connect(
                            $server,
                            $db['user'],
                            $db['password']
                        );
                        if (!$connection) {
                            trigger_error("\nError: Something went wrong while connecting to MSSQL");
                        } else {
                            $selected = mssql_select_db(
                                $db['dbname'],
                                $connection
                            )
                            or trigger_error(
                                "\nError: Cant't open database ".
                                "{$db['dbname']}"
                            );
                        }
                    }
                    break;
                }
                return $db;
        }
        trigger_error(
            "Failed retrieving database connection parameters.\n".
            "Make sure parameters are set in configuration file."
        );
        return false;
    }
}
