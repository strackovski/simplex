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

namespace nv\Simplex\Core\Page;

use Doctrine\ORM\EntityManager;
use nv\Simplex\Model\Entity\PageQuery;

/**
 * Query Manager
 *
 * Manages objects of type PageQuery.
 *
 * @package nv\Simplex\Core\Page
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class QueryManager
{
    /**
     * Page instance
     *
     * @var \nv\Simplex\Model\Entity\PageQuery
     */
    private $query;

    /**
     * @param PageQuery $query
     */
    public function __construct(PageQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @todo https://github.com/strackovski/simplex/issues/4
     *
     * @param EntityManager $em
     * @return \Doctrine\ORM\Query
     */
    public function buildQuery2(EntityManager $em) {
        $qb = $em->createQueryBuilder();
        $contentType = $this->query->getContentType(1);

        if ($this->query->getColumn() == 'author' || $this->query->getColumn() == 'tags') {
            // build join query
            $qb->select(array('u'))
                ->from($contentType, 'u')
                ->leftJoin('u.'.$this->query->getColumn(), 'x')
                ->where($qb->expr()->in('x.name', '?1'))
                ->andWhere($qb->expr()->eq('u.published', '?2'));

            $qb->setParameters(
                array(
                    1 => $this->query->getValue(),
                    2 => true
                )
            );
        } else {
            $qb->select(array('u'))->from($contentType, 'u');
            switch ($this->query->getOperator()) {
                case 'eq':
                    $qb->where($qb->expr()->eq('u.' . $this->query->getColumn(), '?1'))
                        ->andWhere($qb->expr()->eq('u.published', '?2'));

                    $qb->setParameters(
                        array(
                            1 => $this->query->getValue(),
                            2 => true
                        )
                    );
                    break;

                case 'in':
                    $qb->where($qb->expr()->in('u.' . $this->query->getColumn(), '?1'))
                        ->andWhere($qb->expr()->eq('u.published', '?2'));

                    $qb->setParameters(
                        array(
                            1 => $this->query->getValue(),
                            2 => true
                        )
                    );
                    break;

                case 'between':
                    $qb->where($qb->expr()->gte('u.'.$this->query->getColumn(), '?1'))
                        ->andWhere($qb->expr()->lte('u.'.$this->query->getColumn(), '?2'))
                        ->andWhere($qb->expr()->eq('u.published', '?3'));

                    $qb->setParameters(
                        array(
                            1 => new \DateTime($this->query->getValue()[0]),
                            2 => new \DateTime($this->query->getValue()[1]),
                            3 => true
                        )
                    );
                    break;

                case 'before':
                    $qb->where($qb->expr()->lte('u.'.$this->query->getColumn(), '?1'))
                        ->andWhere($qb->expr()->eq('u.published', '?2'));

                    $qb->setParameters(
                        array(
                            1 => new \DateTime($this->query->getValue()[0]),
                            2 => true
                        )
                    );
                    break;

                case 'after':
                    $qb->where($qb->expr()->gte('u.'.$this->query->getColumn(), '?1'))
                        ->andWhere($qb->expr()->eq('u.published', '?2'));

                    $qb->setParameters(
                        array(
                            1 => new \DateTime($this->query->getValue()[0]),
                            2 => true
                        )
                    );
                    break;
            }
        }

        $qb->orderBy('u.'.$this->query->getSortColumn(), $this->query->getSortOrder());

        if ($this->query->getLimitMin()) {
            $qb->setMinResults($this->query->getLimitMin());
        }

        if ($this->query->getLimitMax()) {
            $qb->setMaxResults($this->query->getLimitMax());
        }

        return $qb->getQuery();

    }

    /**
     * Query Builder
     * @todo remove when #2 is tested
     * @param EntityManager $em
     * @return \Doctrine\ORM\Query
     */
    public function buildQuery(EntityManager $em)
    {
        $qb = $em->createQueryBuilder();
        $contentType = $this->query->getContentType(1);
        switch ($this->query->getColumn()) {
            case 'title':
                $qb->select(array('u'))
                    ->from($contentType, 'u');
                if ($this->query->getOperator() == 'contains') {
                    $qb->where($qb->expr()->in('u.title', '?1'))
                        ->andWhere($qb->expr()->eq('u.published', '?2'));
                }
                else {
                    $qb->where($qb->expr()->eq('u.title', '?1'))
                        ->andWhere($qb->expr()->eq('u.published', '?2'));
                }

                $qb->setParameters(
                    array(
                        1 => $this->query->getValue(),
                        2 => true
                    )
                );
                break;

            case 'author':
                $qb->select(array('u'))
                    ->from($contentType, 'u');
                $qb->where($qb->expr()->eq('u.author', '?1'))
                    ->andWhere($qb->expr()->eq('u.published', '?2'));

                $qb->setParameters(
                    array(
                        1 => $this->query->getValue(),
                        2 => true
                    )
                );
                break;

            case 'contentLabel':
                $qb->select(array('u'))
                    ->from($contentType, 'u');
                $qb->where($qb->expr()->eq('u.contentLabel', '?1'))
                    ->andWhere($qb->expr()->eq('u.published', '?2'));

                $qb->setParameters(
                    array(
                        1 => $this->query->getValue(),
                        2 => true
                    )
                );
                break;

            case 'tags':
                if ($contentType == 'nv\Simplex\Model\Entity\Form') {
                    break;
                }

                $qb->select(array('u'))
                    ->from($contentType, 'u')
                    ->leftJoin('u.tags', 'x')
                    ->where($qb->expr()->in('x.name', '?1'))
                    ->andWhere($qb->expr()->eq('u.published', '?2'));

                $qb->setParameters(
                    array(
                        1 => $this->query->getValue(),
                        2 => true
                    )
                );
                break;

            case 'created_at':
            case 'updated_at':
                if ($this->query->getOperator() == 'from_to') {
                    $qb->select(array('u'))
                        ->from($contentType, 'u');
                    $qb->where($qb->expr()->gte('u.'.$this->query->getColumn(), '?1'))
                        ->andWhere($qb->expr()->lte('u.'.$this->query->getColumn(), '?2'))
                        ->andWhere($qb->expr()->eq('u.published', '?3'));

                    $qb->setParameters(
                        array(
                            1 => new \DateTime($this->query->getValue()[0]['date']),
                            2 => new \DateTime($this->query->getValue()[1]['date']),
                            3 => true
                        )
                    );
                } else {
                    $qb->select(array('u'))->from($contentType, 'u');
                    if ($this->query->getOperator() == 'before') {
                        $qb->where($qb->expr()->lte('u.'.$this->query->getColumn(), '?1'))
                            ->andWhere($qb->expr()->eq('u.published', '?2'));
                        $qb->setParameters(
                            array(
                                1 => new \DateTime($this->query->getValue()),
                                2 => true
                            )
                        );
                    } elseif ($this->query->getOperator() == 'after') {
                        $qb->where($qb->expr()->gte('u.'.$this->query->getColumn(), '?1'))
                            ->andWhere($qb->expr()->eq('u.published', '?2'));
                        $qb->setParameters(
                            array(
                                1 => new \DateTime($this->query->getValue()),
                                2 => true
                            )
                        );
                    }
                }
                break;
        }

        $qb->orderBy('u.created_at', $this->query->getSortOrder());

        if ($this->query->getLimitMax()) {
            $qb->setMaxResults($this->query->getLimitMax());
        }

        return $qb->getQuery();
    }
}
