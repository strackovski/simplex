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
use Imagine\Image\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use nv\Simplex\Common\TimestampableAbstract;

/**
 * Media Item base
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\MediaRepository")
 * @InheritanceType("JOINED")
 * @Table(name="media_items")
 * @HasLifecycleCallbacks
 * @EntityListeners({"nv\Simplex\Model\Listener\MediaListener"})
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
     * @Column(name="original_author", type="string", length=100, nullable=true, unique=false)
     */
    protected $originalAuthor;

    /**
     * Image file
     *
     * @var UploadedFile
     *
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"image/jpeg", "image/gif", "image/png", "image/tiff"},
     *     maxSizeMessage = "The maximum allowed file size is 5MB.",
     *     mimeTypesMessage = "Only image file types are allowed."
     * )
     */
    protected $file;

    /**
     * Retrieved item name, like original file name
     *
     * @Column(name="file_ext", type="string", length=255, nullable=false)
     */
    protected $fileExtension;

    /**
     * Extracted metadata
     *
     * @OneToOne(targetEntity="Metadata", cascade={"persist"})
     * @JoinColumn(name="metadata_id", referencedColumnName="id")
     **/
    protected $metadata;

    /**
     * @ManyToMany(targetEntity="Post", mappedBy="mediaItems", cascade={"persist", "detach"})
     **/
    private $posts;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $inLibrary;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $published;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $hasFace;

    /**
     *  @Column(type="string", length=255, nullable=true)
     */
    protected $mediaCategory;

    /**
     * @Column(name="label", type="string", nullable=true, unique=false)
     */
    private $contentLabel;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $author;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
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
    public function getHasFace()
    {
        return $this->hasFace;
    }

    /**
     * @param mixed $hasFace
     */
    public function setHasFace($hasFace)
    {
        $this->hasFace = $hasFace;
    }

    /**
     * Return media type
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Return media ID
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getMediaId();
    }

    /**
     * @return mixed
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param bool $published
     */
    public function setPublished($published = false)
    {
        $this->published = $published;
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
        $this->inLibrary = $inLibrary;
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
     * @return mixed
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * @param mixed $fileExtension
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Get absolute path to media file
     *
     * @return null|string
     */
    public function getAbsolutePath()
    {
        return null === $this->mediaId . '.' . $this->fileExtension
            ? null
            : $this->getUploadRootDir().'/'. $this->mediaId . '.' . $this->fileExtension;
    }

    /**
     * Available media item variations
     *
     * @return array
     */
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
        if ($this->getType() === 'image' and $variation !== false and $this->getPath() !== null) {
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

            return $var.$this->getPath();
        }

        return null === $this->getPath()
            ? null
            : '/uploads/'.$this->getPath();
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
            //$this->path = $filename.'.'.$this->file->guessExtension();
            $this->mediaId = $filename;
            $this->fileExtension = $this->file->guessExtension();
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
            $this->getPath()
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
     * @return string
     */
    public function getPath()
    {
        /*
        return $this->path;
        */

        return $this->mediaId . '.' . $this->fileExtension;
    }

    /**
     * @param $author
     */
    public function setOriginalAuthor($author)
    {
        $this->originalAuthor = $author;
    }

    /**
     * @return mixed
     */
    public function getOriginalAuthor()
    {
        return $this->originalAuthor;
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
    public function setMetadata(Metadata $metadata)
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
        if ($this->title === null) {
            return $this->name;
        }

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
}
