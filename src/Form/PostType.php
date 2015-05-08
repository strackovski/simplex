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

use nv\Simplex\Model\Repository\PageRepository;
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

    /** @var PageRepository  */
    private $pages;

    public function __construct(array $media, PageRepository $pages = null)
    {
        $this->pages = $pages;
        $this->media = $media;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mediaList = array();
        $pageList = array();

        foreach ($this->media as $item) {
            $mediaList[$item->getId()] = $item->getMediaId();
        }

        foreach ($this->pages->findAll() as $page) {
            $pageList[$page->getId()] = $page->getTitle();
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
            ->add('positionWeight', 'text', array(
                'mapped' => true,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Position weight'
                )
            ))
            ->add('contentLabel', 'text', array(
                'mapped' => true,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Content label'
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
                'label' => 'Publish',
                'required' => false,
                'attr' => array(
                'class' => ''
                )
            ))
            ->add('channels', 'choice', array(
                'mapped' => true,
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => array(
                    'twitter' => 'Twitter',
                    'facebook' => 'Facebook'
                )
            ))
            ->add('published_from', 'datetime', array(
                'label' => 'Publish from',
                'mapped' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'attr' => array(
                    'class' => 'datepicker',
                    'placeholder' => 'Publish start date'
                )
            ))
            ->add('published_to', 'datetime', array(
                'label' => 'Publish until',
                'mapped' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'attr' => array(
                    'class' => 'datepicker',
                    'placeholder' => 'Publish end date'
                )
            ))
            ->add('exposed_from', 'datetime', array(
                'label' => 'Expose from',
                'widget' => 'single_text',
                'mapped' => false,
                'format' => 'yyyy-MM-dd',
                'required' => false,
                'attr' => array(
                    'class' => 'datepicker',
                    'placeholder' => 'Expose start date',
                )
            ))
            ->add('exposed_to', 'datetime', array(
                'label' => 'Expose until',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'class' => 'datepicker',
                    'placeholder' => 'Expose end date',
                )
            ))
            ->add('exposed', 'checkbox', array(
                'label' => 'Expose',
                'required' => false
            ))
            ->add('allow_ratings', 'checkbox', array(
                'label' => 'Allow ratings',
                'required' => false
            ))
            ->add('allow_comments', 'checkbox', array(
                'label' => 'Allow comments',
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
            ->add('pages', 'choice', array(
                'mapped' => false,
                'label' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => $pageList
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
