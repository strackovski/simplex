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

namespace nv\Simplex\Core\Page;

use Doctrine\DBAL\Query\QueryException;
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
                    // ->from('nv\Simplex\Model\Entity\Post', 'u');
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
                    // $qb->select(array('u'))->from('nv\Simplex\Model\Entity\Post', 'u');
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

        $qb->orderBy('u.created_at', $this->query->getSortBy());

        if ($this->query->getLimitMax()) {
            $qb->setMaxResults($this->query->getLimitMax());
        }

        return $qb->getQuery();


    }
}
