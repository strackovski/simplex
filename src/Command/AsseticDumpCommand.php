<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AsseticDumpCommand
 *
 * Dump assets to the filesystem as defined in Assetic configuration.
 *
 * @package nv\Simplex\Command
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class AsseticDumpCommand extends ApplicationAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('assetic:dump')
            ->setDescription('Writes all registered assets to the filesystem.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
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
