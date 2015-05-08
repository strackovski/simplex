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
 *
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
