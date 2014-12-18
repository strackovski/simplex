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
 * Class MailSettingsType
 *
 * Defines mail settings form
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class MailSettingsType extends AbstractType
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
            ->add('enableMailing', 'checkbox', array('required' => false, 'label' => 'Enable mailing'))
            ->add('mailTransport', 'choice', array(
                'empty_value' => 'Select a mail transport mode',
                'mapped' => true,
                'required' => false,
                'multiple' => false,
                'choices' => array(
                    'smtp' => 'SMTP',
                    'mail' => 'Mail',
                    'sendmail' => 'Sendmail'
                )
            ))
            ->add('mailHost', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Mail server domain or IP address'
                )
            ))
            ->add('mailPort', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Server port'
                )
            ))
            ->add('mailUsername', 'text', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Mail account username (or email)'
                )
            ))
            ->add('mailPassword', 'password', array(
                'required' => false,
                'attr' => array(
                    'placeholder' => 'Mail account password'
                )
            ))
            ->add('mailEncryption', 'choice', array(
                'empty_value' => 'Select email authentication encryption mode',
                'mapped' => true,
                'required' => false,
                'multiple' => false,
                'choices' => array(
                    'tls' => 'TLS',
                    'ssl' => 'SSL'
                )
            ))
            ->add('mailAuthMode', 'choice', array(
                'empty_value' => 'Select email authentication mode',
                'mapped' => true,
                'required' => false,
                'multiple' => false,
                'choices' => array(
                    'plain' => 'Plain',
                    'login' => 'Login',
                    'cram-md5' => 'Cram-MD5'
                )
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
            'data_class' => 'nv\Simplex\Model\Entity\Settings',
        ));
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'mail_settings';
    }
}
