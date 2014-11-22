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
use nv\Simplex\Model\Entity\MediaItem;
use nv\Simplex\Model\Entity\Video;

/**
 * Video Manager
 *
 * @package nv\Simplex\Core\Media
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class VideoManager implements MediaManagerInterface
{
    /** @var ImagineInterface */
    private $imagine;

    /**
     * Constructor
     *
     * @param ImagineInterface $imagine
     * @param bool $debug
     *
     * @internal param Video $video The video object
     */
    public function __construct(ImagineInterface $imagine, $debug = false)
    {
        $this->imagine = $imagine;
    }

    /**
     * Generate thumbnails
     *
     * Options array must be provided with dimension identifier as key:
     * array('large' => array(width, height))
     *
     * @param MediaItem $video
     * @param array $options Desired thumbnail dimensions
     *
     * @throws \Exception
     * @return mixed|Video
     */
    public function thumbnail(MediaItem $video, array $options = null)
    {
        if ($this->getStillFrame($video)) {
            foreach ($options as $dimension => $values) {
                if (in_array($dimension, array('small', 'medium', 'large'))) {
                    list($width, $height) = $values;
                    $this->imagine
                        ->open(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/'. $video->getMediaId() . '.jpeg'
                        )
                        ->thumbnail(new Box($width, $height))
                        ->save(
                            APPLICATION_ROOT_PATH .
                            '/web/uploads/thumbnails/' .
                            $dimension . '/' . $video->getMediaId() . '.jpeg'
                        );
                }
            }

            return $video;
        }

        return false;
    }

    /**
     * Crop a rectangle of relative size from center of image
     *
     * @param MediaItem|Video $video
     * @param array $options
     */
    public function autoCrop(MediaItem $video, array $options = null)
    {
        $image = $this->imagine->open(
            APPLICATION_ROOT_PATH .
            '/web/uploads/thumbnails/large/'. $video->getMediaId() . '.jpeg'
        );

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
                $video->getMediaId() . '.jpeg'
            );
    }

    /**
     * Watermark video
     *
     * @param MediaItem|Video $video
     * @param string $pathToWatermark Absolute path to watermark file
     *
     * @return $this
     */
    public function watermark(MediaItem $video, $pathToWatermark)
    {
        if (! file_exists($pathToWatermark)) {
            throw new \InvalidArgumentException("Invalid path to watermark {$pathToWatermark}.");
        }

        $watermark = $this->imagine->open($pathToWatermark);
        $image     = $this->imagine->open($video->getMediaId() . '.jpeg');
        $size      = $image->getSize();
        $wSize     = $watermark->getSize();

        $bottomRight = new Point(
            $size->getWidth() - $wSize->getWidth(),
            $size->getHeight() - $wSize->getHeight()
        );

        $image->paste($watermark, $bottomRight)
              ->save(APPLICATION_ROOT_PATH . '/web/uploads/' . $video->getMediaId() . '.jpeg');

        return $video;
    }

    /**
     * Metadata extraction
     * @param MediaItem|Video $video
     * @return array|bool|mixed
     */
    public function metadata(MediaItem $video)
    {
        try {
            return $this->interpretMetadata($video);
        } catch (\Exception $e) {

        }

        return false;
    }

    /**
     * Use ffprobe (part of ffmpeg) to get metadata from video file
     *
     * @param Video $video
     * @param string $format Return format
     *
     * @return mixed
     */
    private function ffprobe(Video $video, $format = 'json')
    {
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('file', APPLICATION_ROOT_PATH . '/var/logs/service.log', 'a')
        );

        $path = APPLICATION_ROOT_PATH.'/web/uploads/'.$video->getPath();
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
     * @param Video $video
     * @return mixed
     */
    public function recode(Video $video)
    {
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('file', APPLICATION_ROOT_PATH . '/var/logs/service.log', 'a')
        );

        $path = APPLICATION_ROOT_PATH.'/web/uploads/'.$video->getMediaId();
        // Use extension to retrieve source file correctly
        // Use highest possible quality to encode video to web h264 mp4
        // Use original resolution
        // Use correct codecs (h264/libx264 and mp4 container)

        //$cmd = "ffmpeg -i {$path}.{$video->getFileExtension()} -c:a libvorbis -movflags faststart {$path}.ogg";

        $cmd = "ffmpeg -i {$path}.{$video->getFileExtension()} -codec:v libx264 -profile:v high -preset slow -b:v 500k -maxrate 500k -bufsize 1000k -vf scale=-1:480 -threads 0 -ar 44100 -codec:a libmp3lame -b:a 128k {$path}.mp4";

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
     * @param Video $video
     * @throws \Exception
     * @return array
     */
    private function interpretMetadata(Video $video)
    {
        $data = $this->ffprobe($video);

        if (!array_key_exists('format', $data)) {
            throw new \Exception('Metadata extraction failed or metadata unavailable.');
        }

        if (!array_key_exists('streams', $data) and !array($data['streams'])) {
            throw new \Exception('Metadata extraction failed or metadata unavailable.');
        }

        $format = $data['format'];
        $videoData = $data['streams'][0];
        $audioData = $data['streams'][1];

        $result = array(
            'time_originated'   => $format['tags']['creation_time'],
            'width'         => $videoData['width'],
            'height'        => $videoData['height'],
            'duration'      => $format['duration'],
            'size'          => array(
                substr($format['size'], 0, strrpos($format['size'], ' ')),
                substr($format['size'], strrpos($format['size'], ' ')+1)
            ),
            'bit_rate'      => array(
                substr($format['bit_rate'], 0, strrpos($format['bit_rate'], ' ')),
                substr($format['bit_rate'], strrpos($format['bit_rate'], ' ')+1)
            ),
            'video' => array(
                'codec'         => $videoData['codec_name'],
                'codec_tag'     => $videoData['codec_tag_string'],
                'codec_level'   => $videoData['level'],
                'pixel_format'  => $videoData['pix_fmt'],
                'frame_count'   => $videoData['nb_frames']
            ),
            'audio' => array(
                'codec'         => $audioData['codec_name'],
                'codec_tag'     => $audioData['codec_tag_string'],
                'sample_format' => $audioData['sample_fmt'],
                'sample_rate'   => array(
                    substr($audioData['sample_rate'], 0, strrpos($audioData['sample_rate'], ' ')),
                    substr($audioData['sample_rate'], strrpos($audioData['sample_rate'], ' ')+1)
                ),
                'channels'      => $audioData['channels']
            ),
            'audio_stream'  => $audioData,
            'video_stream'  => $videoData
        );

        return $result;
    }

    /**
     * Capture a still frame from a video file using ffmpeg
     *
     * @param Video $video
     * @param integer|bool $moment Moment in video to capture in seconds from beginning
     *
     * @throws \Exception if unable to complete
     * @return resource Image resource
     */
    private function getStillFrame(Video $video, $moment = 1)
    {
        // descriptor array
        $desc = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('file', APPLICATION_ROOT_PATH . '/var/logs/service.log', 'a')
        );

        $cmd  = "ffmpeg -ss 00:00:01 -i ";
        $cmd .= APPLICATION_ROOT_PATH.'/web/uploads/'.$video->getPath();
        $cmd .= ' -frames:v 1 '.APPLICATION_ROOT_PATH.'/web/uploads/'.$video->getMediaId().'.jpeg';
        $p = proc_open($cmd, $desc, $pipes);
        fclose($pipes[0]);
        fclose($pipes[1]);
        proc_close($p);

        if (!file_exists(APPLICATION_ROOT_PATH.'/web/uploads/'.$video->getMediaId().'.jpeg')) {
            throw new \Exception('Unable to obtain a video still file.');
        }

        return imagecreatefromjpeg(APPLICATION_ROOT_PATH.'/web/uploads/'.$video->getMediaId().'.jpeg');
    }
}
