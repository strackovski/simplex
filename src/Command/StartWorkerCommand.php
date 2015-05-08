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
