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
     * Form type [contact|subscription]
     *
     * @Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * Form title
     *
     * @Column(type="string", nullable=false)
     */
    protected $title;

    /**
     * Form field collection
     *
     * @ManyToMany(targetEntity="FormField", cascade={"persist", "detach"})
     * @JoinTable(name="forms_fields",
     *      joinColumns={@JoinColumn(name="field_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="form_id", referencedColumnName="id")}
     *      )
     **/
    protected $fields;

    /**
     * @OneToMany(targetEntity="FormResult", mappedBy="form", cascade={"persist", "detach"})
     **/
    protected $results;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $acceptCharset;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $action;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $autoComplete;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $encType;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $method;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $novalidate;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $target;

    /**
     * Published status
     *
     * @Column(name="published", type="boolean", nullable=true)
     */
    protected $published;

    /**
     * Content label
     *
     * @Column(name="label", type="string", nullable=true, unique=false)
     */
    protected $contentLabel;

    /**
     * Form post counter
     *
     * @Column(name="post_count", type="integer", nullable=true, unique=false)
     */
    protected $postCount;

    /**
     * Form author
     *
     * @ManyToOne(targetEntity="User", inversedBy="postsAuthored")
     * @JoinColumn(name="author_id", referencedColumnName="id")
     **/
    protected $author;

    /**
     * Last form editor
     *
     * @ManyToOne(targetEntity="User", inversedBy="postsEdited")
     * @JoinColumn(name="editor_id", referencedColumnName="id")
     **/
    protected $editor;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fields = new ArrayCollection();
        $this->results = new ArrayCollection();
        $this->postCount = 0;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * @todo This should be 'getContentType'
     * @return string
     */
    public function getType()
    {
        return 'form';
    }

    /**
     * @return mixed
     */
    public function getAcceptCharset()
    {
        return $this->acceptCharset;
    }

    /**
     * @param mixed $acceptCharset
     */
    public function setAcceptCharset($acceptCharset)
    {
        $this->acceptCharset = $acceptCharset;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAutoComplete()
    {
        return $this->autoComplete;
    }

    /**
     * @param mixed $autoComplete
     */
    public function setAutoComplete($autoComplete)
    {
        $this->autoComplete = $autoComplete;
    }

    /**
     * @return mixed
     */
    public function getEncType()
    {
        return $this->encType;
    }

    /**
     * @param mixed $encType
     */
    public function setEncType($encType)
    {
        $this->encType = $encType;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getNovalidate()
    {
        return $this->novalidate;
    }

    /**
     * @param mixed $novalidate
     */
    public function setNovalidate($novalidate)
    {
        $this->novalidate = $novalidate;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return mixed
     */
    public function getPostCount()
    {
        return $this->postCount;
    }

    /**
     * @param mixed $postCount
     */
    public function setPostCount($postCount)
    {
        $this->postCount = $postCount;
    }

    /**
     * @param $type
     */
    public function setFormType($type) {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getFormType() {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param mixed $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * @param FormResult $result
     */
    public function addResult(FormResult $result)
    {
        $this->results->add($result);
    }

    /**
     * @param FormResult $result
     */
    public function removeResult(FormResult $result)
    {
        $this->results->removeElement($result);
    }
}
