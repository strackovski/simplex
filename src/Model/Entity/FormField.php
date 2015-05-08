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
 * Form Field class
 *
 * Defines a form field
 *
 * @Entity(repositoryClass="nv\Simplex\Model\Repository\FormRepository")
 * @Table(name="form_fields")
 * @HasLifecycleCallbacks
 *
 * @package nv\Simplex\Model\Entity
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormField
{
    /**
     * Auto generated object identity
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $alt;

    /**
     * @Column(type="boolean", name="auto_complete", nullable=true)
     */
    protected $autoComplete;

    /**
     * @Column(type="boolean", name="auto_focus", nullable=true)
     */
    protected $autoFocus;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $checked;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $disabled;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $max;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $maxLength;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $min;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $multiple;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $placeholder;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $readOnly;

    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $required;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $size;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $src;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $value;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $type;

    /**
     * Field options
     *
     * @var array
     * @Column(type="json_array", name="options", nullable=true)
     */
    protected $options;

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param mixed $alt
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
    }

    /**
     * @return mixed
     */
    public function getAutoComplete()
    {
        return $this->autoComplete;
    }

    /**
     * @param mixed $autoComplete
     */
    public function setAutoComplete($autoComplete)
    {
        $this->autoComplete = $autoComplete;
    }

    /**
     * @return mixed
     */
    public function getAutoFocus()
    {
        return $this->autoFocus;
    }

    /**
     * @param mixed $autoFocus
     */
    public function setAutoFocus($autoFocus)
    {
        $this->autoFocus = $autoFocus;
    }

    /**
     * @return mixed
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param mixed $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * @return mixed
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param mixed $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
    }

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param mixed $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @return mixed
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param mixed $multiple
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param mixed $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return mixed
     */
    public function getReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @param mixed $readOnly
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = $readOnly;
    }

    /**
     * @return mixed
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param mixed $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * @param mixed $src
     */
    public function setSrc($src)
    {
        $this->src = $src;
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

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @return mixed
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param mixed $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
