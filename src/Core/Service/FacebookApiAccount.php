<?php

namespace nv\Simplex\Core\Service;

/**
 * Class Facebook API Account
 * @package nv\Simplex\Core\Service
 */
class FacebookApiAccount extends ApiAccountAbstract
{
    private $consumerKey;

    private $consumerSecret;

    private $oauthCallback;

    private $oauthToken;

    private $oauthTokenSecret;

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
        $r['oauthToken'] = $this->oauthToken;
        $r['oauthTokenSecret'] = $this->oauthTokenSecret;
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
    public function getOauthToken()
    {
        return $this->oauthToken;
    }

    /**
     * @param mixed $oauthToken
     */
    public function setOauthToken($oauthToken)
    {
        $this->oauthToken = $oauthToken;
    }

    /**
     * @return mixed
     */
    public function getOauthTokenSecret()
    {
        return $this->oauthTokenSecret;
    }

    /**
     * @param mixed $oauthTokenSecret
     */
    public function setOauthTokenSecret($oauthTokenSecret)
    {
        $this->oauthTokenSecret = $oauthTokenSecret;
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
}