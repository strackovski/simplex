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
use nv\Simplex\Common\ObservableInterface;
use nv\Simplex\Common\ObserverInterface;
use nv\Simplex\Common\TimestampableAbstract;

/**
 * Page class
 *
 * Defines a page
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\PageRepository")
 * @Table(name="pages")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Page extends TimestampableAbstract implements ObservableInterface
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Page title
     *
     * @Column(name="title", type="string", length=255, nullable=false, unique=false)
     */
    protected $title;

    /**
     * Post subtitle
     *
     * @Column(name="slug", type="string", length=255, nullable=true, unique=true)
     */
    protected $slug;

    /**
     * View template
     *
     * @Column(name="master", type="string", length=255, nullable=true, unique=false)
     */
    protected $master;

    /**
     * View template
     *
     * @Column(name="view", type="string", length=255, nullable=true, unique=false)
     */
    protected $view;

    /**
     * Present in menu
     *
     * @Column(name="in_menu", type="boolean", nullable=true)
     */
    protected $inMenu;

    /**
     * Page description
     *
     * @Column(name="description", type="string", nullable=true)
     */
    protected $description;

    /**
     * @ManyToMany(targetEntity="PageQuery", orphanRemoval=true, cascade={"persist"})
     * @JoinTable(name="pages_queries",
     *      joinColumns={@JoinColumn(name="page_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="query_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    protected $queries;

    /**
     * User defined page type color
     *
     * @Column(name="type_color", type="string", nullable=true)
     */
    protected $typeColor;

    /** @var array */
    private $observers;

    /**
     * Constructor
     *
     * @param string      $title Page title
     * @param string|bool $slug  Page slug
     */
    public function __construct($title, $slug = false)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->queries = new ArrayCollection();
        $this->observers = array();
    }

    /**
     * Return page title
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @param ObserverInterface $observer
     *
     * @return mixed|void
     */
    public function registerObserver(ObserverInterface $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * @param ObserverInterface $observer
     *
     * @return mixed|void
     */
    public function detachObserver(ObserverInterface $observer)
    {
        $newobservers = array();
        foreach ($this->observers as $obs) {
            if ($obs !== $observer) {
                $newobservers[] = $obs;
            }
        }
        $this->observers = $newobservers;
    }

    /**
     * Notify registered observers/managers
     *
     * @return mixed|void
     */
    public function notifyObservers()
    {
        foreach ($this->observers as $obs) {
            if ($obs instanceof ObserverInterface) {
                $obs->update($this);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @param mixed $master
     */
    public function setMaster($master)
    {
        $this->master = $master;
    }

    /**
     * @param $typeColor
     */
    public function setTypeColor($typeColor)
    {
        $this->typeColor = $typeColor;
    }

    /**
     * @return mixed
     */
    public function getTypeColor()
    {
        return $this->typeColor;
    }

    /**
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return ArrayCollection
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * @param PageQuery $query
     *
     * @return $this
     */
    public function addQuery(PageQuery $query)
    {
        $this->queries->add($query);

        return $this;
    }

    /**
     * @param PageQuery $query
     *
     * @return $this
     */
    public function removeQuery(PageQuery $query)
    {
        $this->queries->removeElement($query);

        return $this;
    }

    /**
     * @return bool|string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param $inMenu
     */
    public function setInMenu($inMenu)
    {
        $this->inMenu = $inMenu;
    }

    /**
     * @return mixed
     */
    public function getInMenu()
    {
        return $this->inMenu;
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @PrePersist
     *
     * @return $this
     */
    public function prePersist()
    {
        $this->notifyObservers();
    }

    /**
     * @param $slug
     *
     * @return mixed
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set view template
     *
     * @param string $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Get view template
     *
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }
}
