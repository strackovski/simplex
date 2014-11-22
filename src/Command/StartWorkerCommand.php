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
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting workers...</info>');

        $worker = new \GearmanWorker();
        $worker->addServer();

        $worker->addFunction("send_email", function (\GearmanJob $job) {
            $workload = json_decode($job->workload());
            echo "Sending email: " . print_r($workload, 1);
            sleep(5);
            // You would then, of course, actually call this:
            //mail($workload->email, $workload->subject, $workload->body);
        });

        while ($worker->work());
    }
}
