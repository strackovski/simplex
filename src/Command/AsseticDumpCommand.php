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

/**
 * Class AsseticDumpCommand
 *
 * Dump assets to the filesystem as defined in Assetic configuration.
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class AsseticDumpCommand extends ApplicationAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('assetic:dump')
            ->setDescription('Writes all registered assets to the filesystem.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumper = $this->app['assetic.dumper'];
        if (isset($this->app['twig'])) {
            $dumper->addTwigAssets();
        }
        $dumper->dumpAssets();

        $output->writeln('<info>Dump finished.</info>');
    }
}
