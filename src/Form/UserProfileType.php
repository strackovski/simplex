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
