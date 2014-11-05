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
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $files = array();
        $masters = array();
        $settings = $this->em->getRepository('nv\Simplex\Model\Entity\Settings')->getCurrent();

        foreach (glob(__DIR__ . '/../../web/templates/site/'.$settings->getPublicTheme().'/masters/*.twig') as $file) {
            $masters[basename($file, '.html.twig')] = ucfirst(basename($file, '.html.twig'));
        }

        foreach (glob(__DIR__ . '/../../web/templates/site/'.$settings->getPublicTheme().'/views/*.twig') as $file) {
            $files[basename($file, '.html.twig')] = ucfirst(basename($file, '.html.twig'));
        }

        $authors = $this->em->getRepository('nv\Simplex\Model\Entity\User')->getUsers();
        $authorItems = array();
        foreach ($authors as $author) {
            $authorItems[$author->getId()] = $author->displayName();
        }

        $tags = $this->em->getRepository('nv\Simplex\Model\Entity\Tag')->findAll();
        $tagItems = array();
        foreach ($tags as $tag) {
            $tagItems[$tag->getId()] = $tag->getName();
        }

        $builder
            ->add('title', 'text', array(
                'constraints' => new Assert\NotBlank(),
            ))
            ->add('description', 'textarea', array(
                'required' => false
            ))
            ->add('slug', 'text', array(
                'constraints' => new Assert\NotBlank(),
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
