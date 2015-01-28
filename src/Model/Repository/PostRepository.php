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
use nv\Simplex\Model\Entity\Post;
use nv\Simplex\Model\Entity\Tag;

/**
 * Post Entity Repository
 *
 * Provides Post retrieval and creation functionality
 *
 * @package nv\Simplex\Model\Repository
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PostRepository extends EntityRepository
{
    /**
     * Persist post and flush store
     *
     * @param Post $post Instance of post to save
     *
     * @return Post
     */
    public function save(Post $post)
    {
        $this->getEntityManager()->persist($post);
        $this->getEntityManager()->flush();

        return $post;
    }

    /**
     * Delete post and associated objects
     *
     * @param Post $post
     */
    public function delete(Post $post)
    {
        if ($post->getMetadata()) {
            $this->getEntityManager()->remove($post->getMetadata());
            $post->setMetadata(null);
        }

        $this->getEntityManager()->remove($post);
        $this->getEntityManager()->flush();
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
            ->from('nv\Simplex\Model\Entity\Post', 'u');
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
     * Create a new post object
     *
     * @param string $title
     * @param string $body
     *
     * @return Post
     */
    public function create($title, $body)
    {
        $post = $this->save(new Post($title, $body));

        return $post;
    }

    /**
     * Get published posts
     *
     * @return array
     */
    public function getPublished()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Post', 'u');
        $qb->where($qb->expr()->eq('u.published', true));

        $q = $qb->getQuery();
        return $q->getResult();
    }

    public function getPostsWithTag($tag)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Post', 'u')
            ->leftJoin('u.tags', 'x')
            ->where($qb->expr()->in('x.name', '?1'))
            ->andWhere($qb->expr()->eq('u.published', '?2'));

        $qb->setParameters(array(
                1 => $tag,
                2 => true
            )
        );

        $q = $qb->getQuery();
        return $q->getResult();
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
                ->add('from', 'nv\Simplex\Model\Entity\Post u')
                ->orderBy('u.created_at', 'DESC');
        } elseif(is_array($filter)) {
            if (key($filter) === 'has_media' or key($filter) === 'in_pages') {
                $t = 'get'. lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', key($filter)))));
                return $this->$t($filter[key($filter)]);
            }
            $qb->select(array('u'))
                ->from('nv\Simplex\Model\Entity\Post', 'u')
                ->where($qb->expr()->eq('u.'.key($filter), '?1'))
                ->orderBy('u.created_at', 'DESC');
            $qb->setParameters(array(1 => $filter[key($filter)]));
        }

        $query = $qb->getQuery();
        if ($hydration) {
            return $query->getResult(Query::HYDRATE_ARRAY);
        }
        return $query->getResult();
    }

    /**
     * Filter items
     *
     * @param array $parameter The column name to filter by
     * @param bool  $hydration Result return type (array if true, object if false)
     *
     * @return mixed
     */
    public function filter(array $parameter, $hydration = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select(array('u'))
            ->from('nv\Simplex\Model\Entity\Post', 'u')
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
            ->from('nv\Simplex\Model\Entity\Post', 'u')
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
