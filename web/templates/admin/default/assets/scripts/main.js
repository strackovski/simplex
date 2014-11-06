/* ===================================================
 * nv\Simplex application user scripts
 * http://github.com/strackovski/simplex
 * Copyright 2014 Vladimir Stračkovski <vlado@nv3.org>
 *
 * Main Javascript UI
 * =================================================== */

/*jslint browser:true*/
/*global $, jQuery*/
/*global console*/

// Determine root url
function appURL()
{
    if (document.URL.indexOf('http://') > -1) {
        var tempURL = document.URL.substring(7);
        if (document.URL.indexOf('http://www.') > -1) {
            tempURL = document.URL.substring(12);
        }
        return tempURL.substring(0, tempURL.indexOf('/'));
    }
}

// Array comparison
Array.prototype.equals = function (array) {
    // if the other array is a falsy value, return
    if (!array) {
        return false;
    }

    // compare lengths - can save a lot of time
    if (this.length != array.length) {
        return false;
    }
    var i, l;
    for (i = 0, l = this.length; i < l; i++) {
        // Check if we have nested arrays
        if (this[i] instanceof Array && array[i] instanceof Array) {
            // recurse into the nested arrays
            if (!this[i].equals(array[i])) {
                return false;
            }
        } else if (this[i] != array[i]) {
            // Warning - two different object instances will never be equal: {x:20} != {x:20}
            return false;
        }
    }
    return true;
};

var baseURL = 'http://192.168.64.13/simplexNejaFix/web/index.php/';
var rootURL = 'http://192.168.64.13';

Dropzone.autoDiscover = false;

function initRichEditor(height) {
    if (!height) {
        height = 300;
    }

    tinymce.init({
        selector: "textarea.rte",
        theme: 'modern',
        skin: 'light',
        height: height,
        menubar: false,
        statusbar: false,
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright | mybutton",
        //content_css: rootURL + 'web/assets/build/styles.css',
        setup : function (ed) {
            ed.addButton('mybutton', {
                text: '',
                title: 'Expand editor',
                icon: 'fullscreen',
                onclick: function () {
                    // ed.insertContent('Main button');
                    $('.cmd-edit-modal').trigger('click');
                }
            });

            ed.on('keyUp', function () {
                $('textarea.rte').text(tinyMCE.activeEditor.getContent({format: 'raw'}));
            });

            ed.on('click', function () {
                $('textarea.rte').text(tinyMCE.activeEditor.getContent({format: 'raw'}));
            });
        }
    });
}

function iterateCheckbox() {
    $.each($('.media-selection'), function () {
        if ($(this).find('input[type="checkbox"]').prop('checked') == true) {
            if (!($(this).find('img').hasClass('isSelected'))) {
                $(this).find('img').addClass('isSelected');
            }
            $(this).find('.state').slideDown(200);
        }
    });
}

function userAction() {


    $('.user-actions > a').off('click').on('click', function () {
        var $this = $(this);
        if (!($(this).hasClass('active'))) {
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            if ($(this).attr('href') == '#credentials') {
                $.ajax({
                    beforeSend: function () {
                        $('.tab-pane#credentials').html('');
                        var el = $('<i class="fa fa-spinner fa-spin pull-right"></i>');
                        $this.append(el);
                    },
                    url: baseURL + 'admin/user/credentials'
                })
                    .done(function (html) {
                        $this.find('i.fa').fadeOut().remove();
                        $('.tab-pane#credentials').html(html);


                        userAction();
                    })
                    .fail(function () {
                        var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                        $('body').append(flash);
                        $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                            $('.flash-error').remove();
                        });
                    });
            }
        }
    });
}

function accordionAction() {
    var checkbox_changed,
        radio_changed,
        input_changed,
        file_changed,
        select_changed,
        textarea_text,
        select_choice;
    var cachedForms;
    var formAction = $('.form-action');
    $(".accordion").accordion({
        collapsible: true,
        animate: false,
        active: false,
        heightStyle: 'content',
        header: '.accordion-header',
        beforeActivate: function (event, ui) {
            if ($(event.toElement).hasClass('ui-accordion-header-active') || $(event.toElement).closest('.ui-accordion-header').hasClass('ui-accordion-header-active')) {
                return false;
            }

            if (!($('.file-hider').length)) {
                fileInputAction();
            }

            //$('#settings-form')[0].reset();
            $('.select-file').text('Choose file');

            checkbox_changed = 0;
            radio_changed = 0;
            input_changed = 0;
            file_changed = 0;
            select_changed = 0;

            // hide Save, Cancel button form, and move it to an appropriate location
            formAction.hide().remove();
            $(ui.newPanel).find('.ab-content').append(formAction);
            formAction.show();
            formAction.find('.btn-save').addClass('disabled');
            $('.accordion').find('.accordion-header > span').show();
            $(ui.newHeader).find('span').hide();
            var text;
            var currentText = $(ui.newHeader).find('span:nth-child(3)').html();

            cachedForms = $(event.toElement).closest('.ui-accordion-header').next('.accordion-body').find('.ab-content > .form-group');

            if ($(ui.newPanel).find('textarea').length) {
                textarea_text = $(ui.newPanel).find('textarea').val();
            }
            if ($(ui.newPanel).find('select').length) {
                select_choice = $(ui.newPanel).find('select').val();
            }

            var checkStates = [];
            var newCheckStates = [];

            var checkbox_inputs = $(ui.newPanel).find('.form-group > .checkbox').find('input[type="checkbox"]');
            if (checkbox_inputs.length) {
                checkbox_inputs.each(function () {
                    if ($(this).is(':checked')) {
                        checkStates.push('checked');
                        $(this).next('.checksquare').addClass('checksquare-selected');
                    } else {
                        checkStates.push(0);
                        $(this).next('.checksquare').removeClass('checksquare-selected');
                    }
                });
            }


            $('.form-group > .checkbox').find('input[type="checkbox"]').off('change').on('change', function () {
                newCheckStates.length = 0;
                checkbox_inputs.each(function () {
                    if ($(this).is(':checked')) {
                        newCheckStates.push('checked');
                    } else {
                        newCheckStates.push('0');
                    }
                });
                if (!checkStates.equals(newCheckStates)) {
                    checkbox_changed = 1;
                } else {
                    checkbox_changed = 0;
                }
                handleSaveButton();
            });

            $(':file').on('change', function () {
                if ($(this).val() != '') {
                    file_changed = 1;

                } else {
                    file_changed = 0;
                }
                handleSaveButton();
            });

            var states = [];

            var settings_radios = $(ui.newPanel).find('div[id*="settings_"]').find('.radio input[type="radio"]');
            if (settings_radios.length) {
                settings_radios.each(function () {
                    if ($(this).is(':checked')) {
                        states.push('checked');
                        $(this).next('.radiobox').addClass('radiobox-selected');
                    } else {
                        states.push('0');
                        $(this).next('.radiobox').removeClass('radiobox-selected');

                    }
                });
            }

            var newStates = [];
            $('div[id*="settings_"] input[type="radio"]').off('change').on('change', function () {
                newStates.length = 0;

                settings_radios.each(function () {
                    if ($(this).is(':checked')) {
                        newStates.push('checked');
                    } else {
                        newStates.push('0');
                    }
                });

                if (!states.equals(newStates)) {
                    radio_changed = 1;
                } else {
                    radio_changed = 0;
                }
                handleSaveButton();
            });

            $(ui.newPanel).find('.form-control').off('input').on('input', function () {
                var $this = $(this);
                if ($this[0].nodeName.toLowerCase() === 'select') {
                    if ($this.val() != select_choice) {
                        select_changed = 1;
                    } else {
                        select_changed = 0;
                    }
                } else {
                    text = $(this).val();
                    var formText = $(this).attr('value');
                    if (text != formText) {
                        input_changed = 1;
                    } else {
                        input_changed = 0;
                    }
                }
                handleSaveButton();
            });

            formAction.find('.btn-cancel').off('click').on('click', function () {
                $('.tab-pane form')[0].reset();
                $(this).closest('.accordion-body').prev('.accordion-header').trigger('click');

            });
            var options = {
                target:        '',   // target element(s) to be updated with server response
                beforeSubmit:  function (formData, jqForm, options) {
                    formAction.append('<i class="fa fa-spinner fa-spin"></i>');
                    if ($('#mail_settings_mailPassword').is(':visible')) {
                        text = '';
                    } else {
                        text = $('.ui-accordion-header-active').next('.accordion-body').find('.ab-content .form-control').val();
                    }

                    if (currentText == text && states == newStates && checkStates == newCheckStates) {
                        formAction.find('i.fa').fadeOut().remove();
                        return false;
                    }
                },
                success: function (responseText, statusText, xhr, $form) {
                    // loader spinner remove?
                    // update header span if status success
                    // revert form field value if status not success
                    formAction.find('i.fa').fadeOut().remove();
                    var uiActive = $('.ui-accordion-header-active');
                    if (statusText == 'success') {
                        $('.tab-pane.active').html(responseText);
                        uiActive.find('span:nth-child(3)').html(text);
                        $.each(uiActive.next('.accordion-body').find('input[type="text"]'), function () {
                            $(this).attr('value', $(this).val());
                        });

                        if (uiActive.find('label').text() == 'Enable mailing') {
                            if (uiActive.next('.accordion-body').find('input[type="checkbox"]').is(':checked')) {
                                uiActive.find('span:nth-child(3)').html('<i class="fa fa-check"></i>');
                            } else {
                                uiActive.find('span:nth-child(3)').html('<i class="fa fa-times"></i>');
                            }
                        }
                        uiActive.find('a').html('Changes saved');
                        uiActive.addClass('changesSaved');
                        accordionAction();
                        hideSubmitControls();
                        if($('#media_settings_imageResampleQuality').length) {
                            resampleAction();
                        }

                    } else {
                        uiActive.find('span:nth-child(3)').html(text);
                        uiActive.find('a').html('Change failed');
                        uiActive.addClass('changesFailed');

                    }
                    /*   if ($('#settings .accordion').length) {
                     $('#settings .accordion').accordion('option', 'active', false);
                     }
                     if ($('#themes .accordion').length) {
                     $('#themes .accordion').accordion('option', 'active', false);
                     }
                     if ($('#media .accordion').length) {
                     $('#media .accordion').accordion('option', 'active', false);
                     }
                     if ($('#mailing .accordion').length) {
                     $('#mailing .accordion').accordion('option', 'active', false);
                     }
                     */
                    console.log('response: ' + responseText);
                    console.log('status: ' + statusText);
                },

                // other available options:
                //url:       url         // override for form's 'action' attribute
                //type:      type        // 'get' or 'post', override for form's 'method' attribute
                //dataType:  null        // 'xml', 'script', or 'json' (expected server response type)
                clearForm: false,        // clear all form fields after successful submit
                resetForm: false       // reset the form after successful submit

                // $.ajax options can be used here too, for example:
                //timeout:   3000
            };

            // bind form using 'ajaxForm'
            $('.tab-pane form').ajaxForm(options);
        }
    });

    function handleSaveButton() {
        var inputsChange = 0;
        if (cachedForms.find('textarea').length) {
            if (cachedForms.find('textarea').val() != textarea_text) {
                inputsChange += 1;
            }
        }
        $.each(cachedForms.find('input[type="text"]'), function (index, objectValue) {
            if ($(this).val() != $(this).attr('value')) {
                inputsChange += 1;
            }
        });
        if ($('#mail_settings_mailPassword').is(':visible')) {
            if ($('#mail_settings_mailPassword').val() != $('#mail_settings_mailPassword').attr('value')) {
                inputsChange += 1;
            }
        }

        if (checkbox_changed || radio_changed || inputsChange || file_changed || select_changed || input_changed) {
            formAction.find('.btn-save').removeClass('disabled');
        } else {
            formAction.find('.btn-save').addClass('disabled');
        }
    }
}

function querySelection() {
    var contentTypeField, columnField, operatorField, valueField, sortByField, limitMinField, limitMaxField;

    var existingForm;
    // if ($('#page_queries_0').length) {
    if ($('.editQuery-form').length) {
        $('.add-pq-form').attr("data-proto", '');
        //existingForm = $('.addQuery-form');
        $('.new-query-form').remove();
        //$('.edit-form').wrap('<div class="row"></div>').removeClass('edit-form').addClass('edit-form-group').find('label.control-label').first().hide();
        contentTypeField = $('#page_queries_0_contentType');
        columnField = $('#page_queries_0_column');
        operatorField = $('#page_queries_0_operator');
        valueField = $('#page_queries_0_value');
        sortByField = $('#page_queries_0_sortBy');
        limitMinField = $('#page_queries_0_limitMin');
        limitMaxField = $('#page_queries_0_limitMax');
    } else {
        var forma = $('.add-pq-form').attr("data-proto");
        $('.add-pq-form').attr("data-proto", '');
        $('.tab-pane#data').prepend(forma);
        contentTypeField = $('#page_queries___name___contentType');
        columnField = $('#page_queries___name___column');
        operatorField = $('#page_queries___name___operator');
        valueField = $('#page_queries___name___value');
        sortByField = $('#page_queries___name___sortBy');
        limitMinField = $('#page_queries___name___limitMin');
        limitMaxField = $('#page_queries___name___limitMax');
    }

    var authors = $('#page_authors').closest('.form-group');

    // move tags and authors in the correct field
    $('#page_authors').closest('.form-group').remove();
    authors.prependTo(valueField.closest('.row'));

    var tags = $('#page_tags').closest('.form-group');
    $('#page_tags').closest('.form-group').remove();
    tags.prependTo(valueField.closest('.row'));

    // hide authors & tags form
    $('#page_authors').closest('.form-group').addClass('hidden');
    $('#page_tags').closest('.form-group').addClass('hidden');

    var column = columnField.val();
    var value = valueField.val();

    // arrays to be inserted into valueField field
    var tagsCheckboxes = [];
    var authorsCheckboxes = [];
    var dates = [];

    // hide operator options, unhide them later, based on columnField selection
    operatorField.find('option').addClass('hidden');

    // if contentType is not defined, a new page is being created - hide all fields
    if (!contentTypeField.val()) {
        //contentTypeField.closest('.form-group').siblings().addClass('hidden');
        contentTypeField.closest('.row').siblings().find('.data-group').addClass('hidden');
        contentTypeField.closest('.row').next().find('.data-group').removeClass('hidden');
        //contentTypeField.closest('.data-group').next().next().removeClass('hidden');
    } else {
        if (column == 'title') {
            // show 'equals' and 'contains' options in operandField
            operatorField.find('option[value="eq"]').removeClass('hidden');
            operatorField.find('option[value="in"]').removeClass('hidden');

        } else if (column == 'created_at' || column == 'updated_at') {

            // hide valueField field
            columnField.closest('.row').next().find('.col-xs-8').addClass('hidden');

            // show 'after', 'before' and 'between' options in operandField
            operatorField.find('option[value="before"]').removeClass('hidden');
            operatorField.find('option[value="after"]').removeClass('hidden');
            operatorField.find('option[value="between"]').removeClass('hidden');

            // conjure up date fields, if they have not been created yet, apply datepicker() on them
            if (!($('.dateField').length)) {
                var valueFieldClone2 = valueField.closest('.data-group').clone().addClass('dateField toDateField col-xs-4').removeClass('col-xs-8');
                $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone2.find('.field'));
                valueFieldClone2.find('.field').addClass('prepend-icon');
                // change label, id, remove 'required' attr
                valueFieldClone2.find('label').text('And Date:').attr('for', 'date_2');
                valueFieldClone2.find('input').attr({id: 'date_2', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
                    onClose: function (dateText) {
                        // call a function that collects dateField values and inserts them into valueField field
                        dateChange();
                    }
                });
                valueFieldClone2.insertAfter(valueField.closest('.data-group'));

                // change label, id, remove 'required' attr, unhide forms
                var valueFieldClone1 = valueField.closest('.data-group').clone().addClass('dateField fromDateField col-xs-4').removeClass('hidden col-xs-8');
                $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone1.find('.field'));
                valueFieldClone1.find('.field').addClass('prepend-icon');
                valueFieldClone1.find('label').text('Date:').attr('for', 'date_1');
                valueFieldClone1.find('input').attr({id: 'date_1', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
                    onClose: function (dateText) {
                        // call a function that collects dateField values and inserts them into valueField field
                        dateChange();
                    }
                });
                valueFieldClone1.insertAfter(valueField.closest('.data-group'));
            }

            // collect comma separated values from the valueField and insert them into correct dateFields and unhide them
            var datesArray = value.split(',');
            if (operatorField.val() == 'before' || operatorField.val() == 'after') {
                $('.fromDateField').removeClass('hidden col-xs-4').addClass('col-xs-8').find('input#date_1').val(datesArray[0]);
            } else if (operatorField.val() == 'between') {
                $('.fromDateField').removeClass('hidden col-xs-8').addClass('col-xs-4').find('input#date_1').val(datesArray[0]);
                $('.toDateField').removeClass('hidden').find('input#date_2').val(datesArray[1]);
            }
        } else if (column == 'author') {
            authorsAction('edit');
        } else if (column == 'tags') {
            tagsAction('edit');
        }

    }


    // Input changes

    contentTypeField.on('change', function () {
        // hide all sibling .form-groups
        $(this).closest('.row').siblings().find('.data-group').addClass('hidden');

        // hide tags and authors // NEW
        $('#page_tags').closest('.form-group').addClass('hidden');
        $('#page_authors').closest('.form-group').addClass('hidden');

        // unhide columnField
        $(this).closest('.row').next().find('.data-group').removeClass('hidden');

        // set columnField to 'Select a column ...'
        columnField.find('option:eq(0)').prop('selected', true);
        valueField.val('');

    });

    columnField.on('change', function () {

        // hide all subsequent .form-groups
        $(this).closest('.row').nextAll().find('.data-group').addClass('hidden');

        // hide tags and authors
        $('#page_tags').closest('.form-group').addClass('hidden');
        $('#page_authors').closest('.form-group').addClass('hidden');

        // reset valueField and dateFields
        valueField.val('');
        tagsCheckboxes.length = 0;
        authorsCheckboxes.length = 0;
        dates.length = 0;
        $('.datepickerField').val('');

        // hide operatorField's options (unhide them later)
        operatorField.find('option').addClass('hidden');

        var columnCurrent = $(this).val();
        if (columnCurrent == 'title') {

            // unhide operatorField, show 'equals' and 'contains' options
            operatorField.closest('.data-group').removeClass('hidden');
            operatorField.find('option[value="eq"]').removeClass('hidden').prop('selected', true);
            operatorField.find('option[value="in"]').removeClass('hidden');

            // show valueField
            valueField.closest('.data-group').removeClass('hidden');

        } else if (columnCurrent == 'updated_at' || columnCurrent == 'created_at') {

            // show operatorField, show 'after', 'before' and 'between' options, make 'between' the selected one
            operatorField.closest('.data-group').removeClass('hidden');
            operatorField.find('option[value="before"]').removeClass('hidden');
            operatorField.find('option[value="after"]').removeClass('hidden');
            operatorField.find('option[value="between"]').removeClass('hidden').prop('selected', true);

            // conjure up date fields, if they have not been created yet, apply datepicker() on them
            if (!($('.dateField').length)) {
                var valueFieldClone2 = valueField.closest('.data-group').clone().addClass('dateField toDateField').removeClass('hidden col-xs-8').addClass('col-xs-4');
                $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone2.find('.field'));
                valueFieldClone2.find('.field').addClass('prepend-icon');
                valueFieldClone2.find('label').text('And Date:').attr('for', 'date_2');
                valueFieldClone2.find('input').attr({id: 'date_2', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
                    onClose: function (dateText) {
                        dateChange();
                    }
                });
                valueFieldClone2.insertAfter(valueField.closest('.data-group'));

                var valueFieldClone1 = valueField.closest('.data-group').clone().addClass('dateField fromDateField').removeClass('hidden col-xs-8').addClass('col-xs-4');
                $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone1.find('.field'));
                valueFieldClone1.find('.field').addClass('prepend-icon');
                valueFieldClone1.find('label').text('Date:').attr('for', 'date_1');
                valueFieldClone1.find('input').attr({id: 'date_1', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
                    onClose: function (dateText) {
                        dateChange();
                    }
                });
                valueFieldClone1.insertAfter(valueField.closest('.data-group'));
            } else { // just unhide dateFields
                $('.toDateField').removeClass('hidden col-xs-8').addClass('col-xs-4');
                $('.fromDateField').removeClass('hidden col-xs-8').addClass('col-xs-4');
            }

        } else if (columnCurrent == 'author') {
            authorsAction();

        } else if (columnCurrent == 'tags') {
            tagsAction();
        }
        limitMinField.closest('.data-group').removeClass('hidden');
        limitMaxField.closest('.data-group').removeClass('hidden');
        sortByField.closest('.data-group').removeClass('hidden');

    });

    operatorField.on('change', function () {
        dates.length = 0;
        var columnCurrent;
        if ($('#page_queries___name___column').length) {
            columnCurrent = $('#page_queries___name___column').val();
        } else {
            columnCurrent = $('#page_queries_0_column').val();
        }

        var currentOperator = $(this).val();
        if (columnCurrent == 'updated_at' || columnCurrent == 'created_at') {
            $('.dateField').addClass('hidden').find('input').val('');
            if (currentOperator == 'between') {
                valueField.val('');
                $('.dateField').removeClass('hidden col-xs-8').addClass('col-xs-4');
            } else if (currentOperator == 'before' || currentOperator == 'after') {
                $('.fromDateField').removeClass('hidden').removeClass('col-xs-4').addClass('col-xs-8');
                valueField.val('');
            }
        }
    });

    function dateChange() {
        dates.length = 0;
        var operatorCurrent;
        if ($('#page_queries___name___operator').length) {
            operatorCurrent = $('#page_queries___name___operator').val();
        } else {
            operatorCurrent = $('#page_queries_0_operator').val();
        }

        if (operatorCurrent == 'before' || operatorCurrent == 'after') {
            dates.push($('#date_1').val());
        } else if (operatorCurrent == 'between') {
            if (!($('#date_2').val())) {
                dates.push($('#date_1').val());
            } else if (!($('#date_1').val())) {
                dates.push($('#date_2').val());
            } else {
                dates.push($('#date_1').val());
                dates.push($('#date_2').val());
            }
        }
        var joinedString = dates.join(',');
        valueField.val(joinedString);
    }

    $('#page_tags > .checkbox').find('input[type="checkbox"]').off('change').on('change', function () {
        var joinedString;
        var $this = $(this);
        var $thisValue = $this.val();

        if ($this.is(':checked')) {
            // add to array
            if ($.inArray($thisValue, tagsCheckboxes)  == -1) {
                tagsCheckboxes.push($thisValue);
                joinedString = tagsCheckboxes.join(',');
                valueField.val(joinedString);
            }
        } else {
            // remove from array
            tagsCheckboxes = jQuery.grep(tagsCheckboxes, function (value) {
                return value != $thisValue;
            });

            joinedString = tagsCheckboxes.join(',');
            valueField.val(joinedString);
        }
    });

    $('#page_authors > .checkbox').find('input[type="checkbox"]').off('change').on('change', function () {
        var joinedString;
        var $this = $(this);
        var $thisValue = $this.val();

        if ($this.is(':checked')) {
            // add to array
            if ($.inArray($thisValue, authorsCheckboxes)  == -1) {
                authorsCheckboxes.push($thisValue);
                joinedString = authorsCheckboxes.join(',');
                valueField.val(joinedString);
            }
        } else {
            // remove from array
            authorsCheckboxes = jQuery.grep(authorsCheckboxes, function (value) {
                return value != $thisValue;
            });

            joinedString = authorsCheckboxes.join(',');
            valueField.val(joinedString);
        }
    });

    function authorsAction(edit) {
        // get author checkboxes on the page
        var pageAuthors = $('#page_authors > .checkbox').find('input[type="checkbox"]');

        // get author values from the valueField
        var authorsArray = [];
        if (edit) {
            if (value != '') {
                authorsArray = value.split(',');
            }
        }
        authorsCheckboxes = authorsArray;

        // trigger checkbox click if ids match - click triggers $(...).on('change'), handled later
        $.each(authorsArray, function (index, item) {
            $.each(pageAuthors, function () {
                if (item == $(this).val()) {
                    $(this).trigger('click');
                }
            });
        });

        // show form, hide operatorField and valueField
        $('#page_authors').closest('.form-group').removeClass('hidden');
        operatorField.closest('.data-group').addClass('hidden').closest('.row').next().find('.data-group').addClass('hidden');
    }

    function tagsAction(edit) {
        var pageTags = $('#page_tags > .checkbox').find('input[type="checkbox"]');

        // get tag values from the valueField
        var tagsArray = [];
        if (edit) {
            if (value != '') {
                tagsArray = value.split(',');
            }
        }

        tagsCheckboxes = tagsArray;

        $.each(tagsArray, function (index, item) {
            $.each(pageTags, function () {
                if (item == $(this).val()) {
                    $(this).trigger('click');
                }
            });
        });

        // show form, hide operatorField and valueField
        $('#page_tags').closest('.form-group').removeClass('hidden');
        operatorField.closest('.data-group').addClass('hidden').closest('.row').next().find('.data-group').addClass('hidden');
    }

}

function descriptionOverlay() {
    var thumbnail = $('.thumbnail');
    thumbnail.mouseenter(function () {
        $(this).find('.description').fadeIn(200);
    })
        .mouseleave(function () {
            $(this).find('.description').fadeOut(100);
        });

    $('.bottomright').on('click', function (e) {
        e.preventDefault();

        $(this).find('i.fa').toggleClass('fa-square-o fa-check-square-o');
        $(this).parents('.thumbnail').find('img').toggleClass('isSelected');
        if ($('.isSelected').length) {
            if (!$('.cmd-multiselect').find('i.fa').hasClass('fa-check-square-o')) {
                $('.cmd-multiselect').find('i.fa').toggleClass('fa-check-square-o fa-square-o');
            }
        } else {
            if ($('.cmd-multiselect').find('i.fa').hasClass('fa-check-square-o')) {
                $('.cmd-multiselect').find('i.fa').toggleClass('fa-check-square-o fa-square-o');
            }

        }
        $(this).parents('.thumbnail').find('input').trigger('click');
        if ($(this).find('i.fa').hasClass('fa-check-square-o')) {
            //$(this).parents('.thumbnail').find('input').prop('checked', true);
            $(this).parents('.thumbnail').find('.state').slideDown(200);
        } else {
            $(this).parents('.thumbnail').find('input[type="checkbox"]').prop('checked', false);
            $(this).parents('.thumbnail').find('.state').slideUp(200);
        }
    });

    // For Post -> Media (where you click directly on image to change input[checkbox] value)
    $('.media-selection').on('click', function () {
        var img = $(this).find('img');
        img.toggleClass('isSelected');
        if (img.hasClass('isSelected')) {
            $(this).find('input').prop('checked', true);
            $(this).find('.state').slideDown(200);
        } else {
            $(this).find('input[type="checkbox"]').prop('checked', false);
            $(this).find('.state').slideUp(200);
        }
    });
}

function bindButtons() {
    // Checkbox handler
    $('input[name="delete_items[]"]').off('click').on('click', function () {
        var state = [];
        $('input[name="delete_items[]"]').each(function (e) {
            if ($(this).prop('checked')) {
                state.push($(this));
            }
        });
        if ($(this).prop('checked')) {
            $('.cmd-remove-multiple').css('display', 'block');
        } else {
            if (state.length < 1) {
                $('.cmd-remove-multiple').css('display', 'none');
            }
        }
    });

    $('.alert-exposed .btn-success').off('click').on('click', function (e) {
        e.preventDefault();
        $('.cmd-view-help').trigger('click');
    });
    // Handle command links
    $('.btn-cmd').off('click').on('click', function (e) {
        e.preventDefault();
        var modal = $('#site-modal-sm');
        // var modal_md = $('#site-modal-md');
        var modal_medium = $('#site-modal-md');
        var modal_md = $('#myModal');
        var href = $(this).attr('href');
        var clicked = $(this);
        var removable = $(this).closest('.removable');

        // Helper functions
        function resetModal(_modal, cmd) {
            // view modal
            _modal.find('.modal-title').html('');
            _modal.find('.modal-body p').html('');

            if (cmd == 'view-modal') {
                _modal.find('.btn-confirm').show();
            } else {
                _modal.find('.btn-confirm').off('click');
                _modal.find('.btn-confirm')
                    .removeClass('btn-danger')
                    .addClass('btn-primary')
                    .html('');
            }
            /*else if (cmd == 'edit-modal') {

             }*/
        }

        function setRemoveModal(_modal) {
            _modal.find('.modal-title').html('Confirm delete');
            _modal.find('.modal-body p').html('Are you sure....');
            _modal.find('.btn-confirm')
                .removeClass('btn-primary')
                .addClass('btn-danger')
                .html('Delete');
        }

        // Collapse
        if ($(this).hasClass('cmd-view-collapse')) {
            // @todo toggle, change icon, change command class
            var collection = $('div.collection');
            var viewBtn = $(this);

            if (collection.hasClass('list-collapsed')) {
                collection.find('.panel-body').slideDown(100);
                collection.removeClass('list-collapsed');
                viewBtn.find('i').removeClass('fa-expand').addClass('fa-compress');
            } else {
                collection.find('.panel-body').slideUp(100);
                collection.addClass('list-collapsed');
                viewBtn.find('i').removeClass('fa-compress').addClass('fa-expand');
            }
        }

        // Modal editor
        if ($(this).hasClass('cmd-edit-modal')) {
            e.preventDefault();
            var modalE = $('#modal-editor');

            modalE.off('show.bs.modal').on('show.bs.modal', function (e) {
                var ta = $('textarea.rte');
                var tb = $('input#post_title');
                $('.mce-container').remove();
                $('input#post_title').remove();
                modalE.find('.modal-body').html(ta.show());
                modalE.find('span.modal-title').html(tb);
            });

            modalE.off('shown.bs.modal').on('shown.bs.modal', function (e) {
                initRichEditor(modalE.find('.modal-content').outerHeight() - modalE.find('.modal-header').outerHeight() - 50);
            });

            modalE.on('hide.bs.modal', function (e) {
                $('.mce-container').remove();
                var ta = $('textarea.rte');
                var tb = $('input#post_title');

                $('.form-group.title').html(tb);
                $('.form-group.body').html(ta.show());
                resetModal(modalE, 'edit-modal');
            });

            modalE.on('hidden.bs.modal', function (e) {
                initRichEditor();

            });

            modalE.modal();
        }

        // Cancel
        if ($(this).hasClass('cmd-cancel')) {
            // @todo Confirm cancel if item modified?
            window.history.back();
        }

        // Get view
        if ($(this).hasClass('cmd-populate-parent')) {
            var targetEl = $('.xhr-container');

            $.ajax({
                beforeSend: function () {
                    var el = '<div class="loader"><div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>';
                    targetEl.css('opacity', 0.6);
                    targetEl.prepend(el);
                    $('.loader').fadeIn();
                },
                url: href
            })
                .done(function (data) {
                    targetEl.removeAttr('style');
                    targetEl.html(data);
                })
                .fail(function (xhr, msg) {
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });
        }

        // Toggle state
        if ($(this).hasClass('cmd-toggle')) {
            clicked.css('color', 'red');
            $.ajax({
                url: href
            })
                .done(function (data) {
                })
                .fail(function (xhr, msg) {
                });
        }

        // Help
        if ($(this).hasClass('cmd-view-help')) {
            var help = $('.alert-help');
            if (help.hasClass('shown')) {
                help.slideUp(100);
                help.removeClass('shown');
                $('.affix-collection').removeAttr('style');
                $('.affix-collection').css('padding', '85px 20px 20px 20px');
            } else {
                help.slideDown(100);
                $('.affix-collection').removeAttr('style');
                $('.affix-collection').css('padding', '20px');
                help.addClass('shown');
            }
        }

        // Filter
        if ($(this).hasClass('cmd-filters')) {
            targetEl = $('.xhr-container');
            $.ajax({
                url: href
            })
                .done(function (data) {
                    targetEl.html(data);
                })
                .fail(function (xhr, msg) {
                });
        }

        // View modal
        if ($(this).hasClass('cmd-view-modal')) {
            currentPhoto = $(this).closest('.tb-col');

            $.ajax({
                url: href
            })
                .done(function (data) {
                    //var item = JSON.parse(data);
                    modal_md.off('show.bs.modal').on('show.bs.modal', function (e) {
                        modal_md.html(data);
                        modal_md.find('.btn-confirm').hide();
                    });
                    modal_md.on('hidden.bs.modal', function (e) {
                        resetModal(modal_md, 'view-modal');
                    });
                    modal_md.modal();
                })
                .fail(function (xhr, msg) {
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });
        }

        // Upload theme
        if ($(this).hasClass('cmd-upload-theme')) {
            $.ajax({
                url: $(this).attr('href'),
                beforeSend: function () {
                    modal_medium.find('.modal-body').html('loading');
                    modal_medium.modal();
                }
            })
                .done(function (data) {
                    // modal_medium.find('.modal-body').html(data);
                    modal_medium.find('.modal-content').html(data);
                    $("#theme-dropzone-site").dropzone({
                        init: function () {
                            this.on('error', function(file, errorMessage) {
                                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong. ' + errorMessage + '</div>';
                                $('body').append(flash);
                                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                                    $('.flash-error').remove();
                                });
                            })
                        },
                        previewsContainer: '#theme-dropzone-site .dz-result',
                        previewTemplate: '<div class="dz-preview dz-file-preview site-theme-preview">' +
                        '<div class="dz-details"><div class="dz-filename"><span data-dz-name></span><span class="label label-info pull-right">Uploaded</span></div></div>' +
                        '</div>',
                        clickable: '#theme-dropzone-site .dz-result',
                        parallelUploads: 3,
                        autoProcessQueue: true,
                        addRemoveLinks: false,
                        createImageThumbnails: false,
                        acceptedFiles: '.zip'
                    });

                    $("#theme-dropzone-admin").dropzone({
                        //url: $href,
                        init: function () {
                            this.on('error', function(file, errorMessage) {
                                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong. ' + errorMessage + '</div>';
                                $('body').append(flash);
                                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                                    $('.flash-error').remove();
                                });
                            })
                        },
                        previewsContainer: '#theme-dropzone-admin .dz-result',
                        previewTemplate: '<div class="dz-preview dz-file-preview admin-theme-preview">' +
                        '<div class="dz-details"><div class="dz-filename"><span data-dz-name></span><span class="label label-info pull-right">Uploaded</span></div></div>' +
                        '</div>',
                        clickable: '#theme-dropzone-admin .dz-result',
                        parallelUploads: 3,
                        autoProcessQueue: true,
                        addRemoveLinks: false,
                        createImageThumbnails: false,
                        acceptedFiles: '.zip'
                    });
                });
        }

        // Resample media
        if ($(this).hasClass('cmd-resample-media')) {
            $.ajax({
                url: $(this).attr('href'),
                beforeSend: function () {
                    modal_medium.find('.modal-body').html('loading');
                }
            })
                .done(function (data) {
                    modal_medium.find('.modal-content').html(data);
                    modal_medium.modal();
                });
        }

        // Remove item
        if ($(this).hasClass('cmd-remove')) {
            e.preventDefault();
            modal.off('show.bs.modal').on('show.bs.modal', function (e) {
                setRemoveModal(modal);
            });
            modal.off('shown.bs.modal').on('shown.bs.modal', function (e) {
                modal.find('.btn-confirm').off('click').on('click', function(e) {
                    $.ajax({
                        url: href
                    })
                        .done(function (data) {
                            removable.remove();
                            modal.modal('hide');
                            if ($('.dz-message').length) {
                                $('.tab-pane.active').html(data)

                                if ($('.dropzone').hasClass('vd-dropzone')) {
                                    // $('.vd-dropzone').html(data);
                                    handleDropzone('video');
                                } else {
                                    // $('.im-dropzone').html(data);
                                    handleDropzone('image');
                                }

                                $('.btn-group.selected-menu .btn').css('display', 'none');
                                $('.cmd-multiselect').find('i.fa').removeClass('fa-check-square-o').addClass('fa-square-o');

                                descriptionOverlay();
                                bindButtons();


                            } else if ($('.collection').length) {
                                if (!($('.panel-default').length)) {
                                    $.ajax({
                                        beforeSend: function () {

                                        },
                                        url: baseURL + "admin/posts"
                                    })
                                        .done(function (data) {
                                            $('.main-content').html(data);
                                        })
                                        .fail(function (xhr, msg) {
                                        });
                                }
                            }
                        })
                        .fail(function (xhr, msg) {
                            modal.modal('hide');
                            var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                            $('body').append(flash);
                            $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                                $('.flash-error').remove();
                            });
                        });
                });
            });

            modal.on('hidden.bs.modal', function (e) {
                resetModal(modal, 'remove');
            });
            modal.modal();
        }

        // Remove multiple items
        if ($(this).hasClass('cmd-remove-multiple')) {
            e.preventDefault();
            modal.on('show.bs.modal', function (e) {
                setRemoveModal(modal);
            });
            modal.off('shown.bs.modal').on('shown.bs.modal', function (e) {
                modal.find('.btn-confirm').on('click', function (e) {

                    var arrayOfIds = [];
                    var jsonArrayOfIds = [];
                    arrayOfIds.length = 0;
                    jsonArrayOfIds.length = 0;
                    $('input[name="delete_items[]"]:checked').each(function () {
                        arrayOfIds.push($(this).attr('value'));
                    });

                    jsonArrayOfIds = JSON.stringify(arrayOfIds);

                    $.ajax({
                        url: href + '?id=' + jsonArrayOfIds + '&multi=true',
                        beforeSend: function () {

                        }
                    })
                        .done(function (data) {
                            $('.tab-pane.active').html(data)

                            if ($('.dropzone').hasClass('vd-dropzone')) {
                                // $('.vd-dropzone').html(data);
                                handleDropzone('video');
                            } else {
                                // $('.im-dropzone').html(data);
                                handleDropzone('image');
                            }

                            descriptionOverlay();
                            bindButtons();
                        })
                        .fail(function (xhr, msg) {
                            // @todo Show error in modal OR flash message !
                            var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                            $('body').append(flash);
                            $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                                $('.flash-error').remove();
                            });
                        });

                    $('.isSelected').closest('.removable').remove();
                    modal.modal('hide');
                    $('.btn-group.selected-menu .btn').css('display', 'none');
                    $('.cmd-multiselect').find('i.fa').removeClass('fa-check-square-o').addClass('fa-square-o');
                });
            });
            modal.on('hidden.bs.modal', function (e) {
                resetModal(modal, 'remove-multiple');
            });
            modal.modal();
        }

    });
}

function handleDropzone(param) {
    $('.file-overflow').remove();
    $('.file-hider').remove();
    var name, type, urlType, allowedFiles;
    if (param == 'image') {
        name = 'im-';
        type = 'media';
        urlType = 'images';
        allowedFiles = 'image/*';
    } else {
        name = 'vd-';
        type = 'video';
        urlType = 'videos';
        allowedFiles = '.avi,.mov,.mp4';
    }

    var theDropzone = new Dropzone('div.' + name + 'dropzone', {
        url: baseURL + 'admin/' + type + '/upload',
        clickable: '.' + name + 'dropzone-clickable',
        autoProcessQueue: true,
        thumbnailWidth: 800,
        thumbnailHeight: null,
        previewTemplate: '<div class="dz-preview dz-file-preview tb-col removable">' +

        '</div>',
        previewsContainer: '.tiles',
        createImageThumbnails: false,
        acceptedFiles: allowedFiles
    });

    theDropzone.on("addedfile", function (file) {
        $('.progress').removeClass('hidden');
    });

    theDropzone.on("queuecomplete", function (file) {

        $.ajax({
            beforeSend: function () {
                // $('.main-content .tab-pane.active').animate({opacity: 0});
            },
            url: baseURL + 'admin/media/' + urlType
        })
            .done(function (data) {
                $('.progress').fadeOut(function () {
                    $(this).find('.progress-bar').attr('style', 'width: 4%');
                });
                $('.main-content .tab-pane.active').html(data);

                console.log('dropzone data:');

                setup();
                descriptionOverlay();
                bindButtons();
                // mediaLayout();
            })
            .fail(function () {
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });

    });

    theDropzone.on("error", function (file, msg) {
        var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
        $('body').append(flash);
        $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
            $('.flash-error').remove();
        });

    });

    theDropzone.on("totaluploadprogress", function (progress, totalBytes, bytesSent) {
        $('.progress .progress-bar').attr('style', 'width: ' + progress + '%');

    });
}

function scrollable() {
    console.log('scrollable called');
    if ($('.viewable-meta').length) {
        var winHeight = $(window).height();
        $('.fixo').height(winHeight - 65);
    }

    if ($('.scrollable').length > 0) {
        $('.content-inner-container').width($('.viewable-content').width());
        var $scrollable = $('.viewable-meta .inner-container'),
            $scrollbar = $('.scrollbar'),
            H = $scrollable.outerHeight(),
            sH = $scrollable[0].scrollHeight,
            sbH = H * H / sH;

        $('.scrollbar').height(sbH).draggable({
            axis : 'y',
            containment : 'parent',
            drag: function (e, ui) {
                $scrollable.scrollTop(sH / H * ui.position.top);
            },
            addClasses: false
        });
        $scrollable.on("scroll", function () {
            $scrollbar.css({top: $scrollable.scrollTop() / H * sbH });
        });


        var $contentScrollable = $('.viewable-content .content-inner-container'),
            $contentScrollbar = $('.content-scrollbar'),
            cH = $contentScrollable.outerHeight(),
            csH = $contentScrollable[0].scrollHeight,
            csbH = (cH * cH / csH);

        $('.content-scrollbar').height(csbH).draggable({
            axis: 'y',
            containment: 'parent',
            drag: function (e, ui) {
                $contentScrollable.scrollTop(csH / cH * ui.position.top);
            },
            addClasses: false
        });

        $contentScrollable.on('scroll', function () {
            $contentScrollbar.css({top: $contentScrollable.scrollTop() / cH * csbH});
        });

        if ($('.content-scrollbar').height() == $('.viewable-content').height()) {
            $('.content-scrollbar').hide();
        } else {
            $('.content-scrollbar').show();
        }

        if ($('.viewable-meta .scrollbar').height() == $('.viewable-meta').height()) {
            $('.viewable-meta .scrollbar').hide();
        } else {
            $('.viewable-meta .scrollbar').show();
        }
    }
}
var currentPhoto;

function mediaModalAction() {
    $('#myModal').off('shown.bs.modal').on('shown.bs.modal', function () {
        $('.modal-content').find('img').on('load', function () {
            centerModal();
            $(this).animate({opacity: 1});
        });
    });

    $('.modal').keydown(function (e) {
        if (e.keyCode == 37) {
            $('.modal').find('.modal-prev').trigger('click');
        } else if (e.keyCode == 39) {
            $('.modal .modal-next').trigger('click');
        }

    });

    $('body').off('click', '.media-modal .modal-next').on('click', '.media-modal .modal-next', function () {
        currentPhoto = currentPhoto.next();
        var href = currentPhoto.find('.cmd-view-modal').attr('href');

        if (href == undefined || href == '') {
            currentPhoto = $('.tiles .tb-col').first();
            href = currentPhoto.find('.cmd-view-modal').attr('href');
        }
        var modal_md = $('#myModal');

        $.ajax({
            url: href

        })
            .done(function (data) {
                //var item = JSON.parse(data);
                $('.modal-content').css('margin-top', '0px');
                modal_md.html(data);
                modal_md.find('.btn-confirm').hide();
                $('.modal-content').find('img').on('load', function () {
                    centerModal();
                    $(this).animate({opacity: 1});
                });
            })
            .fail(function (xhr, msg) {
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });
    });

    $('body').off('click', '.media-modal .modal-prev').on('click', '.media-modal .modal-prev', function () {
        currentPhoto = currentPhoto.prev();
        var href = currentPhoto.find('.cmd-view-modal').attr('href');

        if (href == undefined || href == '') {
            currentPhoto = $('.tiles .tb-col').last();
            href = currentPhoto.find('.cmd-view-modal').attr('href');
        }
        var modal_md = $('#myModal');

        $.ajax({
            url: href

        })
            .done(function (data) {
                //var item = JSON.parse(data);
                $('.modal-content').css('margin-top', '0px');

                modal_md.html(data);
                modal_md.find('.btn-confirm').hide();
                $('.modal-content').find('img').on('load', function () {
                    centerModal();
                    $(this).animate({opacity: 1});
                });
            })
            .fail(function (xhr, msg) {
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });

    });
}
function centerModal() {
    var modalContentHeight = $('.media-modal .modal-content').height() + 50;
    var windowHeight = $(window).height();
    var m = (windowHeight - modalContentHeight) / 2;
    $('.modal-content').css('margin-top', m);
}

function resampleAction() {
    /// Slider - Media Sampling
    var slideVal = $('input#media_settings_imageResampleQuality').attr('value');
    var j;
    for (j = 1; j <= 10; j++) {
        $('form .slider-wrapper').find('.ticks').append('<span class="label">' + j * 10 + '</span>');
    }

    if ($('#imageResampleQuality-slider').length) {
        $('#imageResampleQuality-slider').slider({
            min: 10,
            max: 100,
            step: 5,
            animate: true,
            value: slideVal,
            change: function (event, ui) {
                $('input#media_settings_imageResampleQuality').val(ui.value).trigger('input');
            },
            slide: function (event, ui) {
                $('input#media_settings_imageResampleQuality').val(ui.value).trigger('input');

            }
        });
    }

    $('input#media_settings_imageResampleQuality').off('keyup').on('keyup', function (e) {
        var $th = $(this);
        $th.val($th.val().replace(/[^0-9]/g, function (str) { return ''; }));

    });
    $('input#media_settings_imageResampleQuality').off('change').on('change', function () {
        $("#imageResampleQuality-slider").slider("option", "value", $(this).val());
    });

}

function fileInputAction() {
    $('.file-overflow').remove();
    var wrapper = $('<div class="file-hider"></div>').css({height: 0, width: 0, 'overflow': 'hidden'});
    var fileInput = $(':file').wrap(wrapper);


    function readURL(input) {
        var url = input.value;
        var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
        if (input.files && input.files[0] && (ext == "gif" || ext == 'png' || ext == 'jpeg' || ext == 'jpg')) {
            var reader = new FileReader();
            reader.onload = function (e) {
                if ($('.select-image img').length) {
                    $('.select-image img').attr('src', e.target.result);

                } else if ($('.user-form-image').length) {
                    $('.user-form-image img').attr('src', e.target.result)
                        .width(300);
                }/* else {
                 $('img.img-chooser').attr('src', e.target.result)
                 .width(254);
                 }*/
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            fileInput.val('');
        }

    }

    fileInput.off('change').on('change', function () {
        readURL(this);
        var $this = $(this);
        if ($this.val()) {
            var path = $this.val();
            path = path.substr(path.lastIndexOf('\\') + 1);
            $('.select-file').text('New file chosen: ' + path);
        } else {
            $('.select-file').text('Choose file');
        }
    });

    $('.file-chooser').off('click').on('click', function () {
        fileInput.click();

    });
    $('.img-chooser-overlay').off('click').on('click', function (e) {
        fileInput.click();
    });

    /* New user image file chooser */

    $('.user-profile-image-chooser-overlay').off('click').on('click', function () {
        fileInput.click();
    });



    /* New code for :file click handling */
    $('.select-overlay').on('click', function () {
        if ($('.ab-content').length) {
            $(this).closest('.ab-content').find(':file').click();
        } else {
            fileInput.click();
        }
    });

    $('.select-file').on('click', function () {
        if ($('.ab-content').length) {
            $(this).closest('.ab-content').find(':file').click();
        } else {
            fileInput.click();
        }
    });

    $('.select-overlay-multiple > a').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('remove-old')) {
            alert('Are you sure you want to delete the image?');
        } else {
            fileInput.click();
        }
    });

    $('.user-form-image-overlay-multiple > a').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('remove-old')) {
            alert('Are you sure you want to delete the image?');
        } else {
            fileInput.click();
        }
    });

}

function hideSubmitControls() {
    var formAction = $('.form-action');
    formAction.hide();
}

function setup() {
    mediaModalAction();

    // Multiselect
    $('body').off('click', '.cmd-multiselect').on('click', '.cmd-multiselect', function () {
        var $this = $(this).find('i.fa');
        var state = [];
        $('input[name="delete_items[]"]').each(function (e) {
            if ($(this).prop('checked')) {
                state.push($(this));
            }
        });
        if (state.length < 1) {
            var thumbnail = $('.thumbnail');
            thumbnail.find('img').addClass('isSelected');
            thumbnail.find('.state').slideDown(200);
            thumbnail.find('.bottomright i.fa').toggleClass('fa-square-o fa-check-square-o');

            $this.toggleClass('fa-square-o fa-check-square-o');

            $('input[name="delete_items[]"]').prop('checked', true);
            $('.btn-group.selected-menu .btn').css('display', 'block');
        } else {
            if ($this.hasClass('fa-check-square-o')) {
                $this.toggleClass('fa-check-square-o fa-square-o');
            }
            $('.thumbnail').each(function () {
                if ($(this).find('img').hasClass('isSelected')) {
                    $(this).find('img.isSelected').toggleClass('isSelected');
                    $(this).find('.state').slideUp(200);
                    $(this).find('.bottomright i.fa').toggleClass('fa-square-o fa-check-square-o');
                }
            });

            $('input[name="delete_items[]"]').prop('checked', false);
            $('.btn-group.selected-menu .btn').css('display', 'none');
        }
    });

    // Flat forms

    $('body').on('focus', '.field > input', function (e) {
        $(this).parent().addClass('field-focus');
    })
        .on('blur', '.field > input', function (e) {
            $(this).parent().removeClass('field-focus');
        });

    $('body').on('change', '.field.option input[type="checkbox"]', function (e) {
        $(this).next('.checksquare').toggleClass('checksquare-selected');
    });

    $('body').on('change', '.field.option input[type="radio"]', function (e) {
        $(this).closest('.radio').siblings().find('.radiobox').removeClass('radiobox-selected');
        $(this).next('.radiobox').addClass('radiobox-selected');
    });

    if ($('.affix-content').length) {
        $('body').addClass('grey');
    }

    $('.user-toggle').popover({
        html: true
    });


    $('.hide-meta').on('click', function (e) {
        var $this = $(this);
        $this.toggleClass('open');
        $('.main-content.single').toggleClass('inactive active');
        $(window).resize();
        e.preventDefault();
    });


    // Rich text editor
    if ($('textarea.rte').length) {
        initRichEditor();
    }

    // Flash messages
    if ($('.alert-flash-message').length) {
        var flashEl = $('.flash-messages');
        flashEl.slideDown(300, function () {
            setTimeout(function () {
                flashEl.slideUp(150);
            }, 4000);
        });
    }

    // Page Action: if Page is being edited, take the form, and move it to Data tab
    if ($('#page_queries_0').length) {
        var formGroup = $('#page_queries_0').closest('.form-group').addClass('edit-form').remove();
        $('.tab-pane#data').prepend(formGroup);
        formGroup.removeAttr('style');
    }

    // Override File Chooser button
    fileInputAction();


    // Slider for thumbnail resize
    if ($('#imageThumbnailSize-slider').length) {
        $('#imageThumbnailSize-slider').slider({
            min: 1,
            max: 5,
            step: 1,
            animate: false,
            value: 3,
            slide: function (event, ui) {
                var classname;
                if(ui.value == 1) {
                    classname = 'col-dyn-10'
                } else if (ui.value == 2) {
                    classname = 'col-dyn-20'
                } else if (ui.value == 3) {
                    classname = 'col-dyn-25'
                } else if (ui.value == 4) {
                    classname = 'col-dyn-33'
                } else if (ui.value == 5) {
                    classname = 'col-dyn-50'
                }

                $('.tab-pane.active .tb-col').removeClass(function (index, css) {
                    return (css.match (/(^|\s)col-\S+/g) || []).join(' ');
                }).addClass(classname);
            }
        });
    }


    if ($('.media-slider').length) {
        $('.media-slider').bxSlider({
            captions: false,
            ticker: false,
            responsive: true,
            pager: false,
            minSlides: 2,
            maxSlides: 4,
            slideWidth: 200,
            slideMargin: 10
        });
    }

    // Moving Form submit controls in Accordion
    // Needs some work - after all input fields will be known
    hideSubmitControls();
    if ($('.accordion').length) {
        accordionAction();
        resampleAction();
    }

    // Sidebar toggle
    $('.toggle-control a').on('click', function (e) {
        e.preventDefault();
        $(this).find('i.fa').toggleClass('fa-angle-double-right fa-angle-double-left');
        $('.nav-sidebar').toggleClass('switch-nav');
        $('.navbar-brand').toggleClass('switch-title');
        $('.sidebar').toggleClass('sidebar-box');
        if ($('.nav-sidebar').hasClass('switch-nav')) {
            if ($('.popover').hasClass('in')) {
                $('.popover').css('left', '270px');
            }
        } else {
            if ($('.popover').hasClass('in')) {
                $('.popover').css('left', '59px');

            }
        }

    });

    // Enable handlers for editing the user profile
    if ($('.user-profile-layout').length) {
        userAction();
    }

    $('.tooltipped').tooltip({container: 'body', delay: {show: 400, hide: 0}});


    $('.viewable-meta .nav-tabs a').on('shown.bs.tab', function () {
        scrollable();
    });

    $('a[data-toggle="tab"]').on('click', function (e) {
        var $this = $(this);

        var tabsArray = ['#videos', '#images', '#snapshots', '#media', '#mailing', '#themes', '#settings'];

        if ($.inArray($this.attr('href'), tabsArray) != -1) {

            if (!$this.closest('li').hasClass('active')) {
                var ifa = $this.find('i.fa');
                ifa.removeClass('fa-photo fa-video-camera fa-cog fa-leaf fa-history fa-envelope fa-wrench');
                ifa.addClass('fa-spin fa-spinner');
            }
        }

        if ($('.cmd-multiselect').length) {
            $('.cmd-multiselect').find('i.fa').addClass('fa-square-o').removeClass('fa-check-square-o');
            $('.btn-group.selected-menu .btn').css('display', 'none');
        }
    });


    $('a#page-tab').off('show.bs.tab').on('show.bs.tab', function (e) {
        querySelection();
    });


    $('#settings-tabs a').off('show.bs.tab').on('show.bs.tab', function (e) {
        var ifa = $(this).find('i.fa');
        var tabHref = e.target.href;
        var realTabHref = tabHref.substr(tabHref.indexOf('#') + 1);
        var contentUrl = $(this).attr('data-url');

        var ifa_class;
        if(realTabHref == 'snapshots') {
            $('.tab-pane#snapshots').html('');
            ifa_class = 'fa-history';
        } else if(realTabHref == 'media') {
            ifa_class = 'fa-photo';
            $('.tab-pane#media').html('');
        } else if (realTabHref == 'themes') {
            ifa_class = 'fa-leaf';
            $('.tab-pane#themes').html('');
        } else if(realTabHref == 'mailing') {
            ifa_class = 'fa-envelope';
            $('.tab-pane#mailing').html('');
        } else if(realTabHref == 'settings') {
            ifa_class = 'fa-cog';
            $('.tab-pane#settings').html('');
        }

        $.ajax({
            beforeSend: function () {
                $('.tab-wrap .tab-content .tab-pane').html('');
            },
            // url: baseURL + 'admin/settings/snapshots'
            url: contentUrl
        })
            .done(function (html) {
                ifa.removeClass('fa-spinner fa-spin');

                ifa.addClass(ifa_class);

                $('#' + realTabHref).html(html);
                accordionAction();
                if(realTabHref == 'media') {
                    resampleAction();
                    bindButtons();
                }
                if(realTabHref == 'themes') {
                    bindButtons();
                }
                $('.form-action').hide();
            })
            .fail(function () {
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });
    });

    // images & videos tabs handled separately, since they use ajax to show content
    $('.media-tabs a[data-toggle="tab"]').off('show.bs.tab').on('show.bs.tab', function (e) {
        var ifa = $(this).find('i.fa');
        var tabHref = e.target.href;
        var realTabHref = tabHref.substr(tabHref.indexOf('#') + 1);
        var targetEl = $('#' + realTabHref);
        $('.tab-pane.active').html('');

        $.ajax({
            beforeSend: function () {
            },
            url: baseURL + 'admin/media/' + realTabHref
        })
            .done(function (data) {
                ifa.removeClass('fa-spinner fa-spin');
                if (realTabHref == 'images') {
                    ifa.addClass('fa-photo');
                } else if (realTabHref == 'videos') {
                    ifa.addClass('fa-video-camera');
                }

                targetEl.html(data);

                bindButtons();
                descriptionOverlay();
                if ($('div.im-dropzone').length) {
                    handleDropzone('image');
                } else if ($('div.vd-dropzone').length) {
                    handleDropzone('video');

                }

            })
            .fail(function (xhr, msg) {
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });
    });

    // Post Edit: Media tab click
    $('a[data-toggle="tab"][href="#media"]').on('shown.bs.tab', function (e) {
        if ($('.page-header.top').text() != 'Settings') {
            iterateCheckbox();
        }
    });

    if ($('div.im-dropzone').length) {
        handleDropzone('image');
    } else if ($('div.vd-dropzone').length) {
        handleDropzone('video');
    }
}

$(document).ready(function () {
    setup();
    descriptionOverlay();
    bindButtons();
});

$(window).load(function () {

    var State;
    $(function() {
        // Prepare
        var History = window.History; // Note: We are using a capital H instead of a lower h
        if (!History.enabled) {
            // History.js is disabled for this browser.
            // This is because we can optionally choose to support HTML4 browsers or not.
            return false;
        }

        var anchor;

        // Bind to StateChange Event
        History.Adapter.bind(window, 'statechange', function() { // Note: We are using statechange instead of popstate
            State = History.getState();
        });

        $('#settings-tabs a').click(function(e) {
            anchor = rootURL + $(this).attr('data-url');
            e.preventDefault();
            History.pushState(null, $(this).text(), $(this).attr('data-url'));
        });

        var displayTab = function () {
            $('#settings-tabs a[data-url="' + anchor + '"]').click();
        };
        window.addEventListener("popstate", function(e) {
            var location1 = document.location.toString();

            anchor = location1.substr(location1.indexOf('13/') + 2);

            if (anchor.length) {
                displayTab(anchor);
            } /*else {
             anchor =  $('.transformer-tabs li.active a');
             displayTab(anchor);
             }*/
        });
    });

    if ($('#postsChart').length) {
        //setChartGlobalConfiguration();

        $.get(baseURL + 'admin/settings/ap', function (data) {
            var ctx = document.getElementById("postsChart").getContext("2d");
            // window.myPie = new Chart(ctx).Pie(data,{responsive: true});

            /* window.myPolarArea = new Chart(ctx).PolarArea(data, {
             responsive:true
             }); */

            /*
             window.myBarChart = new Chart(ctx).Bar(data, {
             responsive:true
             });
             */

            window.myLine = new Chart(ctx).Line(data, {
                responsive: true,
                multiTooltipTemplate: "<%= datasetLabel %> - <%= value %>"
            });

        });
    }

    if ($('#mediaChart').length) {
        //setChartGlobalConfiguration();

        $.get(baseURL + 'admin/settings/mp', function (data) {
            var ctx = document.getElementById("mediaChart").getContext("2d");
            var ctx2 = document.getElementById("mediaChart2").getContext("2d");
            window.myPie = new Chart(ctx).Pie(data, {responsive: true});
            window.myPie2 = new Chart(ctx2).Doughnut(data, {responsive: true, animateScale: true, animateRotate: false});
        });
    }
    if ($('.viewable-content').length) {
        scrollable();
    }
});
var resize_check = 0;
var rtime = new Date(1, 1, 2000, 12, 00, 00);
var timeout = false;
var delta = 200;
$(window).resize(function () {

    if($('.viewable-content').length) {
        scrollable();
    }

    rtime = new Date();
    if (timeout === false) {
        timeout = true;
        setTimeout(resizeend, delta);
    }

});

function resizeend() {

    if (new Date() - rtime < delta) {
        setTimeout(resizeend, delta);
    } else {
        timeout = false;
        if ($('body').hasClass('modal-open') && $('.media-modal').hasClass('in')) {
            centerModal();
        }
    }

}