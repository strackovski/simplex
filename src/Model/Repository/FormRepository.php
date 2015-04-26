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
use Doctrine\ORM\Query;
use nv\Simplex\Model\Entity\Form;
use nv\Simplex\Model\Entity\Page;

/**
 * Form Entity Repository
 *
 * @package nv\Simplex\Model\Repository
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormRepository extends EntityRepository
{

    public function save(Form $form)
    {
        $this->getEntityManager()->persist($form);
        $this->getEntityManager()->flush();

        return $form;
    }

    /**
     * Get posts
     *
     * @param array $filter Columns to filter by
     * @param bool  $hydration
     *
     * @return array
     */
    public function get(array $filter = null, $hydration = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        if (is_null($filter)) {
            $qb->add('select', 'u')
                ->add('from', 'nv\Simplex\Model\Entity\Form u')
                ->orderBy('u.created_at', 'DESC');
        }

        $query = $qb->getQuery();
        if ($hydration) {
            return $query->getResult(Query::HYDRATE_ARRAY);
        }
        return $query->getResult();
    }

    public function delete(Form $form)
    {
        $this->getEntityManager()->remove($form);
        $this->getEntityManager()->flush();
    }
}
