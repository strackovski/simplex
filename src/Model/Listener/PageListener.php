<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use nv\Simplex\Core\Page\PageManager;
use nv\Simplex\Model\Entity\Page;

/**
 * Class PostListener
 * @package nv\Simplex\Model\Listener
 */
class PageListener implements EntityListenerInterface
{
    /**
     * @param PageManager $manager
     */
    public function __construct(PageManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Page $post
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Page $post, LifecycleEventArgs $event)
    {

    }
}
