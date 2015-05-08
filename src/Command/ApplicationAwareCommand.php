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
