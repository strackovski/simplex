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
use Symfony\Bridge\Monolog\Logger;

/**
 * Class MediaListener
 *
 * @package nv\Simplex\Model\Listener
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class MediaListener extends EntityListenerAbstract
{
    /**
     * @param ImageManager $imageManager
     * @param VideoManager $videoManager
     * @param Settings $settings
     * @param Logger $logger
     * @internal param MediaManagerInterface $manager
     */
    public function __construct(ImageManager $imageManager, VideoManager $videoManager, Settings $settings, Logger $logger)
    {
        parent::__construct($logger);
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

        return false;
    }

    /**
     * @param Image $image
     * @return int
     */
    protected function processImage(Image $image)
    {
        $this->imageManager->thumbnail($image, $this->settings->getImageResizeDimensions());

        try{
            $this->settings->getImageAutoCrop() ?
                $this->imageManager->autoCrop($image, null) :
                $this->imageManager->crop($image, $this->settings->getImageResizeDimensions('crop'));
        } catch (\Exception $e) {
            $this->logger->addError(
                "Cropping failed for media #" . $image->getId() . " with message: " . $e->getMessage()
            );
        }

        if ($image->isInLibrary()) {
            if ($this->settings->getWatermarkMedia() and $this->settings->getWatermark()) {
                try{
                    $this->imageManager->watermark(
                        $image,
                        APPLICATION_ROOT_PATH . '/web/uploads/' . $this->settings->getWatermark()->getPath(),
                        $this->settings->getWatermarkPosition()
                    );
                } catch (\Exception $e) {
                    $this->logger->addError(
                        "Watermarking failed for media #" . $image->getId() . " with message: " . $e->getMessage()
                    );
                }
            }

            if (class_exists("\\GearmanClient")) {
                try {
                    $client = new \GearmanClient();
                    $client->addServer();
                    $result = $client->doBackground("process_image", json_encode($image->getId()));
                } catch (\Exception $e) {
                    $this->logger->addError(
                        "Gearman failed processing image with message " . $e->getMessage()
                    );
                }
            }

            if ($this->settings->detectFacesInPhotos() == true) {
                $image->setHasFace(false);
                try{
                    $fd = $this->imageManager->detectFace($image);
                } catch (\Exception $e) {
                    $this->logger->addError(
                        "Face detection failed for media #" . $image->getId() . " with message: " . $e->getMessage()
                    );
                }
                if (isset($fd)) {
                    $image->setHasFace(true);
                    $faceLoc = json_decode($this->imageManager->getFaceDetector()->toJson(), 1);

                    if (is_array($faceLoc)) {
                        $this->imageManager->crop2(
                            $image,
                            array(
                                $faceLoc['w'],
                                $faceLoc['w'],
                                $faceLoc['x'],
                                $faceLoc['y']
                            ),
                            APPLICATION_ROOT_PATH . '/web/uploads/faces/' . $image->getPath()
                        );
                    }
                }
            }
        }
        try{
            $this->imageManager->cleanUp($image, $this->settings->getImageKeepOriginal());
        } catch (\Exception $e) {
            $this->logger->addError(
                "Cleanup failed for media #" . $image->getId() . " with message: " . $e->getMessage()
            );
        }

        return 1;
    }

    /**
     * @param Video $video
     * @return int
     */
    protected function processVideo(Video $video)
    {
        $metadata = new Metadata();

        try{
            $video->setMetadata($metadata->setData($this->videoManager->metadata($video)));
        } catch (\Exception $e) {
            $this->logger->addError(
                "Metadata retrieval failed for media #" . $video->getId() . " with message: " . $e->getMessage()
            );
        }

        try{
            $this->videoManager->thumbnail($video, $this->settings->getImageResizeDimensions());
        } catch (\Exception $e) {
            $this->logger->addError(
                "Thumbnail generation failed for media #" . $video->getId() . " with message: " . $e->getMessage()
            );
        }

        try{
            $this->videoManager->autoCrop($video, null);
        } catch (\Exception $e) {
            $this->logger->addError(
                "Cropping failed for media #" . $video->getId() . " with message: " . $e->getMessage()
            );
        }

        try{
            $this->videoManager->recode($video);
        } catch (\Exception $e) {
            $this->logger->addError(
                "Encoding failed for media #" . $video->getId() . " with message: " . $e->getMessage()
            );
        }
    }
}
