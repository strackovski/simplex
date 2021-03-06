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
use nv\Simplex\Model\Entity\MediaItem;
use nv\Simplex\Model\Entity\Metadata;

/**
 * Media Entity Repository
 *
 * @package nv\Simplex\Model\Repository
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class MediaRepository extends EntityRepository
{
    /**
     * Persist image and flush store
     *
     * @param MediaItem $item Instance of item to save
     *
     * @return MediaItem
     */
    public function save(MediaItem $item)
    {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();

        return $item;
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

        $qb->select(array('u', 'm'))
            ->from('nv\Simplex\Model\Entity\MediaItem', 'u')
            ->leftJoin('u.metadata', 'm')
            ->where($qb->expr()->eq('u.'.key($parameter), '?1'));
        $qb->setParameters(array(1 => $parameter[key($parameter)]));
        $query = $qb->getQuery();

        if ($hydration == 'array') {
            return $query->getSingleResult(Query::HYDRATE_ARRAY);
        }
        return $query->getSingleResult();
    }

    /**
     * Get images
     *
     * @return array
     */
    public function getImages()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u', 'm'))
            ->from('nv\Simplex\Model\Entity\Image', 'u')
            ->leftJoin('u.metadata', 'm')
            ->orderBy('u.created_at', 'DESC');

        return $query = $qb->getQuery()->getResult();
    }

    /**
     * Get videos
     *
     * @return array
     */
    public function getVideos()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Video', 'u')
            ->orderBy('u.created_at', 'DESC');

        return $query = $qb->getQuery()->getResult();
    }

    /**
     * Get media items in library
     *
     * @return array
     */
    public function getLibraryMedia()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u', 'm'))
            ->from('nv\Simplex\Model\Entity\MediaItem', 'u')
            ->where($qb->expr()->eq('u.inLibrary', '?1'))
            ->leftJoin('u.metadata', 'm')
            ->orderBy('u.created_at', 'DESC');

        $qb->setParameters(array(1 => true));

        return $query = $qb->getQuery()->getResult();
    }

    /**
     * Get images in library
     *
     * @return array
     */
    public function getLibraryImages()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u', 'm'))
            ->from('nv\Simplex\Model\Entity\Image', 'u')
            ->where($qb->expr()->eq('u.inLibrary', '?1'))
            ->leftJoin('u.metadata', 'm')
            ->orderBy('u.created_at', 'DESC');

        $qb->setParameters(array(1 => true));

        return $query = $qb->getQuery()->getResult();
    }

    /**
     * Get videos in library
     *
     * @return array
     */
    public function getLibraryVideos()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('u', 'm'))
            ->from('nv\Simplex\Model\Entity\Video', 'u')
            ->where($qb->expr()->eq('u.inLibrary', '?1'))
            ->leftJoin('u.metadata', 'm')
            ->orderBy('u.created_at', 'DESC');

        $qb->setParameters(array(1 => true));

        return $query = $qb->getQuery()->getResult();
    }

    /**
     * @param int $max
     *
     * @return array|mixed|string
     */
    public function getLatest($max = 1)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\MediaItem', 'u')
            ->where($qb->expr()->eq('u.inLibrary', '?1'))
            ->orderBy('u.created_at', 'DESC')
            ->setMaxResults($max);

        $qb->setParameters(array(1 => true));

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
     * @param MediaItem $item
     */
    public function delete(MediaItem $item)
    {
        $em = $this->getEntityManager();
        if ($item->getMetadata() instanceof Metadata) {
            $em->remove($item->getMetadata());
        }
        $em->remove($item);
        $em->flush();
    }
}
