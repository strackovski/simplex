<?php

namespace nv\Simplex\Model\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use nv\Simplex\Core\Media\ImageManager;
use nv\Simplex\Core\Media\VideoManager;
use nv\Simplex\Model\Entity\Image;
use nv\Simplex\Model\Entity\MediaItem;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Entity\Video;

/**
 * Class MediaListener
 *
 * @package nv\Simplex\Model\Listener
 * @author Vladimir Stračkovski <vlado@nv3.org>
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
        $this->imageManager->thumbnail($image, $this->settings->getImageResizeDimensions());

        $this->settings->getImageAutoCrop() ?
            $this->imageManager->autoCrop($image, null) :
            $this->imageManager->crop($image, $this->settings->getImageResizeDimensions('crop'));

        if ($image->isInLibrary()) {
            if ($this->settings->getWatermarkMedia() and $this->settings->getWatermark()) {
                $this->imageManager->watermark(
                    $image,
                    APPLICATION_ROOT_PATH . '/web/uploads/' . $this->settings->getWatermark()->getPath(),
                    $this->settings->getWatermarkPosition()
                );
            }

            if (class_exists("\\GearmanClient")) {
                try {
                    $client = new \GearmanClient();
                    $client->addServer();
                    $result = $client->doBackground("process_image", json_encode($image->getId()));
                } catch (\Exception $e) {

                }
            }

            if ($this->settings->detectFacesInPhotos() == true) {
                $image->setHasFace(false);
                if ($this->imageManager->detectFace($image)) {
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
        $this->videoManager->recode($video);
    }
}
