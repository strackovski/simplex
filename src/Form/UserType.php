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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserType
 *
 * Defines User form
 *
 * @package nv\Simplex\Form\Type
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
            ))
            ->add('lastName', 'text', array(
                'constraints' => new Assert\NotBlank(),
            ))
            ->add('description', 'textarea', array(
                'attr' => array(
                    'rows' => 8
                )
            ))
            ->add('email', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                'required' => true
            ))
            ->add('save', 'submit')
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
