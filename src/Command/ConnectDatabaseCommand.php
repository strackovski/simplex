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

namespace nv\Simplex\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * ConnectDatabaseCommand
 *
 * @package nv\Simplex\Command
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ConnectDatabaseCommand extends ApplicationAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('database:connect')
            ->setDescription('Configure database connection.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("\nConfigure database connection now?", false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $question = new ChoiceQuestion(
            'Database driver (defaults to mysql)',
            array('mysqli', 'pdo_mysql'),
            0
        );
        $question->setErrorMessage('Driver %s is invalid.');
        $driver = $helper->ask($input, $output, $question);
        $output->writeln('You have selected: ' . $driver);

        $question = new Question('Database host: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'Server host should not contain invalid characters.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $host = $helper->ask($input, $output, $question);

        $question = new Question('Database name: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'Database name should not contain invalid characters.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $name = $helper->ask($input, $output, $question);

        $question = new Question('DB Username: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'Username name should not contain invalid characters.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $username = $helper->ask($input, $output, $question);

        $question = new Question('Password: ', false);
        $question->setValidator(function ($answer) {
            return $answer;
        });
        $question->setHidden(true);
        $password = $helper->ask($input, $output, $question);

        $config = array(
            'dbname' => $name,
            'user' => $username,
            'password' => $password,
            'host' => $host,
            'driver' => $driver,
        );

        $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.json';
        if (!file_exists($file)) {
            fopen($file, 'w');
        }

        try {
            file_put_contents($file, json_encode($config), LOCK_EX);
            $output->writeln('<info>Database configuration saved, trying to connect....</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>An error occured while writing configuration file.</error>');
            return;
        }

        try {
            new \mysqli($host, $username, $password, $name);
            $output->writeln('<info>Connection OK.</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            file_put_contents($file, null, LOCK_EX);
            return;
        }
    }
}
