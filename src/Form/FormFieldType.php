<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 NV3, Vladimir Stračkovski <vlado@nv3.org>
 * All rights reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FormFieldType
 *
 * Defines the Form form
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormFieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'choices' => array(
                    'text' => 'Text',
                    'textarea' => 'Textarea',
                    'checkbox' => 'Checkbox',
                    'radio' => 'Radio button',
                    'select' => 'Select menu',
                    'submit' => 'Submit button',
                    'reset' => 'Reset button'
                ),
                'empty_value' => 'Select a field type...'
            ))
            ->add('autoComplete', 'checkbox', array(
                'label' => 'autocomplete',
                'required' => false
            ))
            ->add('autoFocus', 'checkbox', array(
                'label' => 'autofocus',
                'required' => false
            ))
            ->add('checked', 'checkbox', array(
                'label' => 'checked',
                'required' => false
            ))
            ->add('disabled', 'checkbox', array(
                'label' => 'disabled',
                'required' => false
            ))
            ->add('required', 'checkbox', array(
                'label' => 'required',
                'required' => false
            ))
            ->add('max', 'text', array(
                'required' => false
            ))
            ->add('min', 'text', array(
                'required' => false
            ))
            ->add('maxlength', 'text', array(
                'required' => false
            ))
            ->add('name', 'text', array(
                'required' => false
            ))
            ->add('placeholder', 'text', array(
                'required' => false
            ))
            ->add('value', 'text', array(
                'required' => false
            ))
            ->add('size', 'text', array(
                'required' => false
            ))
            ->add('options', 'text', array(
                'required' => false
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'nv\Simplex\Model\Entity\FormField',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_field';
    }
}
