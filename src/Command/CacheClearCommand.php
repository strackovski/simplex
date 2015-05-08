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
 * Clears all defined cache stores.
 *
 * @package nv\Simplex\Command
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class CacheClearCommand extends ApplicationAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clear all registered cache stores.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pathToCache = APPLICATION_ROOT_PATH . '/var/cache/';
        foreach (
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $pathToCache,
                    \FilesystemIterator::SKIP_DOTS
                ),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
        rmdir($pathToCache);

        $output->writeln('<info>Cache cleared.</info>');
    }
}
