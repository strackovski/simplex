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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Google API Service Account Connector
 * @package nv\Simplex\Core\Connector
 */
class GoogleServiceConnector extends ServiceConnectorAbstract
{
    private $appName;

    protected $clientId;

    protected $emailAddress;

    protected $scopes;

    protected $impersonateEmail;

    protected $privateKey;

    public function __construct(
        $app_name = null,
        $client_id = null,
        $email_address = null
    ) {
        $this->appName = $app_name;
        $this->clientId = $client_id;
        $this->emailAddress = $email_address;
        $this->scopes = new ArrayCollection();
    }

    public function toArray()
    {
        return array(
            'appName' => $this->appName,
            'clientId' => $this->clientId,
            'emailAddress' => $this->emailAddress,
            'privateKey' => $this->privateKey,
            'scopes' => $this->scopes->toArray(),
            'impersonateEmail' => $this->impersonateEmail
        );
    }

    /**
     * @param $scope string Google scope specifier
     * @return ArrayCollection
     */
    public function addScope($scope) {
        $this->scopes->add($scope);
        return $this->scopes;
    }

    /**
     * @param $scope string Google scope specifier
     * @return ArrayCollection
     */
    public function removeScope($scope) {
        $this->scopes->removeElement($scope);
        return $this->scopes;
    }

    /**
     * @return ArrayCollection
     */
    public function getScopes() {
        return $this->scopes;
    }

    /**
     * @param $scopes
     */
    public function setScopes($scopes) {
        $this->scopes = $scopes;
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
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return mixed
     */
    public function getImpersonateEmail()
    {
        return $this->impersonateEmail;
    }

    /**
     * @param mixed $impersonateEmail
     */
    public function setImpersonateEmail($impersonateEmail)
    {
        $this->impersonateEmail = $impersonateEmail;
    }

    /**
     * Path to private key
     *
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Path to private key
     *
     * @param $privateKey string Absolute path to private key file
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Connect to Google
     *
     * @throws \Exception
     * @return \Google_Client
     */
    public function connect()
    {
        $privateKey = file_get_contents($this->privateKey);
        $credentials = new \Google_Auth_AssertionCredentials(
            $this->emailAddress,
            implode(',', $this->scopes),
            $privateKey
        );

        $client = new \Google_Client();
        $client->setAssertionCredentials($credentials);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        return $client;
    }
}