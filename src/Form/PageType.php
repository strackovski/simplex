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

use Doctrine\ORM\EntityManager;
use nv\Simplex\Model\Entity\Settings;
use nv\Simplex\Model\Repository\PageRepository;
use nv\Simplex\Model\Repository\TagRepository;
use nv\Simplex\Model\Repository\UserRepository;
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
            $files[basename($file, '.html.twig')] = ucfirst(basename($file, '.html.twig'));
        }

        $authors = $this->pages->getAuthors();
        $authorItems = array();
        foreach ($authors as $author) {
            $authorItems[$author->getId()] = $author->displayName();
        }

        $tags = $this->pages->getTags();
        $tagItems = array();
        foreach ($tags as $tag) {
            $tagItems[$tag->getId()] = $tag->getName();
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
                    'placeholder' => 'Enter description (optional)'
                )
            ))
            ->add('slug', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array(
                    'placeholder' => 'Enter slug for this page'
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
                    'label' => false
                )
            )
            ->add('authors', 'choice', array(
                'choices' => $authorItems,
                'empty_value' => 'Select authors...',
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'required' => false
            ))
            ->add('tags', 'choice', array(
                'choices' => $tagItems,
                'empty_value' => 'Select tags...',
                'mapped' => false,
                'multiple' => true,
                'expanded' => true,
                'required' => false
            ))
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
