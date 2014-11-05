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
