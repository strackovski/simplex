<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 NV3, Vladimir Stračkovski <vlado@nv3.org>
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Model\Repository;

use Doctrine\ORM\EntityRepository;
use nv\Simplex\Model\Entity\User;
use Doctrine\ORM\Query;

/**
 * Post Entity Repository
 *
 * @package nv\Simplex\Model\Repository
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class UserRepository extends EntityRepository
{
    /**
     * Persist user and flush store
     *
     * @param User $user Instance of user to save
     * @return \nv\Simplex\Model\Entity\User
     */
    public function save(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    public function create($firstName, $lastName, $email, $description)
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setDescription($description);

    }

    /**
     * Get all users
     *
     * @param bool $hydrate Hydration to array, default if false
     *
     * @return array
     */
    public function getUsers($hydrate = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\User', 'u');

        $query = $qb->getQuery();

        if ($hydrate) {
            return $query->getResult(Query::HYDRATE_ARRAY);
        }
        return $query->getResult();
    }

    /**
     * @return string
     */
    private function generateResetToken()
    {
        return md5(uniqid(mt_rand(), true));
    }

    /**
     * @param User $user
     */
    public function setResetToken(User $user)
    {
        $em = $this->getEntityManager();
        $user->setResetTokenExpirationDate((new \DateTime())->add(new \DateInterval('P1D')));
        $token = $this->generateResetToken();

        while ($this->findOneBy(array('resetToken' => $token))) {
            $token = $this->generateResetToken();
        }

        $user->setResetToken($token);
        $em->persist($user);
        $em->flush();
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function validateResetToken(User $user)
    {
        if (is_null($user->getResetToken())) {
            return false;
        }

        if ($user->getResetTokenExpirationDate() < new \DateTime('now')) {
            return false;
        }

        return true;
    }

    /**
     * @param User $user
     */
    public function invalidateResetToken(User $user)
    {
        if (!is_null($user->getResetToken())) {
            $user->setResetToken(null);
            $user->setResetTokenExpirationDate(null);
        }
    }

    /**
     * Check if user exists
     *
     * @param $email
     *
     * @return null|object
     */
    public function userExists($email)
    {
        return $this->findOneBy(array('email' => $email));
    }
}
