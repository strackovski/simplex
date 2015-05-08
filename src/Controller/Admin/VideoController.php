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

namespace nv\Simplex\Controller\Admin;

use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Core\Media\VideoManager;
use nv\Simplex\Model\Entity\Metadata;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\MediaRepository;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use nv\Simplex\Model\Entity\Video;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class VideoController
 *
 * Defines actions to perform on requests regarding Post objects.
 *
 * @package nv\Simplex\Controller
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class VideoController extends ActionControllerAbstract
{
    /** @var MediaRepository */
    private $media;

    /** @var  VideoManager */
    private $manager;

    public function __construct(
        MediaRepository $mediaRepository,
        VideoManager $manager,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->media = $mediaRepository;
        $this->manager = $manager;
    }

    /**
     * Index image items
     *
     * @param Request     $request
     * @return mixed
     */
    public function indexAction(Request $request)
    {
        $form = $this->form->createNamedBuilder(
            null,
            'form',
            array('test' => '')
        )
        ->add('test', 'text', array(
            'required' => false,
            'attr' => array(
                'name' => 'test'
            )
        ))->getForm();

        $ytvids = array();

        /** @var \nv\Simplex\Core\Service\GoogleApiAccount $google */
        if ($google = $this->settings->getApiAccount('google', 1)) {
            if ($token = $google->getAccessToken()) {
                $client = new \Google_Client();
                $client->setApplicationName($google->getAppName());
                $client->setClientId($google->getClientId());
                $client->setClientSecret($google->getClientSecret());
                $client->setRedirectUri($google->getRedirectUri());
                $client->setDeveloperKey($google->getApiKey());
                $client->addScope('https://www.googleapis.com/auth/youtube');
                $client->setAccessToken($token);
                if ($client->isAccessTokenExpired()) {
                    try {
                        $client->refreshToken($google->getRefreshToken());
                        $newToken = $client->getAccessToken();
                        $google->setAccessToken($newToken);
                    }
                    catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
                $youtube = new \Google_Service_YouTube($client);
                try{
                    $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
                        'mine' => 'true',
                    ));

                    foreach ($channelsResponse['items'] as $channel) {
                        // Extract the unique playlist ID that identifies the list of videos
                        // uploaded to the channel, and then call the playlistItems.list method
                        // to retrieve that list.
                        $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

                        $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
                            'playlistId' => $uploadsListId,
                            'maxResults' => 50
                        ));

                        foreach ($playlistItemsResponse['items'] as $playlistItem) {
                            $ytvids[] = $playlistItem;
                        }
                    }
                } catch (\Google_Service_Exception $e) {
                    $this->logger->addError('YouTube API: ' . $e->getMessage());
                    $data['errors'] = 'YouTube Data Unavailable';
                } catch (\Google_Exception $e) {
                    $this->logger->addError('YouTube API: ' . $e->getMessage());
                    $data['errors'] = 'YouTube Data Unavailable';
                }
            }
        }

        $data['yt'] = $ytvids;
        $data['form'] = $form->createView();
        $data['videos'] = $this->media->getLibraryVideos();

        return $this->twig->render('admin/'.$this->settings->getAdminTheme().'/views/videos.html.twig', $data);
    }

    /**
     * Upload video to YouTube
     *
     * Not available as standalone
     *
     * @param Request $request
     * @return bool
     */
    private function youTubeUploadTask(Request $request)
    {
        // @todo Implement YouTube video upload
        return false;
    }

    /**
     * Upload video
     *
     * @param Request     $request
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $files = $request->files;
        $token = $this->security->getToken();

         // @todo Check for YT upload when implemented
        foreach ($files as $uploadedFile) {
            $video = new Video();
            if (null !== $token) {
                $video->setAuthor($token->getUser());
            }
            $video->setPublished(true);
            $video->setInLibrary(true);
            $video->setFile($uploadedFile);
            $video->setName($uploadedFile->getClientOriginalName());
            $video->setMetadata($metadata = new Metadata());
            $this->media->save($video);

            try {
                $video->setMetadata($metadata->setData($this->manager->metadata($video)));
            } catch (\Exception $e) {
                $this->logger->addError('Failed retrieving video metadata: ' . $e->getMessage());
            }

            try {
                $this->media->save($video);
            } catch (\Exception $e) {
                $this->logger->addError('Failed adding video: ' . $e->getMessage());
                $this->media->delete($video);
            }
        }

        return new JsonResponse([$files]);
    }
}
