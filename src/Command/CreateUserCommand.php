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
use nv\Simplex\Model\Entity\User;

/**
 * CreateUserCommand
 *
 * Create a new user account.
 *
 * @package nv\Simplex\Command
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class CreateUserCommand extends ApplicationAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('security:create-user')
            ->setDescription('Create a new user account.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("<info>Would you like to create a new user account now?</info>", false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $question = new ChoiceQuestion(
            'The role for this account (defaults to ROLE_ADMIN)',
            array('ROLE_ADMIN', 'ROLE_EDITOR'),
            0
        );
        $question->setErrorMessage('Role %s is invalid.');

        $role = $helper->ask($input, $output, $question);
        $output->writeln('You have selected: ' . $role);

        $question = new Question('Account email: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException(
                    'Email should be a valid email address.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $email = $helper->ask($input, $output, $question);

        $question = new Question('First name: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'First name should not contain invalid characters.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $firstName = $helper->ask($input, $output, $question);

        $question = new Question('Last name: ', false);
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_SANITIZE_STRING)) {
                throw new \RuntimeException(
                    'Last name should not contain invalid characters.'
                );
            }
            return $answer;
        });
        $question->setMaxAttempts(3);
        $lastName = $helper->ask($input, $output, $question);

        $question = new Question('Account password (no confirmation)');
        $question->setValidator(function ($answer) {
            if (trim($answer) == '') {
                throw new \Exception('The password can not be empty');
            }

            return $answer;
        });
        $question->setHidden(true);
        $question->setMaxAttempts(3);
        $password = $helper->ask($input, $output, $question);

        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setRoles($role);
        $user->setSalt($user->getEmail());
        $user->setIsActive(true);
        $user->setEncodedPassword($this->app['security.encoder.digest'], $password);
        $user->setCreatedAt(new \DateTime('now'));
        $user->setUpdatedAt($user->getCreatedAt());

        try {
            $this->app['orm.em']->persist($user);
            $this->app['orm.em']->flush();
        } catch (\Exception $e) {
            $output->writeln('<error>An error occured, account creation failed.</error>');
        }
        $output->writeln('<info>User account added. The user can now log in, no activation is required.</info>');
    }
}
