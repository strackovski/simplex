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
 * Class PageQueryType
 *
 * Defines the PageQuery form type
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageQueryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contentType', 'choice', array(
                'choices' => array(
                    'post' => 'Post',
                    'form' => 'Form',
                    'image' => 'Image',
                    'video' => 'Video'
                ),
                'empty_value' => 'Select type of content...',
            ))
            ->add('column', 'choice', array(
                'choices' => array(
                    'contentLabel' => 'Content label',
                    'title' => 'Title',
                    'author' => 'Author',
                    'tags' => 'Tags',
                    'created_at' => 'Date created',
                    'updated_at' => 'Date modified',
                    'exposed' => 'Exposed'
                ),
                'attr' => array(
                    'data-opt' => 'column'
                ),
                'empty_value' => 'Select filter column...'
            ))
            ->add('operator', 'choice', array(
                'choices' => array(
                    'eq' => 'Equals',
                    'in' => 'Contains',
                    'between' => 'Between',
                    'before' => 'Before',
                    'after' => 'After',
                ),
                'attr' => array(
                    'data-opt' => 'operator'
                ),
                'empty_value' => 'Select search operator...',
                'required' => false
            ))
            /*
            ->add('value', 'text', array(
                'attr' => array(
                    'placeholder' => 'Value',
                    'data-opt' => 'value'
                )
            ))
            */
            ->add('value', 'collection', array(
                'type'   => 'text',
                'allow_add' => true,
                'prototype' => true,
                'options'  => array(
                    'required'  => false,
                    'label' => 'Value',
                    'attr'      => array(
                        'data-opt' => 'value'
                    )
                ),
                'attr' => array(
                    'class' => 'value-box'
                )
            ))
            ->add('sortColumn', 'choice', array(
                'choices' => array(
                    'contentLabel' => 'Content label',
                    'title' => 'Title',
                    'author' => 'Author',
                    'created_at' => 'Date created',
                    'updated_at' => 'Date modified',
                    'exposed' => 'Exposed'
                ),
            ))
            ->add('sortOrder', 'choice', array(
                'choices' => array('ASC' => 'Ascending', 'DESC' => 'Descending'),
            ))
            ->add('limitMin', 'text', array(
                'label' => 'Min limit',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Min results',
                    'data-opt' => 'minlimit'
                ),
            ))
            ->add('limitMax', 'text', array(
                'label' => 'Max limit',
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Result count limit (number)',
                    'data-opt' => 'maxlimit'
                ),
            ))
            ->add('outputVariable', 'text', array(
                'label' => 'Output variable name',
                'attr' => array(
                    'placeholder' => 'Leave empty to merge'
                ),
                'required' => false
            ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'nv\Simplex\Model\Entity\PageQuery',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'page_query';
    }
}
