<?php

namespace nv\Simplex\Core\Api;

/**
 * Abstract Class API Authenticator
 * @package nv\Simplex\Core\Mailer
 */
abstract class ApiAuthenticatorAbstract
{
    private $clientId;

    private $clientSecret;

    private $redirectUri;

    private $refreshToken;

    private $scopes;

    /**
     * @param $settings
     */
    public function __construct($client_id, $client_secret, $redirect_uri = null, array $scopes = null)
    {
        $this->clientId = $client_id;
        $this->clientSecret = $client_secret;
        $this->redirectUri = $redirect_uri;
        $this->scopes = $scopes;
    }

    public function toArray()
    {
        return (array) $this;
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
}