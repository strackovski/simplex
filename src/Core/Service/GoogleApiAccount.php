<?php

namespace nv\Simplex\Core\Service;

/**
 * Class Google API Account
 * @package nv\Simplex\Core\Service
 */
class GoogleApiAccount extends ApiAccountAbstract
{
    private $accountLogin;

    private $apiKey;

    private $appName;

    public function __construct($client_id = null, $client_secret = null, $redirect_uri = null, array $scopes = null)
    {
        parent::__construct($client_id, $client_secret, $redirect_uri, $scopes);
    }

    public function toArray()
    {
        $r = parent::toArray();
        $r['accountLogin'] = $this->accountLogin;
        $r['apiKey'] = $this->apiKey;
        $r['appName'] = $this->appName;

        return $r;
    }

    /**
     * @return mixed
     */
    public function getAccountLogin()
    {
        return $this->accountLogin;
    }

    /**
     * @param mixed $accountLogin
     */
    public function setAccountLogin($accountLogin)
    {
        $this->accountLogin = $accountLogin;
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
}