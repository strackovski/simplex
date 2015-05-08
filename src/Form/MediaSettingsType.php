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
 * Class MediaSettingsType
 *
 * Defines media settings form
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class MediaSettingsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('watermark', 'file', array(
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => array(
                    'class' => ''
                )
            ))
            ->add('imageResampleQuality', 'text', array(
                'required' => false,
                'attr' => array(
                    'class' => 'form-slider',
                    'placeholder' => '0-100'
                )
            ))
            ->add(
                'image_resize_small_width',
                'text',
                array(
                    'label' => 'Width',
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Width'
                    )
                )
            )
            ->add(
                'image_resize_small_height',
                'text',
                array(
                    'label' => 'Height',
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Height'
                    )
                )
            )
            ->add(
                'image_resize_medium_width',
                'text',
                array(
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Width',
                    )
                )
            )
            ->add(
                'image_resize_medium_height',
                'text',
                array(
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Height',
                    )
                )
            )
            ->add(
                'image_resize_large_width',
                'text',
                array(
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Width'
                    )
                )
            )
            ->add(
                'image_resize_large_height',
                'text',
                array(
                    'label' => false,
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Height'
                    )
                )
            )

            ->add(
                'image_crop_width',
                'text',
                array(
                    'label' => 'Width',
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Width'
                    )
                )
            )
            ->add(
                'image_crop_height',
                'text',
                array(
                    'label' => 'Height',
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Height'
                    )
                )
            )
            ->add(
                'image_auto_crop',
                'checkbox',
                array(
                    'required' => false,
                    'label' => 'Use auto-cropping (recommended)'
                )
            )
            ->add(
                'image_strip_meta',
                'checkbox',
                array(
                    'required' => false,
                    'label' => 'Remove metadata when processing image files'
                )
            )
            ->add(
                'image_keep_original',
                'checkbox',
                array(
                    'required' => false,
                    'label' => 'Keep original image files on upload (recommended)'
                )
            )
            ->add(
                'detect_faces_in_photos',
                'checkbox',
                array(
                    'required' => false,
                    'label' => 'Enable face detection for library media'
                )
            )
            ->add(
                'watermarkMedia',
                'checkbox',
                array(
                    'required' => false,
                    'label' => 'Apply watermark to library images'
                )
            )
            ->add(
                'watermark_position',
                'choice',
                array(
                    'empty_value' => 'Choose where to place the watermark',
                    'mapped' => true,
                    'required' => false,
                    'multiple' => false,
                    'choices' => array(
                        'tl' => 'Top-left',
                        'tr' => 'Top-right',
                        'bl' => 'Bottom-left',
                        'br' => 'Bottom-right',
                        'cn' => 'Center'
                    )
                )
            )
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
            'data_class' => 'nv\Simplex\Model\Entity\Settings',
        ));
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'media_settings';
    }
}
