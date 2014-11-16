<?php

namespace nv\Simplex\Core\Media;

use nv\Simplex\Model\Entity\MediaItem;

/**
 * Media Manager Interface
 *
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
interface MediaManagerInterface
{
    /**
     * Generate thumbnails
     *
     * Options array must be provided with dimension identifier as key:
     * array('large' => array(width, height))
     *
     * @param MediaItem $item
     * @param array $options Desired thumbnail dimensions
     *
     * @return mixed|\nv\Simplex\Model\Entity\MediaItem
     */
    public function thumbnail(MediaItem $item, array $options = null);

    /**
     * Watermark media item
     *
     * @param MediaItem $item
     * @param string $pathToWatermark Absolute path to watermark file
     *
     * @return $this
     */
    public function watermark(MediaItem $item, $pathToWatermark);

    /**
     * Extract and interpret media metadata if available
     *
     * @param MediaItem $item
     * @return mixed
     */
    public function metadata(MediaItem $item);
}
