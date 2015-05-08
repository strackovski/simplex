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

/**
 * Content metadata class
 *
 * @Entity
 * @Table(name="metadata")
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class Metadata
{
    /**
     * Auto generated object identity
     *
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @Column(type="string", length=20, nullable=true)
     */
    protected $type;

    /**
     * Metadata
     *
     * @var array
     * @Column(type="json_array", name="data", nullable=true)
     */
    protected $data;

    /**
     * Constructor
     *
     * @param string|null $data Metadata
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set data
     *
     * @param array $data The metadata to set
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @param bool $decode
     *
     * @return mixed
     */
    public function getData($decode = false)
    {
        if ($decode) {
            if (!is_array($this->data)) {
                return json_decode($this->data, 1);
            }
        }

        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
