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

/**
 * UserVoter
 *
 * @package Sosa
 * @author Ronan Guilloux <ronan.guilloux@gmail.com>
 * @version 0.1
 * @copyright (C) 2013 Ronan Guilloux
 * @license MIT
 */

namespace nv\Simplex\Core\User;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleHierarchyVoter;
use nv\Simplex\Model\Entity\User;

class EditUserVoter implements VoterInterface
{
    /** @var RoleHierarchyVoter */
    protected $roleHierarchyVoter;

    /**
     * @param RoleHierarchyVoter $roleHierarchyVoter
     */
    public function __construct(RoleHierarchyVoter $roleHierarchyVoter)
    {
        $this->roleHierarchyVoter = $roleHierarchyVoter;
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return Boolean true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array('EDIT_USER', 'EDIT_USER_ID'));
    }

    /**
     * Checks if the voter supports the given user token class.
     *
     * @param string $class A class name
     *
     * @return true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object $object     The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return integer either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $user = $token->getUser();

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            if ($this->hasRole($token, 'ROLE_ADMIN')) {
                return VoterInterface::ACCESS_GRANTED;
            }

            if ($attribute == 'EDIT_USER') {
                $user2 = $object;
                return $this->usersHaveSameId($user, $user2) ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
            }

            if ($attribute == 'EDIT_USER_ID') {
                $id = $object;
                return $this->hasUserId($user, $id) ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * @param $token
     * @param $role
     * @return bool
     */
    protected function hasRole($token, $role)
    {
        return VoterInterface::ACCESS_GRANTED == $this->roleHierarchyVoter->vote($token, null, array($role));
    }

    /**
     * @param $user
     * @param $id
     * @return bool
     */
    protected function hasUserId($user, $id)
    {
        return $user instanceof User
            && $id > 0
            && $user->getId() == $id;
    }

    /**
     * @param $user1
     * @param $user2
     * @return bool
     */
    protected function usersHaveSameId($user1, $user2)
    {
        return $user1 instanceof User
            && $user2 instanceof User
            && $user1->getId() > 0
            && $user1->getId() == $user2->getId();
    }
}
