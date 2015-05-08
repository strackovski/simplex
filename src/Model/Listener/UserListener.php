<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use nv\Simplex\Core\User\UserManager;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Entity\User;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class UserListener
 * @package nv\Simplex\Model\Listener
 */
class UserListener extends EntityListenerAbstract
{
    /**
     * @param UserManager $manager
     * @param Settings $settings
     * @param Logger $logger
     */
    public function __construct(UserManager $manager, Settings $settings, Logger $logger)
    {
        parent::__construct($logger);
        $this->manager = $manager;
        $this->settings = $settings;
    }

    /**
     * @param User $user
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(User $user, LifecycleEventArgs $event)
    {
        if ($event instanceof PreUpdateEventArgs) {
            if ($event->hasChangedField('email')) {
                $this->manager->changeEmail($user, $event->getNewValue('email'));
            }
        }
    }

    /**
     * @param User $user
     * @param LifecycleEventArgs $event
     */
    public function prePersist(User $user, LifecycleEventArgs $event)
    {
        if ($user->getId() === null and $user->getIsActive() === false) {
            $this->manager->sendActivationNotification($user);
        }
    }
}
