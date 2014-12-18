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

namespace nv\Simplex\Core\Media;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use nv\Simplex\Model\Entity\Image;
use nv\Simplex\Model\Entity\MediaItem;

/**
 * Image Manager
 *
 * @package nv\Simplex\Core\Media
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ImageManager implements MediaManagerInterface
{
    /** @var ImagineInterface */
    private $imagine;

    /** @var FaceDetector */
    private $face;

    /**
     * @param ImagineInterface $imagine
     * @param FaceDetector $fd
     */
    public function __construct(ImagineInterface $imagine, FaceDetector $fd = null)
    {
        $this->imagine = $imagine;
        $this->face = $fd;
    }

    public function getFaceDetector()
    {
        return $this->face;
    }

    /**
     * @param MediaItem $image
     * @return bool
     * @throws \Exception
     */
    public function detectFace(MediaItem $image)
    {
        return $this->face->faceDetect(
            APPLICATION_ROOT_PATH .
            '/web/uploads/' .
            $image->getPath()
        );
    }

    /**
     * Generate thumbnails
     *
     * Options array must be provided with dimension identifier as key:
     * array('large' => array(width, height))
     *
     * @param MediaItem $image
     * @param array $options Desired thumbnail dimensions
     *
     * @param bool $stripMetadata
     * @return mixed|Image
     */
    public function thumbnail(MediaItem $image, array $options = null, $stripMetadata = true)
    {
        foreach ($options as $dimension => $values) {

            if (in_array($dimension, array('small', 'medium', 'large'))) {
                list($width, $height) = $values;
                if ($stripMetadata) {
                    $this->imagine
                        ->open($image->getAbsolutePath())
                        ->thumbnail(new Box($width, $height))
                        ->strip()
                        ->save(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/thumbnails/' .
                            $dimension . '/' . $image->getPath()
                        );
                } else {
                    $this->imagine
                        ->open($image->getAbsolutePath())
                        ->thumbnail(new Box($width, $height))
                        ->save(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/thumbnails/' .
                            $dimension . '/' . $image->getPath()
                        );
                }
            }
        }

        return $image;
    }

    /**
     * Crop a rectangle of predefined size from center of image
     *
     * @param MediaItem $image
     * @param array $options
     */
    public function crop2(MediaItem $image, array $options, $destination = false)
    {
        $imageTemp = $this->imagine->open($image->getAbsolutePath());
        $size = $imageTemp->getSize();

        if (count($options) === 4) {
            list($width, $height, $x, $y) = $options;
            $box = new Box($width, $height);
            $point = new Point($x, $y);

        } else {
            list($width, $height) = $options;
            $box = new Box($width, $height);
            $point = new Point(
                $size->getWidth() / 2 - $width / 2,
                $size->getHeight() / 2 - $height / 2
            );
        }

        $writeTo = APPLICATION_ROOT_PATH . '/web/uploads/crops/' . $image->getPath();

        if ($destination) {
            $writeTo = $destination;
        }

        $imageTemp->crop($point, $box)->save($writeTo);
    }

    /**
     * Crop a rectangle of predefined size from center of image
     *
     * @param MediaItem $image
     * @param array $options
     */
    public function crop(MediaItem $image, array $options, $destination = false)
    {
        $imageTemp = $this->imagine->open($image->getAbsolutePath());
        $size = $imageTemp->getSize();

        list($width, $height) = $options;
        $box = new Box($width, $height);

        $point = new Point(
            $size->getWidth() / 2 - $width / 2,
            $size->getHeight() / 2 - $height / 2
        );

        $writeTo = APPLICATION_ROOT_PATH . '/web/uploads/crops/' . $image->getPath();

        if ($destination) {
            $writeTo = $destination;
        }

        $imageTemp->crop($point, $box)->save($writeTo);
    }

    /**
     * Crop a rectangle of relative size from center of image
     *
     * @param MediaItem $image
     * @param array $options
     */
    public function autoCrop(MediaItem $image, array $options = null)
    {
        $imageTemp = $this->imagine->open($image->getAbsolutePath());
        $size = $imageTemp->getSize();
        $originalWidth = $size->getWidth();
        $originalHeight = $size->getHeight();
        $ratio = $originalWidth/$originalHeight;

        if ($ratio < 1) {
            $targetHeight = $targetWidth = $originalHeight * $ratio;
        } else {
            $targetWidth = $targetHeight = $originalWidth / $ratio;
        }

        if ($ratio < 1) {
            $srcX = 0;
            $srcY = ($originalHeight / 2) - ($originalWidth / 2);
        } else {
            $srcY = 0;
            $srcX = ($originalWidth / 2) - ($originalHeight / 2);
        }

        $point = new Point($srcX, $srcY);
        $box = new Box($targetWidth, $targetHeight);

        $imageTemp->crop($point, $box)
            ->save(
                APPLICATION_ROOT_PATH .
                '/web/uploads/crops/' .
                $image->getPath()
            );
    }

    /**
     * Watermark image
     *
     * @param MediaItem $image
     * @param string $pathToWatermark Absolute path to watermark file
     * @param string $watermarkPosition Watermark position in pixels, (0,0) is top-left corner
     *
     * @return $this
     */
    public function watermark(MediaItem $image, $pathToWatermark, $watermarkPosition = 'br')
    {
        if (!file_exists($pathToWatermark)) {
            throw new \InvalidArgumentException("Invalid path to watermark {$pathToWatermark}.");
        }

        $targets = $image->getVariations();
        $watermark = $this->imagine->open($pathToWatermark);

        foreach ($targets as $targetName => $targetPath) {
            $imageTemp = $this->imagine->open($targetPath);
            $size      = $imageTemp->getSize();
            $wSize     = $watermark->getSize();

            if ($size < $wSize) {
                $watermark = $watermark->thumbnail(new Box($size->getWidth() / 4, $size->getHeight() / 4));
                $wSize     = $watermark->getSize();
            }

            switch ($watermarkPosition) {
                // Top-right
                case 'tr':
                    $pos = new Point(
                        $size->getWidth() - $wSize->getWidth(),
                        0
                    );
                    break;

                // Top-left
                case 'tl':
                    $pos = new Point(0, 0);
                    break;

                // Bottom-left
                case 'bl':
                    $pos = new Point(
                        0,
                        $size->getHeight() - $wSize->getHeight()
                    );
                    break;

                // Center
                case 'cn':
                    $pos = new Point(
                        $size->getWidth() / 2 - $wSize->getWidth() / 2,
                        $size->getHeight() / 2 - $wSize->getHeight() / 2
                    );
                    break;

                // Default: bottom-right
                default:
                    $pos = new Point(
                        $size->getWidth() - $wSize->getWidth(),
                        $size->getHeight() - $wSize->getHeight()
                    );
                    break;
            }
            $imageTemp->paste($watermark, $pos)->save($targetPath);
        }
        return $this;
    }

    public function cleanUp(Image $image, $keepOriginal = true)
    {
        if ($keepOriginal == false) {
            unlink($image->getAbsolutePath());
        }
    }

    /**
     * Get metadata
     *
     * @param MediaItem $image
     * @return array
     */
    public function metadata(MediaItem $image)
    {
        return $this->interpretMetadata($image);
    }

    /**
     * Extract metadata if EXIF available
     *
     * @param Image $image
     * @return array
     */
    private function extractMetadata(Image $image)
    {
        return exif_read_data(APPLICATION_ROOT_PATH.'/web/uploads/'.$image->getPath());
    }

    /**
     * Interpret EXIF to application usable format
     *
     * @param Image $image
     * @return array
     */
    private function interpretMetadata(Image $image)
    {
        $exif = $this->extractMetadata($image);
        if (isset($exif['SectionsFound'])) {
            $sections = explode(',', $exif['SectionsFound']);
        }
        $result = array();
        $gps = array();

        if (array_key_exists('Make', $exif) && array_key_exists('Model', $exif)) $result['camera'] = $exif['Make'] . ' ' . $exif['Model'];
        if (array_key_exists('IsColor', $exif['COMPUTED'])) $result['is_color'] = $exif['COMPUTED']['IsColor'];
        if (array_key_exists('ApertureFNumber', $exif['COMPUTED'])) $result['aperture'] = $exif['COMPUTED']['ApertureFNumber'];
        if (array_key_exists('ApertureFNumber', $exif['COMPUTED'])) $result['aperture'] = $exif['COMPUTED']['ApertureFNumber'];
        if (array_key_exists('FocalLength', $exif)) $result['focal_length'] = $exif['FocalLength'];
        if (array_key_exists('ExposureTime', $exif)) $result['exposure_time'] = $exif['ExposureTime'];
        if (array_key_exists('DateTimeOriginal', $exif)) $result['time_originated'] = $exif['DateTimeOriginal'];
        if (array_key_exists('GPSLatitude', $exif)) $gps['gps_latitude'] = $exif['GPSLatitude'];
        if (array_key_exists('GPSLatitudeRef', $exif)) $gps['gps_latitude_ref'] = $exif['GPSLatitudeRef'];
        if (array_key_exists('GPSLongitude', $exif)) $gps['gps_longitude'] = $exif['GPSLongitude'];
        if (array_key_exists('GPSLongitudeRef', $exif)) $gps['gps_longitude_ref'] = $exif['GPSLongitudeRef'];

        $result['has_faces'] = '@todo';
        $result['color_palette'] = '@todo';

        if (count($gps) > 0) {
            $result['gps'] = $gps;
        }

        return $result;
    }
}
