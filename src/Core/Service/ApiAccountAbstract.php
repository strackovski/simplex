<?php

namespace nv\Simplex\Core\Service;

/**
 * Abstract Class API Account
 * @package nv\Simplex\Core\Service
 */
abstract class ApiAccountAbstract
{
    protected $enabled;

    protected $accountLogin;

    public function toArray()
    {
        $self = array(
            'isEnabled' => $this->enabled,
            'accountLogin' => $this->accountLogin
        );

        return $self;
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
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}