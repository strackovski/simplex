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

namespace nv\Simplex\Core\Form;

use nv\Simplex\Model\Entity\Form;
use nv\Simplex\Model\Entity\FormField;

/**
 * User Form Builder
 *
 * @package nv\Simplex\Core\Media
 * @author Vladimir Stračkovski <vlado@nv3.org>
 */
class FormBuilder
{
    /**
     * Form getter
     *
     * @param Form $form Form instance
     * @return array Array of HTML form fields
     */
    public function getForm(Form $form)
    {
        return $this->buildForm($form);
    }

    /**
     * Form builder
     *
     * @param Form $form Form instance
     * @return array Array of HTML form fields
     */
    protected function buildForm(Form $form)
    {
        $formResult = array();
        $r = null;
        $label = null;
        foreach ($form->getFields() as $field) {
            /** @var FormField $field */
            switch ($field->getType()) {
                case 'text':
                    $label = '<label for="form_'.$form->getName().'_'.$field->getName().'">'.$field->getName().'</label>';
                    $r = '<input type="text" class="form-control" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'" ' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'" ' : null;
                    $r .= 'id="form_'.$form->getName().'_'.$field->getName().'" ';
                    $field->getPlaceholder() ? $r .= 'placeholder="'.$field->getPlaceholder().'" ' : null;
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
                    $label  = '<label for="form_'.$form->getName().'_'.$field->getName().'">'.$field->getName().'</label>';
                    $r = '<textarea  class="form-control" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'" ' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'" ' : null;
                    $r .= 'id="form_'.$form->getName().'_'.$field->getName().'" ';
                    $field->getPlaceholder() ? $r .= 'placeholder="'.$field->getPlaceholder().'" ' : null;
                    $field->getAutoComplete() ? $r .= 'autocomplete="on" ' : $r .= 'autocomplete="off" ';
                    $field->getAutoFocus() ? $r .= 'autofocus  ' : null;
                    $field->getDisabled() ? $r .= 'disabled ' : null;
                    $field->getReadOnly() ? $r .= 'readonly ' : null;
                    $field->getRequired() ? $r .= 'required ' : null;
                    $field->getMaxLength() ? $r .= 'maxlength="'.$field->getMaxLength().'" ' : null;
                    $field->getSize() ? $r .= 'size="'.$field->getSize().'" ' : null;
                    $r .= '></textarea>';
                    break;

                case 'checkbox':
                    $label  = '<label for="form_'.$form->getName().'_'.$field->getName().'">'.$field->getName().'</label>';
                    $r = '<input type="checkbox"  class="form-control" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'"' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'"' : null;
                    $r .= 'id="form_'.$form->getName().'_'.$field->getName().'" ';
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getChecked() ? $r .= 'checked  ' : null;
                    $field->getDisabled() ? $r .= 'disabled ' : null;
                    $field->getRequired() ? $r .= 'required ' : null;
                    $r .= '/>';

                    break;

                case 'radio':
                    $label  = '<label for="form_'.$form->getName().'_'.$field->getName().'">'.$field->getName().'</label>';
                    $r = '<input type="radio" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'"' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'"' : null;
                    $r .= 'id="form_'.$form->getName().'_'.$field->getName().'" ';
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getChecked() ? $r .= 'checked ' : null;
                    $field->getDisabled() ? $r .= 'disabled ' : null;
                    $field->getRequired() ? $r .= 'required ' : null;
                    $r .= '/>';

                    break;

                case 'select':
                    $label  = '<label for="form_'.$form->getName().'_'.$field->getName().'">'.$field->getName().'</label>';
                    $r = '<select name="'.$field->getName().'" ';
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $r .= 'id="form_'.$form->getName().'_'.$field->getName().'" ';
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
                    $r = '<input type="submit"';
                    $r .= 'id="form_'.$form->getName().'_'.$field->getName().'" ';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'" ' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'" ' : null;
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getDisabled() ? $r .= 'disabled' : null;
                    $r .= '/>';

                    break;

                case 'reset':
                    $r = '<input type="reset"';
                    $field->getValue() ? $r .= 'value="'.$field->getValue().'" ' : null;
                    $field->getName() ? $r .= 'name="'.$field->getName().'" ' : null;
                    $r .= 'id="form_'.$form->getName().'_'.$field->getName().'" ';
                    $field->getAutoFocus() ? $r .= 'autofocus ' : null;
                    $field->getDisabled() ? $r .= 'disabled' : null;
                    $r .= '/>';

                    break;

                default:

                    break;
            }

            $formResult[strtolower($field->getName())]['control'] = $r;
            $formResult[strtolower($field->getName())]['label'] = $label;
        }

        // define form markup (form open, close)
        $f  = '<form ';
        $form->getAction() ? $f .= 'action="'.$form->getAction().'/'.$form->getId().'" ' : $f.= 'action="form/'.$form->getId().'" ';
        $form->getMethod() ? $f .= 'method="'.$form->getMethod().'" ' : $f .= 'method="POST" ';
        $form->getTarget() ? $f .= 'target="'.$form->getTarget().'" ' : null;
        $form->getName() ? $f .= 'name="'.$form->getName().'" ' : null;
        $form->getAcceptCharset() ? $f .= 'accept-charset="'.$form->getAcceptCharset().'" ' : null;
        $form->getEncType() ? $f .= 'enctype="'.$form->getEncType().'" ' : null;
        $f .= '>';

        $formResult['form_start'] = $f;
        $formResult['form_end'] = '</form>';

        return $formResult;
    }
}
