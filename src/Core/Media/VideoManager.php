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
use nv\Simplex\Model\Entity\Video;

/**
 * Video Manager
 *
 * @package nv\Simplex\Core\Media
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class VideoManager implements MediaManagerInterface
{
    /** @var Video */
    private $video;

    /** @var bool */
    private $debug;

    /**
     * Constructor
     *
     * @param Video $video The video object
     * @param bool  $debug
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Video $video, $debug = false)
    {
        $this->video = $video;
        $this->debug = $debug;
    }

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
     * @return mixed|Video
     */
    public function thumbnail(ImagineInterface $imagine, array $options = null)
    {
        if ($this->getStillFrame()) {
            foreach ($options as $dimension => $values) {
                if (in_array($dimension, array('small', 'medium', 'large'))) {
                    list($width, $height) = $values;
                    $imagine
                        ->open(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/'. $this->video->getMediaId() . '.jpeg'
                        )
                        ->thumbnail(new Box($width, $height))
                        ->save(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/thumbnails/' .
                            $dimension . '/' . $this->video->getMediaId() . '.jpeg'
                        );
                }
            }

            return $this->video;
        }

        return false;
    }

    /**
     * Crop a rectangle of relative size from center of image
     *
     * @param ImagineInterface $imagine
     * @param array            $options
     */
    public function autoCrop(ImagineInterface $imagine, array $options = null)
    {
        $image = $imagine->open(
                APPLICATION_ROOT_PATH .
                    '/web/uploads/thumbnails/large/'. $this->video->getMediaId() . '.jpeg'
                );

        $size = $image->getSize();

        $originalWidth = $size->getWidth();
        $originalHeight = $size->getHeight();

        $ratio = $originalWidth/$originalHeight;

        if($ratio < 1) {
            $targetHeight = $targetWidth = $originalHeight * $ratio;

        } else {
            $targetWidth = $targetHeight = $originalWidth / $ratio;

        }

        if($ratio < 1) {
            $srcX = 0;
            $srcY = ($originalHeight / 2) - ($originalWidth / 2);

        } else {
            $srcY = 0;
            $srcX = ($originalWidth / 2) - ($originalHeight / 2);

        }

        $point = new Point(
            $srcX,
            $srcY
        );

        $box = new Box(
            $targetWidth,
            $targetHeight
        );

        $image->crop($point, $box)
            ->save(
                APPLICATION_ROOT_PATH .
                '/web/uploads/crops/' .
                $this->video->getMediaId() . '.jpeg'
            );
    }

    /**
     * Watermark video
     *
     * @param ImagineInterface $imagine Image processing library
     * @param string           $pathToWatermark Absolute path to watermark file
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function watermark(ImagineInterface $imagine, $pathToWatermark)
    {
        if (! file_exists($pathToWatermark)) {
            throw new \InvalidArgumentException("Invalid path to watermark {$pathToWatermark}.");
        }

        $watermark = $imagine->open($pathToWatermark);
        $image     = $imagine->open($this->video->getMediaId() . '.jpeg');
        $size      = $image->getSize();
        $wSize     = $watermark->getSize();

        $bottomRight = new Point(
            $size->getWidth() - $wSize->getWidth(),
            $size->getHeight() - $wSize->getHeight()
        );

        $image->paste($watermark, $bottomRight)
              ->save(APPLICATION_ROOT_PATH . '/web/uploads/' . $this->video->getMediaId() . '.jpeg');

        return $this->video;
    }

    /**
     * Metadata extraction
     */
    public function metadata()
    {
        try{
            $interpreted = $this->interpretMetadata();
            $this->video->setDuration($interpreted['duration']);
        } catch (\Exception $e) {

        }

        return $this->interpretMetadata();
    }

    /**
     * Use ffprobe (part of ffmpeg) to get metadata from video file
     *
     * @param string $format Return format
     *
     * @return mixed
     */
    private function ffprobe($format = 'json')
    {
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('file', APPLICATION_ROOT_PATH . '/var/logs/service.log', 'a')
        );

        $path = APPLICATION_ROOT_PATH.'/web/uploads/'.$this->video->getPath();
        $cmd = "ffprobe -print_format json -show_format -show_streams -pretty -loglevel quiet " . $path;
        $p = proc_open($cmd, $desc, $pipes);

        fclose($pipes[0]);
        $json = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($p);

        return json_decode($json, true);
    }

    /**
     * Convert video to web formats [currently ogg]
     *
     * @return mixed
     */
    public function recode()
    {
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('file', APPLICATION_ROOT_PATH . '/var/logs/service.log', 'a')
        );

        $path = APPLICATION_ROOT_PATH.'/web/uploads/'.$this->video->getMediaId();
        $cmd = "ffmpeg -i {$path}.avi -c:a libvorbis -movflags faststart {$path}.ogg";
        $p = proc_open($cmd, $desc, $pipes);

        fclose($pipes[0]);
        $json = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($p);

        return json_decode($json, true);
    }


    /**
     * Interpret & aggregate extracted metadata
     *
     * @throws \Exception
     * @return array
     */
    private function interpretMetadata()
    {
        $data = $this->ffprobe();

        if (!array_key_exists('format', $data)) {
            throw new \Exception('Metadata extraction failed or metadata unavailable.');
        }

        if (!array_key_exists('streams', $data) and !array($data['streams'])) {
            throw new \Exception('Metadata extraction failed or metadata unavailable.');
        }

        $format = $data['format'];
        $video = $data['streams'][0];
        $audio = $data['streams'][1];

        $result = array(
            'time_originated'   => $format['tags']['creation_time'],
            'width'         => $video['width'],
            'height'        => $video['height'],
            'duration'      => $format['duration'],
            'size'          => array(substr($format['size'], 0, strrpos($format['size'], ' ')), substr($format['size'], strrpos($format['size'], ' ')+1)),
            'bit_rate'      => array(substr($format['bit_rate'], 0, strrpos($format['bit_rate'], ' ')), substr($format['bit_rate'], strrpos($format['bit_rate'], ' ')+1)),
            'video' => array(
                'codec'         => $video['codec_name'],
                'codec_tag'     => $video['codec_tag_string'],
                'codec_level'   => $video['level'],
                'pixel_format'  => $video['pix_fmt'],
                'frame_count'   => $video['nb_frames']
            ),
            'audio' => array(
                'codec'         => $audio['codec_name'],
                'codec_tag'     => $audio['codec_tag_string'],
                'sample_format' => $audio['sample_fmt'],
                'sample_rate'   => array(substr($audio['sample_rate'], 0, strrpos($audio['sample_rate'], ' ')), substr($audio['sample_rate'], strrpos($audio['sample_rate'], ' ')+1)),
                'channels'      => $audio['channels']
            ),
            'audio_stream'  => $audio,
            'video_stream'  => $video
        );

        return $result;
    }

    /**
     * Capture a still frame from a video file using ffmpeg
     *
     * @param integer|bool $moment Moment in video to capture in seconds from beginning
     *
     * @return resource Image resource
     * @throws \Exception if unable to complete
     */
    private function getStillFrame($moment = 1)
    {
        // descriptor array
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('file', APPLICATION_ROOT_PATH . '/var/logs/service.log', 'a')
        );

        $cmd = "ffmpeg -ss 00:00:01 -i ".APPLICATION_ROOT_PATH.'/web/uploads/'.$this->video->getPath().' -frames:v 1 '.APPLICATION_ROOT_PATH.'/web/uploads/'.$this->video->getMediaId().'.jpeg';
        $p = proc_open($cmd, $desc, $pipes);
        fclose($pipes[0]);
        fclose($pipes[1]);
        proc_close($p);

        if ( ! file_exists(APPLICATION_ROOT_PATH.'/web/uploads/'.$this->video->getMediaId().'.jpeg')) {
            throw new \Exception('Unable to obtain a video still file.');
        }

        return imagecreatefromjpeg(APPLICATION_ROOT_PATH.'/web/uploads/'.$this->video->getMediaId().'.jpeg');
    }
}
