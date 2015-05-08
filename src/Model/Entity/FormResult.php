<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Model\Entity;

use nv\Simplex\Common\TimestampableAbstract;

/**
 * Form result class
 *
 * Defines a form result
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\FormRepository")
 * @Table(name="form_results")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormResult extends TimestampableAbstract
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * The Form instance this result belongs to
     *
     * @ManyToOne(targetEntity="Form", inversedBy="results")
     * @JoinColumn(name="form_id", referencedColumnName="id")
     **/
    protected $form;

    /**
     * Collected form data
     *
     * @Column(type="json_array", name="form_data")
     */
    protected $formData;

    /**
     * IP address of client that posted the form
     *
     * @Column(type="string", name="client_ip", nullable=true)
     */
    protected $clientIpAddress;

    /**
     * Constructor
     *
     * @param array $formData
     * @param null $clientIp
     */
    public function __construct(array $formData = null, $clientIp = null)
    {
        $this->formData = $formData;
        $this->clientIpAddress = $clientIp;
    }

    /**
     * @return mixed
     */
    public function getClientIpAddress()
    {
        return $this->clientIpAddress;
    }

    /**
     * @param mixed $clientIpAddress
     */
    public function setClientIpAddress($clientIpAddress)
    {
        $this->clientIpAddress = $clientIpAddress;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return mixed
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @param mixed $formData
     */
    public function setFormData($formData)
    {
        $this->formData = $formData;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
