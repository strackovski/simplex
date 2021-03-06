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

namespace nv\Simplex\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FormType
 *
 * Defines the Form form
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'required' => true,
                'label' => 'Form title',
                'attr' => array(
                    'placeholder' => 'Form title/name'
                )
            ))
            ->add('acceptCharset', 'text', array(
                'required' => false,
                    'label' => 'Accepted Charset',
                'attr' => array(
                    'placeholder' => 'Accepted character set'
                )
            ))
            ->add('action', 'text', array(
                'required' => false,
                'label' => 'Action',
                'attr' => array(
                    'placeholder' => 'Action URL'
                )
            ))
            ->add('autoComplete', 'checkbox', array(
                'label' => 'Auto Complete',
                'required' => false
            ))
            ->add('encType', 'choice', array(
                'required' => false,
                'label' => 'Encoding Type',
                'choices' => array(
                    'application/x-www-form-urlencoded' => 'application/x-www-form-urlencoded',
                    'multipart/form-data' => 'multipart/form-data',
                    'text/plain' => 'text/plain'
                ),
                'empty_value' => 'Select data encoding type...'
            ))
            ->add('method', 'choice', array(
                'required' => false,
                'label' => 'Method',
                'choices' => array(
                    'POST' => 'HTTP POST',
                    'GET' => 'HTTP GET'
                ),
                'empty_value' => 'Select form\'s HTTP method...'
            ))
            ->add('name', 'text', array(
                'required' => false,
                'label' => 'Name',
                'attr' => array(
                    'placeholder' => 'Form name'
                )
            ))
            ->add('target', 'choice', array(
                'required' => false,
                'label' => 'Response target',
                'choices' => array(
                    '_blank' => 'New window',
                    '_self' => 'Current window',
                    '_parent' => 'Parent',
                    '_top' => 'Top'
                ),
                'empty_value' => 'Select where to display results...'
            ))
            ->add('noValidate', 'checkbox', array(
                'label' => 'Disable validation',
                'required' => false
            ))
            ->add('contentLabel', 'text', array(
                'required' => true,
                'label' => 'Content Label',
                'attr' => array(
                    'placeholder' => 'Content label'
                )
            ))
            ->add('published', 'checkbox', array(
                'required' => false,
                'label' => 'Publish this form',
            ))
            ->add('fields', 'collection', array(
                'type' => new FormFieldType(),
                'allow_add'    => true,
                'allow_delete' => true,
                'label' => false
            ))
            ->add('save', 'submit', array(
                'attr' => array(
                    'class' => 'btn-save'
                )
            ))
            ->add('cancel', 'button', array(
                'attr' => array(
                    'class' => 'btn-cmd cmd-cancel btn-cancel'
                )
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'nv\Simplex\Model\Entity\Form',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form';
    }
}
