<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use nv\Simplex\Core\Page\PageManager;
use nv\Simplex\Model\Entity\Page;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class PostListener
 * @package nv\Simplex\Model\Listener
 */
class PageListener extends EntityListenerAbstract
{
    /**
     * @param PageManager $manager
     */
    public function __construct(PageManager $manager, Logger $logger)
    {
        parent::__construct($logger);
        $this->manager = $manager;
    }

    /**
     * @param Page $page
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Page $page, LifecycleEventArgs $event)
    {

    }
}
