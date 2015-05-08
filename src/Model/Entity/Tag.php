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
     * @ManyToMany(targetEntity="Post", mappedBy="tags", cascade={"persist"})
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
