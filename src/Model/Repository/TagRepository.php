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
use nv\Simplex\Model\Entity\Tag;

/**
 * Tag Entity Repository
 *
 * @package nv\Simplex\Model\Repository
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class TagRepository extends EntityRepository
{
    /**
     * @param Tag $tag
     *
     * @return Tag
     */
    public function save(Tag $tag)
    {
        $this->getEntityManager()->persist($tag);
        $this->getEntityManager()->flush();

        return $tag;
    }

    /**
     * @param      $title
     *
     * @return Tag
     */
    public function create($title)
    {
        $tag = $this->save(new Tag($title));

        return $tag;
    }
}
