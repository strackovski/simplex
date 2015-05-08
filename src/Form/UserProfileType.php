<?php

/*
 * This file is part of the Simplex project.
 *
 * 2015 NV3, Vladimir Stračkovski <vlado@nv3.org>
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nv\Simplex\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserProfileType
 *
 * Defines the User profile form
 *
 * @package nv\Simplex\Form\Type
 * @author Vladimir Stračkovski <vlado@nv3.org>
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
                'attr' => array(
                    'placeholder' => 'Your first'
                )
            ))
            ->add('last_name', 'text', array(
                'constraints' => new Assert\NotBlank(),
                'attr' => array(
                    'placeholder' => 'and last name'
                )
            ))
            ->add('description', 'textarea', array(
                'attr' => array(
                    'rows' => '8',
                    'placeholder' => 'Enter description (optional)'
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
