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
use Doctrine\ORM\Query;
use nv\Simplex\Model\Entity\Page;

/**
 * Page Entity Repository
 *
 * @package nv\Simplex\Model\Repository
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageRepository extends EntityRepository
{
    /**
     * @param Page $page
     *
     * @return Page
     */
    public function save(Page $page)
    {
        $this->getEntityManager()->persist($page);
        $this->getEntityManager()->flush();

        return $page;
    }

    /**
     * Delete page and associated objects
     *
     * @param Page $page
     */
    public function delete(Page $page)
    {
        $this->getEntityManager()->remove($page);
        $this->getEntityManager()->flush();
    }

    /**
     * @param      $title
     * @param bool $slug
     *
     * @return Page
     */
    public function create($title, $slug = false)
    {
        $page = $this->save(new Page($title, $slug));

        return $page;
    }

    /**
     * @param $slug
     *
     * @return bool
     */
    public function slugExists($slug)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Page', 'u');
        $qb->where($qb->expr()->eq('u.slug', '?1'));
        $qb->setParameters(array(1 => $slug));
        $q = $qb->getQuery();

        try {
            return $q->getSingleResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getMenuPages()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Page', 'u')
            ->where($qb->expr()->eq('u.inMenu', '?1'));
        $qb->setParameters(array(1 => true));
        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * Filter items
     *
     * @param array $parameter
     * @param bool  $hydration
     *
     * @return mixed
     */
    public function filter(array $parameter, $hydration = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Page', 'u')
            ->where($qb->expr()->eq('u.'.key($parameter), '?1'));
        $qb->setParameters(array(1 => $parameter[key($parameter)]));

        $query = $qb->getQuery();
        if ($hydration == 'array') {
            return $query->getSingleResult(Query::HYDRATE_ARRAY);
        }

        return $query->getSingleResult();
    }

    /**
     * @param int $max
     * @return array|mixed|string
     */
    public function getLatest($max = 1)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Page', 'u')
            ->orderBy('u.created_at', 'DESC')
            ->setMaxResults($max);

        $query = $qb->getQuery();
        try {
            if ($max === 1) {
                return $query->getSingleResult();
            }
            return $query->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array|mixed|string
     */
    public function getAuthors()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\User', 'u');

        $query = $qb->getQuery();
        try {
            return $query->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array|mixed|string
     */
    public function getTags()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Tag', 'u');

        $query = $qb->getQuery();
        try {
            return $query->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
