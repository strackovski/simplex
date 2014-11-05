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
 * Class UserProfileType
 *
 * Defines the User profile form
 *
 * @package nv\Simplex\Form\Type
 */
class UserProfileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', 'text', array(
                'constraints' => new Assert\NotBlank(),
            ))
            ->add('last_name', 'text', array(
                'constraints' => new Assert\NotBlank(),
            ))
            ->add('description', 'textarea', array(
                'attr' => array('rows' => '8')
            ))
            ->add('save', 'submit')
            ->add('cancel', 'button', array(
                'attr' => array(
                    'class' => 'btn-cmd cmd-cancel btn-cancel'
                )
            ))
            ->add('avatarFile', 'file', array('required' => false, 'mapped' => false));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'edit_user_profile';
    }
}
