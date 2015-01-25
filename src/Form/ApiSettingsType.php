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
use nv\Simplex\Model\Repository\SettingsRepository;

/**
 * Class SettingsType
 *
 * Defines the Settings class as a form type
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class ApiSettingsType extends AbstractType
{
    /** @var \nv\Simplex\Model\Repository\SettingsRepository */
    private $settings;

    /**
     * @param \nv\Simplex\Model\Repository\SettingsRepository $repository
     */
    public function __construct(SettingsRepository $repository)
    {
        $this->settings = $repository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enableGoogleApi', 'checkbox', array('required' => false, 'mapped' => false))
            ->add('appName', 'text', array('required' => false, 'mapped' => false))
            ->add('clientId', 'text', array('required' => false, 'mapped' => false))
            ->add('clientSecret', 'text', array('required' => false, 'mapped' => false))
            ->add('redirectUri', 'text', array('required' => false, 'mapped' => false))
            ->add('apiKey', 'text', array('required' => false, 'mapped' => false))
            ->add('accountLogin', 'text', array('required' => false, 'mapped' => false))

            ->add('enableTwitterApi', 'checkbox', array('required' => false, 'mapped' => false))
            ->add('twitter_ConsumerKey', 'text', array('required' => false, 'mapped' => false))
            ->add('twitter_ConsumerSecret', 'text', array('required' => false, 'mapped' => false))
            ->add('twitter_OauthCallback', 'text', array('required' => false, 'mapped' => false))
            ->add('twitter_AccountLogin', 'text', array('required' => false, 'mapped' => false))

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
        return 'settings';
    }
}