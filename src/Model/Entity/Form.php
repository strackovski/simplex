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

namespace nv\Simplex\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use nv\Simplex\Common\TimestampableAbstract;

/**
 * Form class
 *
 * Defines a form
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\FormRepository")
 * @Table(name="forms")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Form extends TimestampableAbstract
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $title;

    /**
     * @ManyToMany(targetEntity="FormField", cascade={"persist", "detach"})
     * @JoinTable(name="forms_fields",
     *      joinColumns={@JoinColumn(name="field_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="form_id", referencedColumnName="id")}
     *      )
     **/
    protected $fields;

    /**
     *
     *
     * @var array
     * @Column(type="json_array", name="attributes", nullable=false)
     */
    protected $attributes;

    /**
     * @Column(name="published", type="boolean", nullable=true)
     */
    protected $published;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="postsAuthored")
     * @JoinColumn(name="author_id", referencedColumnName="id")
     **/
    protected $author;

    /**
     * @ManyToOne(targetEntity="User", inversedBy="postsEdited")
     * @JoinColumn(name="editor_id", referencedColumnName="id")
     **/
    protected $editor;

    /**
     * @Column(name="label", type="string", nullable=true, unique=false)
     */
    private $contentLabel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getContentLabel()
    {
        return $this->contentLabel;
    }

    /**
     * @param mixed $contentLabel
     */
    public function setContentLabel($contentLabel)
    {
        $this->contentLabel = $contentLabel;
    }

    /**
     * @return mixed
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * @param mixed $editor
     */
    public function setEditor($editor)
    {
        $this->editor = $editor;
    }

    /**
     * @return mixed
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param mixed $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getType()
    {
        return 'form';
    }
}
