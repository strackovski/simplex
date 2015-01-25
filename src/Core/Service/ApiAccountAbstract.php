<?php

namespace nv\Simplex\Core\Service;

/**
 * Abstract Class API Account
 * @package nv\Simplex\Core\Service
 */
abstract class ApiAccountAbstract
{
    protected $clientId;

    protected $clientSecret;

    protected $redirectUri;

    protected $refreshToken;

    protected $accessToken;

    protected $scopes;

    protected $enabled;

    /**
     * @param $client_id
     * @param $client_secret
     * @param null $redirect_uri
     * @param array $scopes
     * @internal param $settings
     */
    public function __construct($client_id = null, $client_secret = null, $redirect_uri = null, array $scopes = null)
    {
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->redirectUri = $redirect_uri;
        $this->scopes = $scopes;
        $this->enabled = true;
        $this->accessToken = false;
        $this->refreshToken = false;
    }

    public function toArray()
    {
        $self = array(
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri,
            'scopes' => $this->scopes,
            'isEnabled' => $this->enabled,
            'refreshToken' => $this->refreshToken,
            'accessToken' => $this->accessToken
        );

        return $self;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param mixed $clientSecret
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

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return boolean
     */
    public function isAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param boolean $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }
}