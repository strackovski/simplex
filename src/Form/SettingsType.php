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
use nv\Simplex\Model\Repository\SettingsRepository;

/**
 * Class SettingsType
 *
 * Defines the Settings class as a form type
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class SettingsType extends AbstractType
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
            ->add('siteName', 'text', array(
                'constraints' => new Assert\NotBlank(),
            ))
            ->add('siteDescription', 'textarea', array('required' => false))
            ->add('siteOwner', 'text', array(
                'label' => 'Site owner',
                'attr' => array(
                    'placeholder' => 'Name of person or organization'
                )
            ))

            ->add('siteLogo', 'file', array(
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => array(
                    'class' => ''
                )
            ))
            ->add('keywords', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Site keywords'
                )
            ))
            ->add('language', 'language')
            ->add('adminEmail', 'email', array(
                'label' => 'Administrative email address'
            ))
            ->add('enableQueryStringAccess', 'checkbox', array('required' => false))
            ->add('enableAnnotations', 'checkbox', array('required' => false))
            ->add('allowPublicUserRegistration', 'checkbox', array(
                'required' => false,
                'mapped' => true
            ))
            ->add('live', 'checkbox', array('required' => false))
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
