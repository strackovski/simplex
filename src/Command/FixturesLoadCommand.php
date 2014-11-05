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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use nv\Simplex\Core\Post\PostManager;
use nv\Simplex\Model\Entity\Page;
use nv\Simplex\Model\Entity\Post;
use nv\Simplex\Model\Entity\User;

/**
 * Class FixturesLoadCommand
 *
 * Load data fixtures required to present a basic webpage.
 *
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
            $user->setFirstName('test');
            $user->setLastName('test');
            $user->setEmail('test@test.com');
            $user->setRoles('ROLE_ADMIN');
            $user->setDescription("This is the test user.");
            $user->setSalt($user->getEmail());
            $user->setIsActive(true);
            $user->setEncodedPassword($this->app, 'testing');
            $user->setCreatedAt(new \DateTime('now'));
            $user->setUpdatedAt($user->getCreatedAt());
            $this->app['orm.em']->persist($user);
            $authors[] = $user;
        }

        // Set route for homepage
        $homePage = new Page('Home');
        $homePage->setView('index');
        $homePage->setSlug('/');
        try{
            $this->app['orm.em']->persist($homePage);
            $this->app['orm.em']->flush();
        }
        catch (Exception $e) {
            sprintf("An error occured while loading fixtures: <info>%s</info>.", $e->getMessage());
        }

        $output->writeln('<info>Fixtures loaded.</info>');
    }
}
