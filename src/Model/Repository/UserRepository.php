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
