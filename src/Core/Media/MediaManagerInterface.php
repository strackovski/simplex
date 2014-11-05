<?php

namespace nv\Simplex\Core\Media;

use Imagine\Image\ImagineInterface;

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
     *
     * @param ImagineInterface $imagine Image processing library
     * @param array            $options Desired thumbnail dimensions
     *
     * @return mixed|\nv\Simplex\Model\Entity\MediaItem
     */
    public function thumbnail(ImagineInterface $imagine, array $options = null);

    /**
     * Watermark media item
     *
     * @param ImagineInterface $imagine Image processing library
     * @param string           $pathToWatermark Absolute path to watermark file
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function watermark(ImagineInterface $imagine, $pathToWatermark);

    /**
     * Extract and interpret media metadata if available
     *
     * @return mixed
     */
    public function metadata();
}
