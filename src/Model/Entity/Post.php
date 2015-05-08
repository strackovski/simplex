<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use nv\Simplex\Common\TimestampableAbstract;
use nv\Simplex\Common\ObservableInterface;
use nv\Simplex\Common\ObserverInterface;

/**
 * Post class
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\PostRepository")
 * @EntityListeners({"nv\Simplex\Model\Listener\PostListener"})
 * @Table(name="posts")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Post extends TimestampableAbstract implements ObservableInterface
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Post title
     * @Column(name="title", type="string", length=255, nullable=false, unique=false)
     */
    protected $title;

    /**
     * Slug
     * @Column(name="slug", type="string", length=255, nullable=false, unique=true)
     */
    protected $slug;

    /**
     * Post subtitle
     * @Column(name="subtitle", type="string", length=255, nullable=true, unique=false)
     */
    protected $subtitle;

    /**
     * Post content body
     * @Column(name="body", type="text", nullable=false, unique=false)
     */
    protected $body;

    /**
     * @ManyToMany(targetEntity="MediaItem", inversedBy="posts", cascade={"persist", "detach"})
     * @JoinTable(name="posts_media",
     *      joinColumns={@JoinColumn(name="post_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="media_id", referencedColumnName="id")}
     *      )
     **/
    protected $mediaItems;

    /**
     * @OneToOne(targetEntity="Metadata", cascade={"all"})
     * @JoinColumn(name="metadata_id", referencedColumnName="id")
     **/
    protected $metadata;

    /**
     * @ManyToMany(targetEntity="Tag", inversedBy="posts", cascade={"persist"})
     * @JoinTable(name="posts_tags")
     **/
    protected $tags;

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
     * @Column(name="published", type="boolean", nullable=true)
     */
    protected $published;

    /**
     * @Column(name="published_interval", type="datetime", nullable=true)
     */
    protected $publishedInterval;

    /**
     * @Column(name="exposed", type="boolean", nullable=true)
     */
    protected $exposed;

    /**
     * @Column(name="exposed_interval", type="datetime", nullable=true)
     */
    protected $exposedInterval;

    /**
     * @Column(name="allow_ratings", type="boolean", nullable=true)
     */
    protected $allowRatings;

    /**
     * @Column(name="allow_comments", type="boolean", nullable=true)
     */
    protected $allowComments;

    /**
     * @var array
     */
    private $observers;

    /**
     * @Column(name="channels", type="json_array", nullable=true)
     */
    protected $channels;

    /**
     * @ManyToMany(targetEntity="Page", inversedBy="posts")
     * @JoinTable(name="posts_pages")
     **/
    private $pages;

    /**
     * @Column(name="label", type="string", nullable=true, unique=false)
     */
    private $contentLabel;

    /**
     * @Column(name="position_weight", type="integer", nullable=true)
     */
    private $positionWeight;

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
     * Return post title
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param $exposedInterval
     */
    public function setExposedInterval(\DateTime $exposedInterval = null)
    {
        $this->exposedInterval = $exposedInterval;
    }

    /**
     * @return mixed
     */
    public function getExposedInterval()
    {
        return $this->exposedInterval;
    }

    /**
     * @param $publishedInterval
     */
    public function setPublishedInterval(\DateTime $publishedInterval = null)
    {
        $this->publishedInterval = $publishedInterval;
    }

    /**
     * @return mixed
     */
    public function getPublishedInterval()
    {
        return $this->publishedInterval;
    }

    /**
     * @param $allowComments
     */
    public function setAllowComments($allowComments)
    {
        $this->allowComments = $allowComments;
    }

    /**
     * @return mixed
     */
    public function getAllowComments()
    {
        return $this->allowComments;
    }

    /**
     * @param $allowRatings
     */
    public function setAllowRatings($allowRatings)
    {
        $this->allowRatings = $allowRatings;
    }

    /**
     * @return mixed
     */
    public function getAllowRatings()
    {
        return $this->allowRatings;
    }

    /**
     * @param $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return mixed
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param $exposed
     */
    public function setExposed($exposed)
    {
        $this->exposed = $exposed;
    }

    /**
     * @return mixed
     */
    public function getExposed()
    {
        return $this->exposed;
    }

    /**
     * Constructor
     *
     * @param $title
     * @param $body
     */
    public function __construct($title, $body)
    {
        $this->title = $title;
        $this->body = $body;
        $this->mediaItems = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->observers = array();
        $this->pages = new ArrayCollection();
        $this->authors = new ArrayCollection();
    }


    public function registerObserver(ObserverInterface $observer)
    {
        $this->observers[] = $observer;
    }

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

            }
        }
    }

    /**
     * @param MediaItem $item
     *
     * @return $this
     */
    public function addMediaItem(MediaItem $item)
    {
        if (!$this->mediaItems->contains($item)) {
            $this->mediaItems[] = $item;
        }

        return $this;
    }

    /**
     * @param MediaItem $item
     * @return $this
     */
    public function removeMediaItem(MediaItem $item)
    {
        if ($this->mediaItems->contains($item)) {
            $this->mediaItems->removeElement($item);
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getMediaItems()
    {
        return $this->mediaItems;
    }

    /**
     * @return ArrayCollection
     */
    public function hasMediaItems()
    {
        if ($this->mediaItems->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get instance ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @return mixed
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function getTagsString()
    {
        $tags = '';
        $tagArr = array();

        foreach ($this->tags as $tagObj) {
            $tagArr[] = $tagObj->getName();
        }

        $last = end($tagArr);

        foreach ($tagArr as $tag) {
            $tags .= ($tag === $last) ? $tag : $tag . ', ';
        }

        return $tags;
    }

    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function addTag(Tag $tag)
    {
        $tag->addPost($this);
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @param Tag $tag
     *
     * @return $this
     */
    public function removeTag(Tag $tag)
    {
        $tag->removePost($this);
        $this->tags->remove($tag);

        return $this;
    }

    public function clearTags()
    {
        if ($this->tags->count() > 0) {
            foreach ($this->tags as $tag) {
                $this->tags->removeElement($tag);
            }
        }
        return $this;
    }

    /**
     * @param $metadata
     *
     * @return mixed
     */
    public function setMetadata($metadata)
    {
        return $this->metadata = $metadata;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return ArrayCollection
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setAuthor(User $user)
    {
        $this->author = $user;

        return $this;
    }

    /**
     * @return $this
     */
    public function clearAuthor()
    {
        $this->author = null;

        return $this;
    }

    /**
     * @param User $editor
     */
    public function setEditor(User $editor)
    {
        $this->editor = $editor;
    }

    /**
     * @return mixed
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * Clear attached media items
     */
    public function clearMedia()
    {
        foreach ($this->getMediaItems() as $media) {
            if ($media instanceof MediaItem) {
                $this->removeMediaItem($media);
            }
        }

        return $this;
    }

    public function getType()
    {
        return 'post';
    }

    /**
     * @return mixed
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param mixed $channels
     */
    public function setChannels($channels)
    {
        $this->channels = $channels;
    }

    public function addPage(Page $page)
    {
        $page->addPost($this);
        $this->pages[] = $page;
    }

    public function removePage(Page $page)
    {
        $page->removePost($this);
        $this->pages->remove($page);
    }

    /**
     * @return mixed
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param mixed $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
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
    public function getPositionWeight()
    {
        return $this->positionWeight;
    }

    /**
     * @param mixed $positionWeight
     */
    public function setPositionWeight($positionWeight)
    {
        $this->positionWeight = $positionWeight;
    }


}
