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
use nv\Simplex\Model\Entity\Form;

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
