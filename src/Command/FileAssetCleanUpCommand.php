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
 * Clears all defined cache stores.
 *
 * @package nv\Simplex\Command
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FileAssetCleanUpCommand extends ApplicationAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('assets:clean-up')
            ->setDescription('Remove all user-created file assets.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = APPLICATION_ROOT_PATH . '/web/uploads/';

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        ) as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        @rmdir($path);
        $output->writeln('<info>File assets removed.</info>');
    }
}
