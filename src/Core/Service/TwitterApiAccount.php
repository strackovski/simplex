<?php

namespace nv\Simplex\Core\Service;

/**
 * Class Twitter API Account
 * @package nv\Simplex\Core\Service
 */
class TwitterApiAccount extends ApiAccountAbstract
{
    private $accountLogin;

    public function __construct($client_id, $client_secret, $redirect_uri = null, array $scopes = null)
    {
        parent::__construct($client_id, $client_secret, $redirect_uri, $scopes);
    }

    public function toArray()
    {
        $r = parent::toArray();
        $r['accountLogin'] = $this->accountLogin;

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


}