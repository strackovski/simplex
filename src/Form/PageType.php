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

use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\PageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PageType
 *
 * Defines the Page form
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class PageType extends AbstractType
{
    /** @var PageRepository  */
    private $pages;

    /** @var Settings */
    private $settings;

    public function __construct(
        PageRepository $pageRepository,
        Settings $settings
    ) {
        $this->pages = $pageRepository;
        $this->settings = $settings;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $files = array();
        $masters = array();
        $tplDir = __DIR__ . '/../../web/templates/site/';

        foreach (glob($tplDir . $this->settings->getPublicTheme().'/masters/*.twig') as $file) {
            $masters[basename($file, '.html.twig')] = ucfirst(basename($file, '.html.twig'));
        }

        foreach (glob($tplDir .$this->settings->getPublicTheme().'/views/*.twig') as $file) {
            if (basename($file) !== 'post.html.twig') {
                $files[basename($file, '.html.twig')] = ucfirst(basename($file, '.html.twig'));
            }
        }
        
        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array(
                    'placeholder' => 'Page title'
                )
            ))
            ->add('description', 'textarea', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'A short description of the page (optional)'
                )
            ))
            ->add('keywords', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'A list of keywords, separated by comma (optional)'
                )
            ))
            ->add('slug', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array(
                    'placeholder' => 'URL slug'
                )
            ))
            ->add('typeColor', 'choice', array(
                'choices' => array(
                    'red' => 'Red',
                    'blue' => 'Blue',
                    'turquoise' => 'Turquoise',
                    'green' => 'Green',
                    'orange' => 'Orange',
                    'pink' => 'Pink'
                ),
                'empty_value' => 'Select a color...',
                'multiple' => false,
                'required' => false
            ))
            ->add('contentLabel', 'text', array(
                'mapped' => true,
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Content label'
                )
            ))
            ->add('master', 'choice', array(
                'choices' => $masters,
                'required' => false,
                'empty_value' => 'Select a master template...'
            ))
            ->add('view', 'choice', array(
                'choices' => $files,
                'required' => false,
                'empty_value' => 'Select a view template...'
            ))
            ->add(
                'queries',
                'collection',
                array(
                    'mapped' => true,
                    'type' => new PageQueryType(),
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'label' => false,
                    'by_reference' => false
                )
            )
            ->add('in_menu', 'checkbox', array(
                'required' => false,
                'label' => 'Include a link to this page in main menu'
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
            'data_class' => 'nv\Simplex\Model\Entity\Page',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'page';
    }
}
