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
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use nv\Simplex\Common\TimestampableAbstract;
use nv\Simplex\Core\Media\MediaManagerInterface;

/**
 * Media Item base
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\MediaRepository")
 * @InheritanceType("JOINED")
 * @Table(name="media_items")
 * @HasLifecycleCallbacks
 *
 * @DiscriminatorColumn(name="discr", type="string")
 * @DiscriminatorMap({"video" = "Video", "image" = "Image"})
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
abstract class MediaItem extends TimestampableAbstract
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Unique system-generated media identifier
     *
     * @Column(type="string", length=255, nullable=true, unique=true)
     */
    protected $mediaId;

    /**
     * Retrieved item name, like original file name
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * User defined item title
     *
     * @Column(name="title", type="string", length=255, nullable=true, unique=false)
     */
    protected $title;

    /**
     * User defined item description
     *
     * Description
     * @Column(name="description", type="string", length=255, nullable=true, unique=false)
     */
    protected $description;

    /**
     * License
     *
     * The license that applies to this media item (if any). Usually the name
     * of the license or link to full text.
     *
     * @Column(name="license", type="string", length=255, nullable=true, unique=false)
     */
    protected $license;

    /**
     * Original author
     *
     * The identification (name, email, etc.) of the author of the media
     *
     * @Column(name="author", type="string", length=100, nullable=true, unique=false)
     */
    protected $author;

    /**
     * Path to media file
     *
     * @var string
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * Image file
     *
     * @var UploadedFile
     *
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"image/jpeg", "image/gif", "image/png", "image/tiff"},
     *     maxSizeMessage = "The maximum allowed file size is 5MB.",
     *     mimeTypesMessage = "Only the file types image are allowed."
     * )
     */
    protected $file;

    /**
     * @OneToOne(targetEntity="Metadata", cascade={"persist"})
     * @JoinColumn(name="metadata_id", referencedColumnName="id")
     **/
    protected $metadata;

    /**
     * Media manager
     *
     * @var MediaManagerInterface
     */
    protected $manager;

    /**
     * @ManyToMany(targetEntity="Post", mappedBy="mediaItems", cascade={"persist", "detach"})
     **/
    private $posts;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $inLibrary;

    /**
     *  @Column(type="string", length=255, nullable=true)
     */
    protected $mediaCategory;

    /**
     * @return mixed
     */
    abstract public function getType();

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }

    /**
     *
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function isInLibrary()
    {
        return $this->inLibrary;
    }

    /**
     * @param bool $inLibrary
     */
    public function setInLibrary($inLibrary = false)
    {
        if ($inLibrary) {
            $this->inLibrary = true;
        }
    }

    /**
     * @param $mediaCategory
     */
    public function setMediaCategory($mediaCategory)
    {
        $this->mediaCategory = $mediaCategory;
    }

    /**
     * @return mixed
     */
    public function getMediaCategory()
    {
        return $this->mediaCategory;
    }

    /**
     * Get absolute path to media file
     *
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getVariations()
    {
        return array(
          'small' => $this->getUploadRootDir() . '/thumbnails/small/' . $this->getPath(),
          'medium' => $this->getUploadRootDir() . '/thumbnails/medium/' . $this->getPath(),
          'large' => $this->getUploadRootDir() . '/thumbnails/large/' . $this->getPath(),
        );
    }

    /**
     * Get web path to media file
     *
     * @param bool $variation
     * @return null|string
     */
    public function getWebPath($variation = false)
    {
        if ($this->getType() === 'image' and $variation !== false and $this->path !== null) {

            // @todo Improve variation selection
            switch ($variation) {
                case 'small':
                    $var = 'thumbnails/small/';
                    break;

                case 'medium':
                    $var = 'thumbnails/medium/';
                    break;

                case 'large':
                    $var = 'thumbnails/large/';
                    break;

                case 'crop':
                    $var = 'crops/';
                    break;

                default:
                    $var = 'thumbnails/medium/';
                    break;
            }


            return $var.$this->path;
        }

        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    /**
     * Get absolute directory path where media items are uploaded
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../'.$this->getUploadDir();
    }

    /**
     * @return string
     */
    protected function getUploadDir()
    {
        return 'web/uploads';
    }
/**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Process file to be uploaded
     *
     * @PrePersist()
     * @PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->file->guessExtension();
            $this->mediaId = $filename;
        }
    }

    /**
     * Remove file prior to removal of media instance
     *
     * @PreRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }

        $dir = array(
            $this->getUploadRootDir() . '/thumbnails/large/',
            $this->getUploadRootDir() . '/thumbnails/medium/',
            $this->getUploadRootDir() . '/thumbnails/small/',
            $this->getUploadRootDir() . '/crops/'
        );

        foreach ($dir as $d) {
            if (is_file($d . $this->getPath())) {
                unlink($d . $this->getPath());
            }
        }
    }

    /**
     * Handle file upload
     *
     * @PostPersist()
     * @PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move(
            $this->getUploadRootDir(),
            $this->path
        );

        $this->file = null;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * @return mixed
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set item title
     *
     * @param $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get item title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $mediaId
     */
    public function setMediaId($mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return mixed
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * @return MediaManagerInterface
     */
    public function getManager()
    {
        return $this->manager;
    }
}
