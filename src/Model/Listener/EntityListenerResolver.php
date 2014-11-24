<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use nv\Simplex\Core\Simplex;

/**
 * Class EntityListenerResolver
 *
 * Resolves entity-specific listeners
 *
 * @package nv\Simplex\Model\Listener
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class EntityListenerResolver extends DefaultEntityListenerResolver
{
    /**
     * @var Simplex
     */
    private $container;

    /**
     * @param Simplex $app
     */
    public function __construct(Simplex $app)
    {
        $this->container = $app;
    }

    /**
     * @param string $className
     * @return mixed|object
     */
    public function resolve($className)
    {
        $id = null;

        if ($className === 'nv\Simplex\Model\Listener\PostListener') {
            $id = 'post.listener';
        } elseif ($className === 'nv\Simplex\Model\Listener\MediaListener') {
            $id = 'media.listener';
        } elseif ($className === 'nv\Simplex\Model\Listener\PageListener') {
            $id = 'page.listener';
        } elseif ($className === 'nv\Simplex\Model\Listener\UserListener') {
            $id = 'user.listener';
        }

        if (is_null($id)) {
            return new $className();
        } else {
            return $this->container[$id];
        }
    }
}
