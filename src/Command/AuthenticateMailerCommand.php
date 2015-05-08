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
 * CreateUserCommand
 *
 * Create a new user account from user input.
 *
 * @package nv\Simplex\Command
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class AuthenticateMailerCommand extends ApplicationAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('mailing:configure')
            ->setDescription('Configure system mailing account.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("\nConfigure system mailing account?", false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $question = new ChoiceQuestion(
            'Authentication mode',
            array('login', 'cram-md5', 'plain'),
            0
        );
        $question->setErrorMessage('Authentication mode %s is invalid.');
        $authMode = $helper->ask($input, $output, $question);
        $output->writeln('You have selected: ' . $authMode);

        $question = new ChoiceQuestion(
            'Authentication encryption mode',
            array('ssl', 'tls', 'none'),
            0
        );
        $question->setErrorMessage('Encryption mode %s is invalid.');
        $encMode = $helper->ask($input, $output, $question);
        $output->writeln('You have selected: ' . $encMode);

        $question = new Question('Username: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'Username should not contain invalid characters.'
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

        $question = new Question('Mail host: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'Mail host should not contain invalid characters.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $host = $helper->ask($input, $output, $question);

        $question = new Question('Mail host port: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'Mail host port should not contain invalid characters.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $port = $helper->ask($input, $output, $question);

        $config = array(
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'auth_mode' => $authMode,
            'encryption' => $encMode
        );

        $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'mailing.json';
        if (!file_exists($file)) {
            fopen($file, 'w');
        }

        try {
            file_put_contents($file, json_encode($config), LOCK_EX);
            $output->writeln('<info>Mailing configuration saved.</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>An error occured while writing configuration file.</error>');
            return;
        }
    }
}
