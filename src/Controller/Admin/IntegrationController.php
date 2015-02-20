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

namespace nv\Simplex\Controller\Admin;

use nv\Simplex\Controller\ActionControllerAbstract;
use nv\Simplex\Core\Service\GoogleApiAccount;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\SettingsRepository;
use Silex\Application;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\SecurityContext;
use nv\Simplex\Core\Service\TwitterApiAccount;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class IntegrationController
 *
 * @package nv\Simplex\Controller
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class IntegrationController extends ActionControllerAbstract
{
    /** @var SettingsRepository */
    private $settingsRepository;

    public function __construct(
        SettingsRepository $settingsRepository,
        Settings $settings,
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        SecurityContext $security,
        Session $session,
        UrlGenerator $url,
        Logger $logger
    ) {
        parent::__construct($settings, $twig, $formFactory, $security, $session, $url, $logger);
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Connect app to Google
     *
     * @param Request $request
     * @return string
     */
    public function googleConnectAction(Request $request)
    {
        if (!$this->settings->getApiAccount('google')) {
            $this->logger->addInfo('Google API access parameters not configured, service is disabled.');
        }

        /** @var GoogleApiAccount $google */
        $google = $this->settings->getApiAccount('google', 1);

        $client = new \Google_Client();
        $client->setApplicationName($google->getAppName());
        $client->setClientId($google->getClientId());
        $client->setClientSecret($google->getClientSecret());
        $client->setRedirectUri($google->getRedirectUri());
        $client->setDeveloperKey($google->getApiKey());
        $client->addScope('https://www.googleapis.com/auth/youtube');
        $client->addScope('https://www.googleapis.com/auth/drive');
        $client->addScope('https://www.googleapis.com/auth/analytics.readonly');
        $client->setAccessType('offline');

        if (isset($_GET['code'])) {
            $client->authenticate($_GET['code']);
            $_SESSION['token'] = $client->getAccessToken();
            $token = json_decode($_SESSION['token'], 1);
            $g = $this->settings->getApiAccount('google', 1);
            if ($g instanceof GoogleApiAccount) {
                $g->setAccessToken($_SESSION['token']);
                if (array_key_exists('refresh_token', $token)) {
                    $g->setRefreshToken($token['refresh_token']);
                }
                $this->settings->addApiAccount($g);
                $this->settingsRepository->save($this->settings);
            }
            // $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            // header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));

            return new RedirectResponse($this->url->generate('admin/dashboard'));
        }

        if (isset($_SESSION['token'])) {
            $client->setAccessToken($_SESSION['token']);
        } elseif ($google->getAccessToken() and $google->getAccessToken() != null) {
            $client->setAccessToken($google->getAccessToken());
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->refreshToken($google->getRefreshToken());
                $newToken = $client->getAccessToken();
            } else {
                $client->revokeToken();
                $_SESSION['service_auth_required'] = 1;
                echo "<a class='login' href='".$client->createAuthUrl()."'>Connect Me!</a>";
                return 1;
            }
        }

        if (!$client->getAccessToken()) {
            $authUrl = $client->createAuthUrl();
            $_SESSION['service_auth_required'] = 1;
            echo "<a class='login' href='$authUrl'>Connect Me!</a>";
        } else {
            try {
                $youtube = new \Google_Service_YouTube($client);
                echo '<br>Authorized to YouTube API<br>';
            } catch (\Google_Service_Exception $e) {
                if ($e->getCode() == 401) {
                    die('probably revoked');
                }
                echo 'A service error occurred: ' . htmlspecialchars($e->getMessage()) . '<br>';
            } catch (\Google_Exception $e) {
                echo 'A client error occurred: ' . htmlspecialchars($e->getMessage()) . '<br>';
            }
        }

        return '.';
    }

    /**
     * Check Google connection status
     *
     * @param Request $request
     * @return int|string
     */
    public function googleCheckAction(Request $request)
    {
        if (!$this->settings->getApiAccount('google')) {
            $this->logger->addInfo('Google API access parameters not configured, service is disabled.');
            return false;
        }

        /** @var GoogleApiAccount $google */
        $google = $this->settings->getApiAccount('google', 1);

        $client = new \Google_Client();
        $client->setApplicationName($google->getAppName());
        $client->setClientId($google->getClientId());
        $client->setClientSecret($google->getClientSecret());
        $client->setRedirectUri($google->getRedirectUri());
        $client->setDeveloperKey($google->getApiKey());
        $client->addScope('https://www.googleapis.com/auth/youtube');
        $client->addScope('https://www.googleapis.com/auth/drive');
        $client->addScope('https://www.googleapis.com/auth/analytics.readonly');
        $client->setAccessType('offline');

        if (!$google->getAccessToken()) {
            $authUrl = $client->createAuthUrl();
            $_SESSION['service_auth_required'] = 1;
            return "<a class='login' href='$authUrl'>Connect now!</a>";
        }

        $client->setAccessToken($google->getAccessToken());

        if ($client->isAccessTokenExpired()) {
            if ($google->getRefreshToken()) {
                echo 'found refresh';
            }

            $authUrl = $client->createAuthUrl();
            $_SESSION['service_auth_required'] = 1;
            return "<a class='login' href='$authUrl'>Expired, connect now!</a>";
        } else {
            try {
                $youtube = new \Google_Service_YouTube($client);
                $drive = new \Google_Service_Drive($client);
                $an = new \Google_Service_Analytics($client);
                return 200;
            } catch (\Google_Service_Exception $e) {
                if ($e->getCode() == 401) {
                    return 'Grant denied, revoked?';
                }
                return 'A service error occurred: ' . htmlspecialchars($e->getMessage()) . '<br>';
            } catch (\Google_Exception $e) {
                return 'A client error occurred: ' . htmlspecialchars($e->getMessage()) . '<br>';
            }
        }
    }

    /**
     * Check Twitter connection status
     *
     * @param Request $request
     * @return string
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    public function twitterCheckAction(Request $request)
    {
        if (!$this->settings->getApiAccount('twitter', 1)) {
            exit('no twitter');
        }
        /** @var TwitterApiAccount $twitter */
        $twitter = $this->settings->getApiAccount('twitter', 1);

        if ($token = $twitter->getAccessToken()) {
            // Access token is set, try if it is valid
            $connection = new TwitterOAuth(
                $twitter->getConsumerKey(),
                $twitter->getConsumerSecret(),
                $token['oauth_token'],
                $token['oauth_token_secret']
            );
            $connection->get("account/verify_credentials");
            if (200 !== $connection->lastHttpCode()) {
                // Token invalid
                if (429 == $connection->lastHttpCode()) {
                    return 'Too many requests in time window, API rate limiting in effect.';
                }
                echo 'No token or token invalid! ';
                $connection = new TwitterOAuth($twitter->getConsumerKey(), $twitter->getConsumerSecret());
                $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $twitter->getOauthCallback()));
                $_SESSION['oauth_token'] = $request_token['oauth_token'];
                $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
                $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
                return '<a href="'.$url.'">Connect to Twitter now</a>';
            }

            return $connection->lastHttpCode();
        } else {
            // No access token
            echo 'No token! ';
            $connection = new TwitterOAuth($twitter->getConsumerKey(), $twitter->getConsumerSecret());
            $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => $twitter->getOauthCallback()));
            $_SESSION['oauth_token'] = $request_token['oauth_token'];
            $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
            $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
            return '<a href="'.$url.'">Connect to Twitter now</a>';
        }
    }

    /**
     * Connect app to Twitter
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    public function twitterConnectAction(Request $request)
    {
        if (!$this->settings->getApiAccount('twitter', 1)) {
            exit('no twitter');
        }
        /** @var TwitterApiAccount $twitter */
        $twitter = $this->settings->getApiAccount('twitter', 1);

        /* Get temporary credentials from session. */
        $request_token = [];
        $request_token['oauth_token'] = $_SESSION['oauth_token'];
        $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

        /* If denied, bail. */
        if (isset($_REQUEST['denied'])) {
            exit('Permission was denied. Please start over.');
        }

        /* If the oauth_token is not what we expect, bail. */
        if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
            $_SESSION['oauth_status'] = 'oldtoken';
            exit;
        }

        /* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
        $connection = new TwitterOAuth(
            $twitter->getConsumerKey(),
            $twitter->getConsumerSecret(),
            $request_token['oauth_token'],
            $request_token['oauth_token_secret']
        );

        /* Request access tokens from twitter */
        $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $connection->lastHttpCode()) {
            /* Save the access tokens. Normally these would be saved in a database for future use. */
            $_SESSION['access_token'] = $access_token;
            $twitter->setAccessToken($access_token);
            $this->settings->addApiAccount($twitter);
            $this->settingsRepository->save($this->settings);

            /* Remove no longer needed request tokens */
            unset($_SESSION['oauth_token']);
            unset($_SESSION['oauth_token_secret']);
            /* The user has been verified and the access tokens can be saved for future use */
            $_SESSION['status'] = 'verified';
        } else {
            /* Save HTTP status for error dialog on connect page.*/
            $_SESSION['status'] = $connection->lastHttpCode();
        }

        // Redirect to status check
        $redirect = $this->url->generate('admin/service/twitter/check');
        return new RedirectResponse($redirect);
    }
}
