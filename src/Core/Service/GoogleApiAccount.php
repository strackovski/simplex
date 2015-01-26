<?php

namespace nv\Simplex\Core\Service;

/**
 * Class Google API Account
 * @package nv\Simplex\Core\Service
 */
class GoogleApiAccount extends ApiAccountAbstract
{
    protected $clientId;

    protected $clientSecret;

    protected $redirectUri;

    protected $accessToken;

    protected $refreshToken;

    private $apiKey;

    private $appName;

    public function __construct($client_id = null, $client_secret = null, $redirect_uri = null)
    {
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->redirectUri = $redirect_uri;
    }

    public function toArray()
    {
        $r = parent::toArray();
        $r['clientId'] = $this->clientId;
        $r['clientSecret'] = $this->clientSecret;
        $r['redirectUri'] = $this->redirectUri;
        $r['accessToken'] = $this->accessToken;
        $r['refreshToken'] = $this->refreshToken;
        $r['apiKey'] = $this->apiKey;
        $r['appName'] = $this->appName;

        return $r;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return mixed
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param mixed $appName
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * @return null
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param null $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return null
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param null $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return null
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param null $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param mixed $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }
}