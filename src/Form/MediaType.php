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
 * Class MediaType
 *
 * Defines the Media type form
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class MediaType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('required' => false, 'attr' => array('placeholder' => 'Media title')))
            ->add('description', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'A short description of the media item'
                )
            ))
            ->add('contentLabel', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Content label'
                )
            ))
            ->add('mediaCategory', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Media category'
                )
            ))
            ->add('originalAuthor', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Original author'
                )
            ))
            ->add('published', 'checkbox', array(
                'required' => false,
                'label' => 'Publish this media item'
            ))
            ->add('license', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Applicable content license'
                )
            ))
            ->add('save', 'submit', array(
                'attr' => array(
                    'class' => 'btn-save pull-right'
                )
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'nv\Simplex\Model\Entity\MediaItem',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'media_item';
    }
}
