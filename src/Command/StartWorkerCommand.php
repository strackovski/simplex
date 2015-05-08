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
class StartWorkerCommand extends ApplicationAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('workers:start')
            ->setDescription('Writes all registered assets to the filesystem.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting workers...</info>');

        if (!class_exists("\\GearmanWorker")) {
            throw new \Exception("Gearman not supported!");
        }

        $worker = new \GearmanWorker();
        $worker->addServer();

        // @todo Implement worker
        $worker->addFunction("process_image", function (\GearmanJob $job) {
            $workload = json_decode($job->workload());
            echo "Processing image: " . print_r($workload, 1);
            sleep(5);
        });

        while ($worker->work());
    }
}
