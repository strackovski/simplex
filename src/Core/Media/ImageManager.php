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

namespace nv\Simplex\Core\Media;

use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use nv\Simplex\Model\Entity\Image;

/**
 * Image Manager
 *
 * @package nv\Simplex\Core\Media
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ImageManager implements MediaManagerInterface
{
    private $image;
    private $debug;

    /**
     * Constructor
     *
     * @param Image $image
     * @param bool  $debug
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Image $image, $debug = false)
    {
        $this->image = $image;
        $this->debug = $debug;
    }

    /**
     * Generate thumbnails
     *
     * Options array must be provided with dimension identifier as key:
     * array('large' => array(width, height))
     *
     * @param ImagineInterface $imagine Image processing library
     * @param array $options Desired thumbnail dimensions
     *
     * @param bool $stripMetadata
     * @return mixed|Image
     */
    public function thumbnail(ImagineInterface $imagine, array $options = null, $stripMetadata = true)
    {
        foreach ($options as $dimension => $values) {

            if (in_array($dimension, array('small', 'medium', 'large'))) {

                list($width, $height) = $values;

                if ($stripMetadata) {
                    $imagine
                        ->open($this->image->getAbsolutePath())
                        ->thumbnail(new Box($width, $height))
                        ->strip()
                        ->save(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/thumbnails/' .
                            $dimension . '/' . $this->image->getPath()
                        );
                } else {
                    $imagine
                        ->open($this->image->getAbsolutePath())
                        ->thumbnail(new Box($width, $height))
                        ->save(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/thumbnails/' .
                            $dimension . '/' . $this->image->getPath()
                        );
                }
            }
        }
        return $this->image;
    }

    /**
     * Crop a rectangle of predefined size from center of image
     *
     * @param ImagineInterface $imagine
     * @param array            $options
     */
    public function crop(ImagineInterface $imagine, array $options)
    {
        $image = $imagine->open($this->image->getAbsolutePath());
        $size = $image->getSize();

        list($width, $height) = $options;
        $box = new Box($width, $height);

        $point = new Point(
            $size->getWidth() / 2 - $width / 2,
            $size->getHeight() / 2 - $height / 2
        );

        $image->crop($point, $box)
              ->save(
                  APPLICATION_ROOT_PATH .
                  '/web/uploads/crops/' .
                  $this->image->getPath()
              );
    }

    /**
     * Crop a rectangle of relative size from center of image
     *
     * @param ImagineInterface $imagine
     * @param array            $options
     */
    public function autoCrop(ImagineInterface $imagine, array $options = null)
    {
        $image = $imagine->open($this->image->getAbsolutePath());
        $size = $image->getSize();
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

        $image->crop($point, $box)
            ->save(
                APPLICATION_ROOT_PATH .
                '/web/uploads/crops/' .
                $this->image->getPath()
            );
    }

    /**
     * Watermark image
     *
     * @param ImagineInterface $imagine Image processing library
     * @param string $pathToWatermark Absolute path to watermark file
     * @param string $watermarkPosition Watermark position in pixels, (0,0) is top-left corner
     *
     * @return $this
     */
    public function watermark(ImagineInterface $imagine, $pathToWatermark, $watermarkPosition = 'br')
    {
        if (!file_exists($pathToWatermark)) {
            throw new \InvalidArgumentException("Invalid path to watermark {$pathToWatermark}.");
        }

        $targets = $this->image->getVariations();
        $watermark = $imagine->open($pathToWatermark);

        foreach ($targets as $targetName => $targetPath) {

            $image     = $imagine->open($targetPath);
            $size      = $image->getSize();
            $wSize     = $watermark->getSize();

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
            $image->paste($watermark, $pos)->save($targetPath);
        }
        return $this;
    }

    public function cleanUp($keepOriginal = true)
    {
        if ($keepOriginal == false) {
            unlink($this->image->getAbsolutePath());
        }
    }

    /**
     * Get metadata
     *
     * @return array
     */
    public function metadata()
    {
        return $this->interpretMetadata();
    }

    /**
     * Extract metadata if EXIF available
     *
     * @return array
     */
    private function extractMetadata()
    {
        return exif_read_data(APPLICATION_ROOT_PATH.'/web/uploads/'.$this->image->getPath());
    }

    /**
     * Interpret EXIF to application usable format
     *
     * @return array
     */
    private function interpretMetadata()
    {
        $exif = $this->extractMetadata();
        if (isset($exif['SectionsFound'])) {
            $sections = explode(',', $exif['SectionsFound']);
        }
        $result = array();

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

        return $result;
    }
}
