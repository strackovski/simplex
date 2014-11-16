<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use nv\Simplex\Core\Media\ImageManager;
use nv\Simplex\Core\Media\VideoManager;
use nv\Simplex\Model\Entity\Image;
use nv\Simplex\Model\Entity\MediaItem;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Entity\Video;

/**
 * Class MediaListener
 * @package nv\Simplex\Model\Listener
 */
class MediaListener
{
    /**
     * @param ImageManager $imageManager
     * @param VideoManager $videoManager
     * @param Settings $settings
     * @internal param MediaManagerInterface $manager
     */
    public function __construct(ImageManager $imageManager, VideoManager $videoManager, Settings $settings)
    {
        $this->imageManager = $imageManager;
        $this->videoManager = $videoManager;
        $this->settings = $settings;
    }

    /**
     * @param MediaItem $media
     * @param LifecycleEventArgs $event
     * @return int
     */
    public function postPersist(MediaItem $media, LifecycleEventArgs $event)
    {
        if ($media instanceof Image) {
            return $this->processImage($media);
        } elseif ($media instanceof Video) {
            return $this->processVideo($media);
        }
    }

    /**
     * @param Image $image
     * @return int
     */
    protected function processImage(Image $image)
    {
        $metadata = new Metadata();
        $image->setMetadata($metadata->setData($this->imageManager->metadata($image)));

        $this->imageManager->thumbnail($image, $this->settings->getImageResizeDimensions());

        $this->settings->getImageAutoCrop() ?
            $this->imageManager->autoCrop($image, null) :
            $this->imageManager->crop($image, $this->settings->getImageResizeDimensions('crop'));

        if ($this->settings->getWatermarkMedia() and $this->settings->getWatermark()) {
            $this->imageManager->watermark(
                $image,
                APPLICATION_ROOT_PATH . '/web/uploads/' . $this->settings->getWatermark(),
                $this->settings->getWatermarkPosition()
            );
        }
        $this->imageManager->cleanUp($image, $this->settings->getImageKeepOriginal());

        return 1;
    }

    /**
     * @param Video $video
     * @return int
     */
    protected function processVideo(Video $video)
    {
        $metadata = new Metadata();
        $video->setMetadata($metadata->setData($this->videoManager->metadata($video)));

        $this->videoManager->thumbnail($video, $this->settings->getImageResizeDimensions());
        $this->videoManager->autoCrop($video, null);

        if (strtolower(pathinfo($video->getAbsolutePath(), PATHINFO_EXTENSION)) == 'avi') {
            $this->videoManager->recode($video);
        }

        return 1;

    }
}
