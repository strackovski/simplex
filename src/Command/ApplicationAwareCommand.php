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

use Symfony\Component\Console\Command\Command;
use Silex\Application;

/**
 * Base for app-aware commands
 *
 * @package nv\Simplex\Command
 * @author Саша Стаменковић <umpirsky@gmail.com>
 * @url https://github.com/umpirsky/silex-on-steroids
 */
abstract class ApplicationAwareCommand extends Command
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        parent::__construct();
    }
}
