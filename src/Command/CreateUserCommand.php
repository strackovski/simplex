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
use nv\Simplex\Model\Entity\User;

/**
 * CreateUserCommand
 *
 * Create a new user account from user input.
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Would you like to create a new user account now?", false);
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
        $user->setEncodedPassword($this->app, $password);
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
