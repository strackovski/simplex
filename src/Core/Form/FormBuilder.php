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

namespace nv\Simplex\Core\Form;

use nv\Simplex\Model\Entity\Form;
use nv\Simplex\Model\Entity\FormField;

/**
 * User Form Builder
 *
 * @todo Use php-ffmpeg lib
 *
 * @package nv\Simplex\Core\Media
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormBuilder
{
    public function buildForm(Form $form)
    {
        $formResult = array();
        $r = null;
        foreach ($form->getFields() as $field) {
            /** @var FormField $field */
            switch ($field->getType()) {
                case 'text':
                    $r  = '<input type="text" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'" ' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'" ' : null;
                    $field->getAutoComplete() ? $r .= 'autocomplete="on" ' : $r .= 'autocomplete="off" ';
                    $field->getAutoFocus() ? $r .= 'autofocus  ' : null;
                    $field->getDisabled() ? $r .= 'disabled ' : null;
                    $field->getReadOnly() ? $r .= 'readonly ' : null;
                    $field->getRequired() ? $r .= 'required ' : null;
                    $field->getMaxLength() ? $r .= 'maxlength="'.$field->getMaxLength().'" ' : null;
                    $field->getSize() ? $r .= 'size="'.$field->getSize().'" ' : null;
                    $r .= '/>';
                    break;

                case 'textarea':

                    break;

                case 'checkbox':
                    $r  = '<input type="checkbox" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'"' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'"' : null;
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getChecked() ? $r .= 'checked  ' : null;
                    $field->getDisabled() ? $r .= 'disabled ' : null;
                    $field->getRequired() ? $r .= 'required ' : null;
                    $r .= '/>';

                    break;

                case 'radio':
                    $r  = '<input type="radio" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'"' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'"' : null;
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getChecked() ? $r .= 'checked ' : null;
                    $field->getDisabled() ? $r .= 'disabled ' : null;
                    $field->getRequired() ? $r .= 'required ' : null;
                    $r .= '/>';

                    break;

                case 'select':
                    $r  = '<select name="'.$field->getName().'" ';
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getDisabled() ? $r .= 'disabled ' : null;
                    $field->getRequired() ? $r .= 'required ' : null;
                    $field->getSize() ? $r .= 'size="'.$field->getSize().'"' : null;
                    $r .= '>';

                    $o = explode(',', $field->getOptions());



                    foreach ($o as $name) {
                        $r .= '<option value="'.$name.'">'.$name.'</option>';
                    }

                    $r .= '</select>';

                    break;

                case 'submit':
                    $r  = '<input type="submit"';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'"' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'"' : null;
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getDisabled() ? $r .= 'disabled' : null;
                    $r .= '/>';

                    break;

                case 'reset':
                    $r  = '<input type="reset"';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'"' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'"' : null;
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getDisabled() ? $r .= 'disabled' : null;
                    $r .= '/>';

                    break;

                default:

                    break;
            }

            $formResult[] = $r;
        }

        return implode("", $formResult);
    }
}