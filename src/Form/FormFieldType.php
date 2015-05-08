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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FormFieldType
 *
 * Defines the Form Field form
 *
 * @package nv\Simplex\Form
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormFieldType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'choices' => array(
                    'text' => 'Text',
                    'textarea' => 'Textarea',
                    'checkbox' => 'Checkbox',
                    'radio' => 'Radio button',
                    'select' => 'Select menu',
                    'submit' => 'Submit button',
                    'reset' => 'Reset button'
                ),
                'empty_value' => 'Select a field type...'
            ))
            ->add('autoComplete', 'checkbox', array(
                'label' => 'Auto Complete',
                'required' => false
            ))
            ->add('autoFocus', 'checkbox', array(
                'label' => 'Auto Focus',
                'required' => false
            ))
            ->add('checked', 'checkbox', array(
                'label' => 'Checked',
                'required' => false
            ))
            ->add('disabled', 'checkbox', array(
                'label' => 'Disabled',
                'required' => false
            ))
            ->add('required', 'checkbox', array(
                'label' => 'Required',
                'required' => false
            ))
            ->add('max', 'text', array(
                'required' => false,
                'label' => 'Maximum',
                'attr' => array(
                    'placeholder' => 'Max value'
                )
            ))
            ->add('min', 'text', array(
                'required' => false,
                'label' => 'Minimum',
                'attr' => array(
                    'placeholder' => 'Min value',
                 )
            ))
            ->add('maxlength', 'text', array(
                'required' => false,
                'label' => 'Max Length',
                'attr' => array(
                    'placeholder' => 'Max characters',
                )
            ))
            ->add('name', 'text', array(
                'required' => false,
                'label' => 'Name',
                'attr' => array(
                    'placeholder' => 'Field name'
                )
            ))
            ->add('placeholder', 'text', array(
                'required' => false,
                'label' => 'Placeholder',
                'attr' => array(
                    'placeholder' => 'Field description'
                )
            ))
            ->add('value', 'text', array(
                'required' => false,
                'label' => 'Value',
                'attr' => array(
                    'placeholder' => 'Field value'
                )
            ))
            ->add('size', 'text', array(
                'required' => false,
                'label' => 'Size',
                'attr' => array(
                    'placeholder' => 'Size (px)'
                )
            ))
            ->add('options', 'text', array(
                'required' => false,
                'label' => 'Options',
                'attr' => array(
                    'placeholder' => 'Field options'
                )
            ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'nv\Simplex\Model\Entity\FormField',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form_field';
    }
}
