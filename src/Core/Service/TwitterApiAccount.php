<?php

namespace nv\Simplex\Core\Service;

/**
 * Class Twitter API Account
 * @package nv\Simplex\Core\Service
 */
class TwitterApiAccount extends ApiAccountAbstract
{
    /**
     * Twitter Consumer Key
     *
     * @var string
     */
    private $consumerKey;

    /**
     * Twitter Consumer Secret
     *
     * @var string
     */
    private $consumerSecret;

    /**
     * Twitter oAuth Callback URL
     *
     * @var string
     */
    private $oauthCallback;

    /**
     * Access token
     *
     * @var array
     */
    private $accessToken;

    public function __construct($consumer_key = null, $consumer_secret = null, $oauth_callback = null)
    {
        $this->consumerKey = $consumer_key;
        $this->consumerSecret = $consumer_secret;
        $this->oauthCallback = $oauth_callback;
    }

    public function toArray()
    {
        $r = parent::toArray();
        $r['consumerKey'] = $this->consumerKey;
        $r['consumerSecret'] = $this->consumerSecret;
        $r['oauthCallback'] = $this->oauthCallback;
        $r['accessToken'] = $this->accessToken;

        return $r;
    }

    /**
     * @return mixed
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * @param mixed $consumerKey
     */
    public function setConsumerKey($consumerKey)
    {
        $this->consumerKey = $consumerKey;
    }

    /**
     * @return mixed
     */
    public function getConsumerSecret()
    {
        return $this->consumerSecret;
    }

    /**
     * @param mixed $consumerSecret
     */
    public function setConsumerSecret($consumerSecret)
    {
        $this->consumerSecret = $consumerSecret;
    }

    /**
     * @return mixed
     */
    public function getOauthCallback()
    {
        return $this->oauthCallback;
    }

    /**
     * @param mixed $oauthCallback
     */
    public function setOauthCallback($oauthCallback)
    {
        $this->oauthCallback = $oauthCallback;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        if (!is_null($this->accessToken)) {
            return $this->accessToken;
        }
        return false;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }
}