<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use nv\Simplex\Core\Post\PostManager;
use nv\Simplex\Model\Entity\Post;
use nv\Simplex\Model\Entity\Settings;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class PostListener
 * @package nv\Simplex\Model\Listener
 */
class PostListener extends EntityListenerAbstract
{
    /**
     * @param PostManager $manager
     * @param Settings $settings
     * @param Logger $logger
     */
    public function __construct(PostManager $manager, Settings $settings, Logger $logger)
    {
        parent::__construct($logger);
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

        // @todo Check if twitter enabled and tweet this post

        if ($this->settings->getEnableAnnotations()) {
            try{
                $this->manager->metadata($post);
            } catch (\Exception $e) {
                $this->logger->addError(
                    'Failed retrieving metadata for post #' . $post->getId() . ': ' . $e->getMessage()
                );
            }
        }
    }
}
