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
 * Class PostType
 *
 * Defines the Post form
 *
 * @package nv\Simplex\Form\Type
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PostType extends AbstractType
{
    /** @var array Library media items */
    private $media;

    public function __construct(array $media)
    {
        $this->media = $media;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mediaList = array();

        foreach ($this->media as $item) {
            $mediaList[$item->getId()] = $item->getMediaId();
        }

        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array(
                    'class' => '',
                    'placeholder' => 'Click to enter a title'
                )
            ))
            ->add('tags', 'text', array(
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Comma separated list of tags'
                )
            ))
            ->add('keywords', 'text', array(
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Comma separated list of keywords'
                )
            ))
            ->add('subtitle', 'textarea', array(
                'attr' => array(
                    'rows' => '3',
                    'required' => false,
                    'placeholder' => 'Click to enter a subtitle'
                )
            ))
            ->add('published', 'checkbox', array(
                'label' => 'Publish this post',
                'required' => false,
                'attr' => array(
                'class' => ''
                )
            ))
            /*->add('published_interval', 'datetime', array(
                'label' => 'Publish during this interval',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'attr' => array(
                    'class' => ''
                )
            ))
            ->add('exposed_interval', 'datetime', array(
                'label' => 'Expose during this interval',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'attr' => array(
                    'class' => 'flat-input'
                )
            ))*/
            ->add('exposed', 'checkbox', array(
                'label' => 'Expose this post',
                'required' => false
            ))
            ->add('allow_ratings', 'checkbox', array(
                'label' => 'Allow users to rate this post',
                'required' => false
            ))
            ->add('allow_comments', 'checkbox', array(
                'label' => 'Allow users to comment on this post',
                'required' => false
            ))
            ->add('body', 'textarea', array(
                'label' => false,
                'attr' => array(
                    'class' => 'textbox-simple rte',
                    'rows' => '10',
                    'placeholder' => 'Put some body in the post'
                )
            ))
            ->add('media', 'choice', array(
                'mapped' => false,
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $mediaList
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
            'data_class' => 'nv\Simplex\Model\Entity\Post',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'post';
    }
}
