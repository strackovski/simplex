<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 Vladimir Stračkovski <vlado@nv3.org>
 * The MIT License <http://choosealicense.com/licenses/mit/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit the link above.
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
            if($max === 1){
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
            $item->setMetadata(null);
        }
        $em->remove($item);
        $em->flush();
    }
}
