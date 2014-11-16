<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use nv\Simplex\Core\Post\PostManager;
use nv\Simplex\Model\Entity\Post;
use nv\Simplex\Model\Entity\Settings;

/**
 * Class PostListener
 * @package nv\Simplex\Model\Listener
 */
class PostListener implements EntityListenerInterface
{
    /**
     * @param PostManager $manager
     * @param Settings $settings
     */
    public function __construct(PostManager $manager, Settings $settings)
    {
        $this->manager = $manager;
        $this->settings = $settings;
    }

    /**
     * @param Post $post
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Post $post, LifecycleEventArgs $event)
    {
        $this->manager->slug($post);

        if ($this->settings->getEnableAnnotations()) {
            $this->manager->metadata($post);
        }
    }
}
