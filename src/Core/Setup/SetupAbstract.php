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
        print $this->formatText($message, 'prompt');
        $handle = fopen("php://stdin", "r");

        return trim(fgets($handle));
    }

    protected function formatText($text, $format)
    {
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

        if ($format) {
            return sprintf($formats[$format], $text);
        }

        return $text;
    }

    public function writeLine($message, $format = false)
    {
        if ($format) {
            return print "\n" . $this->formatText($message, $format) . "\n";
        }

        return print "\n" . $message . "\n";
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

        try {
            file_put_contents($this->path . $fileName, json_encode($content), LOCK_EX);
            print "\nConfiguration saved.";
        } catch (\Exception $e) {
            print "\nAn error occured while writing configuration file.";
            return;
        }
    }

    protected function setParameters(array $parameters, $config)
    {
        $file = $this->path . 'parameters.json';
        if (!file_exists($file)) {
            fopen($file, 'w');
        }

        $params = json_decode(file_get_contents($file), 1);

        foreach ($parameters as $name => $value) {
            if (is_array($params)) {
                if (array_key_exists($config, $params)) {
                    if (array_key_exists($name, $params[$config])) {
                        $params[$config][$name] = $value;
                    }
                }
            }
        }

        try {
            file_put_contents($file, json_encode($params), LOCK_EX);
            print "\nConfiguration saved.";
            return true;
        } catch (\Exception $e) {
            print "\nAn error occured while writing configuration file.";
            return false;
        }
    }

    /**
     * Verifies database connection configuration
     *
     * @return bool|array Array of database connection parameters or false on error
     */
    protected function verifyDatabaseConfiguration()
    {
        $configFile = 'parameters.json';

        if (!file_exists($this->path . $configFile)) {
            fopen($this->path . $configFile, 'w');
            return false;
        }

        $config = json_decode(file_get_contents($this->path . $configFile), 1);

        if (isset($config) and is_array($config['database'])) {
            $db = $config['database'];
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
                            print "\n * Connection to MySQL Server successful.";

                            if (!mysqli_select_db($conn, $db['dbname'])) {
                                print $this->formatText(
                                    "\n * Schema {$db['dbname']} not found, trying to create it now...",
                                    'light-red'
                                );

                                if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS {$db['dbname']};")) {
                                    print $this->formatText("FAIL.\n", 'red');
                                    trigger_error('Irrecoverable error: Schema creation failed.');
                                }
                                print $this->formatText("DONE.\n", 'green');
                            }
                            print $this->formatText("\nDatabase configuration OK.\n", 'green');

                        } catch (\ErrorException $e) {
                            $this->writeLine($e->getMessage(), 'error');
                            $this->writeLine("Setup will now restart...\n");
                            return false;
                        }
                    }
                    break;
                // @next Test PGSQL support
                case 'postgresql':
                    if (function_exists("pg_connect")) {
                        pg_connect(
                            "host={$db['host']} ".
                            "dbname={$db['name']} ".
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
                            $db['duser'],
                            $db['dpassword']
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
                default:
                    return false;
            }
            return $db;
        }

        $config = array(
            'database' => array(
                'driver' => '',
                'host' => '',
                'user' => '',
                'password' => '',
                'dbname' => ''
            ),
            'mailing' => array(
                'mail_host' => '',
                'mail_port' => '',
                'mail_username' => '',
                'mail_password' => '',
                'mail_auth_mode' => '',
                'mail_encryption' => ''
            )
        );

        $this->writeConfigFile('parameters.json', $config);

        return false;
    }
}
