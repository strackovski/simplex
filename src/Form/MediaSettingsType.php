<?php

/*
 * This file is part of the Simplex project.
 *
 * Copyright (c) 2014 Vladimir Stračkovski <vlado@nv3.org>
 * The MIT License <http://choosealicense.com/licenses/mit/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit the link above.
 */

namespace nv\Simplex\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use nv\Simplex\Model\Repository\SettingsRepository;

/**
 * Class MediaSettingsType
 *
 * Defines media settings form
 *
 * @package nv\Simplex\Form
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
                    'class' => 'form-slider'
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
                    'label' => 'Width',
                    'required' => false,
                    'mapped' => false,
                    'attr' => array(
                        'placeholder' => 'Width'
                    )
                )
            )
            ->add(
                'image_resize_medium_height',
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
                'image_resize_large_width',
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
                'image_resize_large_height',
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
