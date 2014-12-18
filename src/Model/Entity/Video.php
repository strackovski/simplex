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

use Symfony\Component\HttpFoundation\File\UploadedFile;
use nv\Simplex\Core\Media\VideoManager;

/**
 * Video class
 *
 * A media item of type video.
 *
 * @Entity
 * @Table(name="videos")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Video extends MediaItem
{
    /**
     * Video file
     *
     * @var UploadedFile
     *
     * @Assert\File(
     *     maxSize = "300M",
     *     mimeTypes = {"video/mp4", "video/mov", "video/avi"},
     *     maxSizeMessage = "The maximum allowed file size is 300MB.",
     *     mimeTypesMessage = "Only the file types image are allowed."
     * )
     */
    protected $file;

    /**
     * @return string
     */
    public function getType()
    {
        return 'video';
    }
}
