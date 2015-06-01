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
 * Abstract Class API Connector
 * @package nv\Simplex\Core\Connector
 */
abstract class ApiConnectorAbstract
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