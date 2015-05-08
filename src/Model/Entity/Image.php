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

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image
 *
 * @Entity
 * @Table(name="images")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Image extends MediaItem
{
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
     * Return item type
     *
     * @return string
     */
    public function getType()
    {
        return 'image';
    }

}
