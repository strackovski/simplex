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

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserType
 *
 * Defines User form
 *
 * @package nv\Simplex\Form\Type
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'required' => true,
                'attr' => array(
                    'placeholder' => 'First name'
                )
            ))
            ->add('lastName', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array(
                    'placeholder' => 'Last name'
                )
            ))
            ->add('description', 'textarea', array(
                'attr' => array(
                    'rows' => 8,
                    'placeholder' => 'Enter description (optional)'
                )
            ))
            ->add('email', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Enter email address'
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
            ))
            ->add('avatarFile', 'file', array('required' => false, 'mapped' => false))
            ->add('roles', 'choice', array(
                'mapped' => false,
                'required' => true,
                'empty_value' => 'Select a role...',
                'choices' => array(
                    'ROLE_ADMIN' => 'Administrators',
                    'ROLE_EDITOR' => 'Editors'
                )
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'add_user';
    }
}
