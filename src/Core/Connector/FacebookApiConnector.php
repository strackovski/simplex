<?php

/*
 * This software is licensed under the Apache 2 license, quoted below.
 *
 * Copyright 2015 NV3
 * Copyright 2015 Vladimir Stračkovski <vlado@nv3.org>

 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace nv\Simplex\Core\Connector;

/**
 * Class Facebook API Account
 * @package nv\Simplex\Core\Connector
 */
class FacebookApiConnector extends ApiConnectorAbstract
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