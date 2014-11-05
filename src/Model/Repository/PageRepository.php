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

        try{
            return $q->getSingleResult();
        }
        catch (\Exception $e) {
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
            if($max === 1){
                return $query->getSingleResult();
            }
            return $query->getResult();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
