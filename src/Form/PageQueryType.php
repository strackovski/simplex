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
                'choices' => array('post' => 'Post', 'image' => 'Image', 'video' => 'Video'),
                'data' => 'post',
                'empty_value' => 'Select type of content to retrieve...',
            ))
            ->add('column', 'choice', array(
                'choices' => array(
                    'contentLabel' => 'Content label',
                    'title' => 'Title',
                    'created_at' => 'Date created',
                    'updated_at' => 'Date modified',
                    'author' => 'Author',
                    'tags' => 'Tags',
                    'inLibrary' => 'In library',
                ),
                'empty_value' => 'Select a column to filter by...'
            ))
            ->add('operator', 'choice', array(
                'choices' => array(
                    'eq' => 'Equals',
                    'in' => 'Contains',
                    'between' => 'Between',
                    'before' => 'Before',
                    'after' => 'After'
                ),
                'empty_value' => 'Select search operator...',
                'required' => false
            ))
            ->add('value', 'text', array(
                'attr' => array(
                    'placeholder' => 'Value'
                )
            ))
            ->add('sortBy', 'choice', array(
                'choices' => array('asc' => 'Ascending', 'desc' => 'Descending'),
            ))
            ->add('limitMax', 'text', array(
                'required' => false
            ))
            ->add('outputVariable', 'text', array(
                'attr' => array(
                    'placeholder' => 'Output variable name (empty to merge)'
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
