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

namespace nv\Simplex\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Tag class
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\TagRepository")
 * @Table(name="tags")
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Tag
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Tag name
     * @Column(name="name", type="string", length=255, nullable=false, unique=true)
     */
    protected $name;

    /**
     * Tagged posts
     * @ManyToMany(targetEntity="Post", mappedBy="tags")
     **/
    protected $posts;

    /**
     * Constructor
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->posts = new ArrayCollection();
    }

    /**
     * Return tag name
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add post to collection
     *
     * @param Post $post
     *
     * @return $this
     */
    public function addPost(Post $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * Remove post from tag
     *
     * @param Post $post
     *
     * @return $this
     */
    public function removePost(Post $post)
    {
        $this->posts->removeElement($post);

        return $this;
    }

    /**
     * Get tagged posts
     *
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }
}
