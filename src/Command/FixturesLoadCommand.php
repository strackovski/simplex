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
use Symfony\Component\Security\Acl\Exception\Exception;
use nv\Simplex\Model\Entity\Page;
use nv\Simplex\Model\Entity\User;

/**
 * Class FixturesLoadCommand
 *
 * Load data fixtures required to present a basic webpage.
 *
 * @package nv\Simplex\Command
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FixturesLoadCommand extends ApplicationAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('fixtures:load')
            ->setDescription('Load data fixtures to the database.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->app['debug']) {
            $authors = array();
            $user = new User();
            $user->setFirstName('Janez');
            $user->setLastName('Novak');
            $user->setEmail('janez@novak.com');
            $user->setRoles('ROLE_ADMIN');
            $user->setDescription("Moje ime je Janez Novak.");
            $user->setSalt($user->getEmail());
            $user->setIsActive(true);
            $user->setEncodedPassword($this->app['security.encoder.digest'], 'testing');
            $user->setCreatedAt(new \DateTime('now'));
            $user->setUpdatedAt($user->getCreatedAt());
            $this->app['orm.em']->persist($user);
            $authors[] = $user;
        }
        
        // Set route for homepage
        $homePage = new Page('Home');
        $homePage->setView('index');
        $homePage->setSlug('/');
        try {
            $this->app['orm.em']->persist($homePage);
            $this->app['orm.em']->flush();
        } catch (Exception $e) {
            sprintf("An error occured while loading fixtures: <info>%s</info>.", $e->getMessage());
        }

        $output->writeln('<info>Fixtures loaded.</info>');
    }
}
