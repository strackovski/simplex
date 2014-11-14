var baseURL = 'http://192.168.64.13/simplex/web/index_dev.php/';
Dropzone.autoDiscover = false;
var theDropzone;
$(document).ready(function () {

    // settings


    // functions

    function deactivateRightBar() {
        $('.grid-outer').removeClass('active-right-bar');
        $('.grid-outer-full').removeClass('active-right-bar');
        $('.right-toolbar').removeClass('active-right-bar');
        $('.float-btn').removeClass('active-right-bar');
        $('.right-bar').removeClass('active-right-bar');

        //post meta-btn
        $('.meta-btn').removeClass('active');
    }

    function activateRightBar() {
        $('.grid-outer').addClass('active-right-bar');
        $('.grid-outer-full').addClass('active-right-bar');
        $('.right-toolbar').addClass('active-right-bar');
        $('.float-btn').addClass('active-right-bar');
        $('.right-bar').addClass('active-right-bar');
    }


    function handleDropzone() {
        $('input[type="file"]').remove();
        $('.dropzone').remove();
        $('.upload-modal .modal-body').append('<div class="dropzone dropzone-clickable"><div class="dz-message">' +
        '<i class="fa fa-upload fa-3x"></i>' +
        '<p>Drag items here or click to open the file browser.'+
        '</p></div></div>');

        //$('.file-overflow').remove();
        //$('.file-hider').remove();
        var name, type, urlType, allowedFiles;

        if($('.media-tabs a[href="#images"]').closest('li').hasClass('active')) {
            console.log('images')
            name = 'im-';
            type = 'media';
            urlType = 'images';
            allowedFiles = 'image/*';
        }
         else {
            console.log('videos')
            name = 'vd-';
            type = 'video';
            urlType = 'videos';
            allowedFiles = '.avi,.mov,.mp4';
        }


        theDropzone = new Dropzone('div.dropzone', {
            url: baseURL + 'admin/' + type + '/upload',
            clickable: '.dropzone-clickable',
            autoProcessQueue: true,
            thumbnailWidth: null,
            thumbnailHeight: 200,
            previewTemplate:
            '<div class="dz-preview dz-file-preview">'+
                '<div class="dz-details">'+
                    '<div class="dz-filename"><span data-dz-name></span></div>' +
                    '<div class="dz-size" data-dz-size></div>' +
                    '<img data-dz-thumbnail />' +
                '</div>' +
                '<div class="dz-progress">'+'' +
                    '<span class="dz-upload" data-dz-uploadprogress></span>' +
                '</div>' +
            '</div>',
            previewsContainer: '.dropzone',
            createImageThumbnails: true,
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
                    $('.progress').addClass('hidden');
                    $('.progress-bar').attr('style', 'width: 1%');

                    $('.tab-content .tab-pane.active .tiles').html(data);

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

    function querySelection() {
        var contentTypeField, columnField, operatorField, valueField, sortByField, limitMinField, limitMaxField;

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

        var authors = $('#page_authors').closest('.data-group');
        var tags = $('#page_tags').closest('.data-group');

        // move tags and authors in the correct field
        $('#page_authors').closest('.data-group').remove();
        authors.insertAfter(valueField.closest('.data-group'));

        $('#page_tags').closest('.data-group').remove();
        tags.insertAfter(valueField.closest('.data-group'));

        // hide authors & tags form
        //$('#page_authors').closest('.data-group').addClass('hidden');
        //$('#page_tags').closest('.data-group').addClass('hidden');
        authors.addClass('hidden');
        tags.addClass('hidden');

        var column = columnField.val();
        var value = valueField.val();

        // arrays to be inserted into valueField field
        var tagsCheckboxes = [];
        var authorsCheckboxes = [];
        var dates = [];

        // hide operator options, unhide them later, based on columnField selection
        operatorField.find('option').addClass('hidden');


        // if contentType is not defined, a new page is being created
        if (!contentTypeField.val()) {

            //hide all secondary options, unhide first choices

            contentTypeField.closest('.data-group').nextAll().addClass('hidden');

            columnField.closest('.data-group').removeClass('hidden');

            operatorField.closest('.data-group').removeClass('hidden');
            operatorField.find('option[value="eq"]').removeClass('hidden').prop('selected', true);
            operatorField.find('option[value="in"]').removeClass('hidden');

            valueField.closest('.data-group').removeClass('hidden');

            limitMaxField.closest('.data-group').removeClass('hidden');
            sortByField.closest('.data-group').removeClass('hidden');



            //operatorField.find('option').removeClass('hidden');

        } else {
            if (column == 'title') {
                // show 'equals' and 'contains' options in operandField
                operatorField.find('option[value="eq"]').removeClass('hidden');
                operatorField.find('option[value="in"]').removeClass('hidden');

            } else if (column == 'created_at' || column == 'updated_at') {

                // hide valueField field
                valueField.closest('.data-group').addClass('hidden');

                // show 'after', 'before' and 'between' options in operandField
                operatorField.find('option[value="before"]').removeClass('hidden');
                operatorField.find('option[value="after"]').removeClass('hidden');
                operatorField.find('option[value="between"]').removeClass('hidden');

                // conjure up date fields, if they have not been created yet, apply datepicker() on them
                if (!($('.dateField').length)) {
                    var valueFieldClone2 = valueField.closest('.data-group').clone().addClass('dateField toDateField');
                    $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone2.find('.form-element'));
                    valueFieldClone2.find('.form-element').addClass('prepend-icon');
                    // change label, id, remove 'required' attr
                    valueFieldClone2.find('label').text('To:').attr('for', 'date_2');
                    valueFieldClone2.find('input').attr({id: 'date_2', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
                        onClose: function (dateText) {
                            // call a function that collects dateField values and inserts them into valueField field
                            dateChange();
                        }
                    });
                    valueFieldClone2.insertAfter(valueField.closest('.data-group'));

                    // change label, id, remove 'required' attr, unhide forms
                    var valueFieldClone1 = valueField.closest('.data-group').clone().addClass('dateField fromDateField');
                    $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone1.find('.form-element'));
                    valueFieldClone1.find('.form-element').addClass('prepend-icon');
                    valueFieldClone1.find('label').text('From').attr('for', 'date_1');
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
                    $('.fromDateField').find('input#date_1').val(datesArray[0]);
                } else if (operatorField.val() == 'between') {
                    $('.fromDateField').find('input#date_1').val(datesArray[0]);
                    $('.toDateField').find('input#date_2').val(datesArray[1]);
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
            //$(this).closest('.row').siblings().find('.data-group').addClass('hidden');
            //$(this).closest('.data-group').nextAll().addClass('hidden');

            // hide tags and authors // NEW
            //$('#page_tags').closest('.form-group').addClass('hidden');
            //$('#page_authors').closest('.form-group').addClass('hidden');

            // unhide columnField
            //$(this).closest('.data-group').next().removeClass('hidden');

            // set columnField to 'Select a column ...'
            columnField.find('option:eq(0)').prop('selected', true);
            operatorField.find('option').addClass('hidden');
            operatorField.find('option[value="eq"]').removeClass('hidden').prop('selected', true);
            operatorField.find('option[value="in"]').removeClass('hidden');
            valueField.val('');
            valueField.closest('.data-group').removeClass('hidden');
            tags.addClass('hidden');
            authors.addClass('hidden');
            $('.dateField').addClass('hidden');
            $('.dateField').val('');


        });

        columnField.on('change', function () {

            operatorField.find('option').addClass('hidden');

            valueField.val('');
            valueField.closest('.data-group').addClass('hidden');

            $('.dateField').val('');
            $('.dateField').closest('.data-group').addClass('hidden');

            authors.addClass('hidden');
            tags.addClass('hidden');


            // hide all subsequent .form-groups
            //$(this).closest('.row').nextAll().find('.data-group').addClass('hidden');

            // hide tags and authors
            //$('#page_tags').closest('.form-group').addClass('hidden');
            //$('#page_authors').closest('.form-group').addClass('hidden');

            // reset valueField and dateFields
            //valueField.val('');
            //$('.datepickerField').val('');
            //limitMaxField.val('');
            //sortByField.val('');

            //columnField.closest('.data-group').nextAll().addClass('hidden');

            //valueField.closest('.data-group').addClass('hidden');
            //authors.addClass('hidden');
            //tags.addClass('hidden');
            //$('.dateField').addClass('hidden');

            // hide operatorField's options (unhide them later)
            //operatorField.find('option').addClass('hidden');

            tagsCheckboxes.length = 0;
            authorsCheckboxes.length = 0;
            dates.length = 0;


            var columnCurrent = $(this).val();

            if (columnCurrent == 'title') {

                valueField.closest('.data-group').removeClass('hidden');

                // operatorField, show 'equals' and 'contains' options
                operatorField.closest('.data-group').removeClass('hidden');
                operatorField.find('option[value="eq"]').removeClass('hidden').prop('selected', true);
                operatorField.find('option[value="in"]').removeClass('hidden');

            } else if (columnCurrent == 'updated_at' || columnCurrent == 'created_at') {

                // show operatorField, show 'after', 'before' and 'between' options, make 'between' the selected one
                //$('.dateField').removeClass('hidden');
                operatorField.closest('.data-group').removeClass('hidden');
                operatorField.find('option[value="before"]').removeClass('hidden');
                operatorField.find('option[value="after"]').removeClass('hidden');
                operatorField.find('option[value="between"]').removeClass('hidden').prop('selected', true);

                // conjure up date fields, if they have not been created yet, apply datepicker() on them
                if (!($('.dateField').length)) {
                    var valueFieldClone2 = valueField.closest('.data-group').clone().addClass('dateField toDateField').removeClass('hidden');
                    $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone2.find('.form-element'));
                    valueFieldClone2.find('.form-element').addClass('prepend-icon');
                    valueFieldClone2.find('label').text('And date: ').attr('for', 'date_2');
                    valueFieldClone2.find('input').attr({id: 'date_2', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
                        onClose: function (dateText) {
                            dateChange();
                        }
                    });
                    valueFieldClone2.insertAfter(valueField.closest('.data-group'));

                    var valueFieldClone1 = valueField.closest('.data-group').clone().addClass('dateField fromDateField').removeClass('hidden');
                    $('<div class="field-addon"><i class="fa fa-calendar"></i></div>').appendTo(valueFieldClone1.find('.form-element'));
                    valueFieldClone1.find('.form-element').addClass('prepend-icon');
                    valueFieldClone1.find('label').text('Date:').attr('for', 'date_1');
                    valueFieldClone1.find('input').attr({id: 'date_1', name: ''}).removeAttr('required').addClass('datepickerField').datepicker({
                        onClose: function (dateText) {
                            dateChange();
                        }
                    });
                    valueFieldClone1.insertAfter(valueField.closest('.data-group'));
                } else { // just unhide dateFields
                    $('.dateField').removeClass('hidden');
                }

            } else if (columnCurrent == 'author') {
                authorsAction();

            } else if (columnCurrent == 'tags') {
                tagsAction();
            }
            //limitMinField.closest('.data-group').removeClass('hidden');
            //limitMaxField.closest('.data-group').removeClass('hidden');
            //sortByField.closest('.data-group').removeClass('hidden');

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
                    $('.dateField').removeClass('hidden');
                } else if (currentOperator == 'before' || currentOperator == 'after') {
                    $('.fromDateField').removeClass('hidden');
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
            $('#page_authors').closest('.data-group').removeClass('hidden');
            operatorField.closest('.data-group').addClass('hidden');
            valueField.closest('.data-group').addClass('hidden');
            $('.dateField').closest('.data-group').addClass('hidden');
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
            $('#page_tags').closest('.data-group').removeClass('hidden');
            operatorField.closest('.data-group').addClass('hidden');
            valueField.closest('.data-group').addClass('hidden');
            $('.dateField').closest('.data-group').addClass('hidden');
        }
    }


    querySelection();


    // handlers

    // flat forms
    $('body').on('focus', '.form-element > input', function (e) {
        $(this).parent().addClass('field-focus');
    })
        .on('blur', '.form-element > input', function (e) {
            $(this).parent().removeClass('field-focus');
        });

    $('body').on('change', '.field.option input[type="checkbox"]', function (e) {
        $(this).next('.checksquare').toggleClass('checksquare-selected');
    });

    $('body').on('change', '.field.option input[type="radio"]', function (e) {
        $(this).closest('.radio').siblings().find('.radiobox').removeClass('radiobox-selected');
        $(this).next('.radiobox').addClass('radiobox-selected');
    });

    if ($('.tooltipped').length) {
        $('.tooltipped').tooltip();
    }

    if($('.select').length) {
      /*  $('.select select').selectmenu({
            change: function( event, ui ) {

                console.log($(this).val())
            }
        });*/
    }


    $('header').on('show.bs.tab', '.media-tabs a[data-toggle="tab"]', function (e) {
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
                targetEl.html(data);

            })
            .fail(function (xhr, msg) {
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });
    });

    $('header').on('click', '.user-tabs a' , function (e) {
        var $this = $(this);
        if (!($(this).hasClass('active'))) {

            if ($(this).attr('href') == '#credentials') {
                $.ajax({
                    beforeSend: function () {
                        $('.tab-pane#credentials').html('');
                    },
                    url: baseURL + 'admin/user/credentials'
                })
                    .done(function (html) {
                        $this.find('i.fa').fadeOut().remove();
                        $('.tab-pane#credentials').html(html);
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

    $('.settings-tabs a').off('show.bs.tab').on('show.bs.tab', function (e) {

        var tabHref = e.target.href;
        var realTabHref = tabHref.substr(tabHref.indexOf('#') + 1);
        var contentUrl = $(this).attr('data-url');

        $.ajax({
            beforeSend: function () {
                $('.tab-wrap .tab-content .tab-pane').html('');
            },
            // url: baseURL + 'admin/settings/snapshots'
            url: contentUrl
        })
            .done(function (html) {

                $('#' + realTabHref).html(html);
                accordionAction();
                if(realTabHref == 'media') {
                    //resampleAction();
                    //bindButtons();
                }
                if(realTabHref == 'themes') {
                    //bindButtons();
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

    // page navigation, add mask
    $('.nav-btn').on('click', function () {
        $('.side-nav').addClass('side-nav-active');
        $('body').addClass('masked');
        $('.page-mask').addClass('mask-visible');
    });

    $('.page-mask').on('click', function () {
        $(this).removeClass('mask-visible');
        $('body').removeClass('masked');
        $('.side-nav').removeClass('side-nav-active');
    });

    // detail-btn (posts, pages list)
    $('.grid').on('click', '.detail-btn', function (e) {
        e.preventDefault();

        var $this = $(this);

        if ($this.closest('.item-wrap').hasClass('active')) {
            $this.closest('.item-wrap').removeClass('active');
            deactivateRightBar();
        } else {
            if ($('.item-wrap.active').length) {
                $('.item-wrap.active').removeClass('active');
                $this.closest('.item-wrap').addClass('active');
                $('.right-bar').html('reload');

                // todo: RELOAD data in .right-bar

            } else {
                $this.closest('.item-wrap').addClass('active');
                activateRightBar();

                // todo: LOAD data in .right-bar

            }
        }
    });

    // right-bar handlers
    // todo: make it work for every view, not just pages/posts

    $('body').keydown(function (e) {
        if (e.keyCode == 27 && $('.right-bar').hasClass('active-right-bar')) {

            $('.right-bar .close-btn').click();
        }

    });

    $('.right-bar').on('click', '.close-btn', function (e) {
        e.preventDefault();
        $('.item-wrap.active').removeClass('active');
        deactivateRightBar();

        if ($('body').hasClass('modal-open') && $('.media-modal').hasClass('in')) {
            $('.media-modal').modal('hide');
        }
    });


    // help-btn (login page)
    $('.help-btn').on('click', function (e) {
        e.preventDefault();
        activateRightBar();
    });

    // meta-btn

    $('.meta-btn').on('click', function (e) {

        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('active')) {
            $this.removeClass('active');
            deactivateRightBar();
        } else {
            $this.addClass('active');
            activateRightBar();
        }
    });

    $('.grid').on('click', '.media-select-all', function (e) {
        e.preventDefault();
        $('.thumb-check').addClass('active');
        $('.thumbnail').addClass('active edit-mode');
        $('.thumbnail').find('input').prop('checked', true);
        $('.media-count').html($('.thumb-check.active').length + ' selected');
    });


    $('.grid').on('click', '.media-deselect-all', function (e) {
        e.preventDefault();
        $('.thumb-check').removeClass('active');
        $('.thumbnail').removeClass('active edit-mode');
        $('.thumbnail').find('input').prop('checked', false);
        $('.media-actions').hide();
    });

    $('.upload-btn').on('click', function (e) {
        e.preventDefault();
        $('.upload-modal').modal();
        handleDropzone();


    });

    $('.grid').on('click', '.delete-btn', function (e) {
        console.log('delete tbn')
        e.preventDefault();
        var href = $(this).attr('href');
        $('.confirm-modal').modal();
        $('.confirm-modal .btn-confirm').off('click').on('click', function (e) {
            e.preventDefault();

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
                    $('.tab-pane.active .tiles').html(data)

                })
                .fail(function (xhr, msg) {
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });

            $('.thumbnail.active').closest('.removable').remove();
            //$('.isSelected').closest('.removable').remove();
            $('.confirm-modal').modal('hide');
            $('.media-actions').hide();
            //$('.btn-group.selected-menu .btn').css('display', 'none');
            //$('.cmd-multiselect').find('i.fa').removeClass('fa-check-square-o').addClass('fa-square-o');
        });
    });

    $('.grid').on('click', '.thumbnail', function (e) {
        e.preventDefault();

        var $this;

        if ( $(event.target).closest('.thumb-check').length > 0 ) {
            $this = $(this).find('.thumb-check');
            if ($this.hasClass('active')) {
                $this.removeClass('active');
                $this.closest('.thumbnail').removeClass('active');
                $this.closest('.thumbnail').find('input').prop('checked', false);

                if ($('.thumbnail.active').length) {
                    // todo: change how many are selected (media-actions)

                } else {
                    $('.thumbnail').removeClass('edit-mode');
                    $('.media-actions').hide();
                }
            } else {
                $this.addClass('active');
                $('.thumbnail').addClass('edit-mode');
                $this.closest('.thumbnail').find('input').prop('checked', true);
                $this.closest('.thumbnail').addClass('active');

                if ($('.media-actions').is(':visible')) {
                    // todo : update how many are selected
                } else {
                    $('.media-actions').show();
                }
            }

            $('.media-count').html($('.thumb-check.active').length + ' selected');
            return false;
        }

        if($('#images').length) {
            $this = $(this);

            var $src = $this.find('img').attr('src');
            console.log($src);

            $('.media-modal .modal-body').html('<img src="'+ $src +'" alt="pumpkin" />');

            $('.media-modal').modal({
                backdrop: 'static'
            });

            activateRightBar();
        }
    });

    $('body').on('shown.bs.modal', '.media-modal', function () {
        mediaModalAction();

    });

    $('body').on('show.bs.modal', '.media-modal', function () {
      //  $('.media-modal .modal-body').html('<img src="ambers-ambry.jpg" alt="pumpkin" />');
    });

    $.each($('.grid #media .thumbnail'), function () {
        if ($(this).find('input[type="checkbox"]').prop('checked') == true) {
            if (!($(this).hasClass('active'))) {
                $(this).addClass('active');
                $(this).find('.thumb-check').addClass('active')
            }

        }
    });

});

function centerModal() {
    var modalContentHeight = $('.media-modal .modal-content').height() + 50;
    var windowHeight = $(window).height();
    var m = (windowHeight - modalContentHeight) / 2;
    $('.media-modal .modal-content').css('margin-top', m);
}

function mediaModalAction() {

    $('.media-modal .modal-body img').on('load', function () {
        centerModal();
        $(this).animate({opacity: 1});
    });


    $('.modal').keydown(function (e) {
        if (e.keyCode == 37) {
            $('.modal').find('.modal-prev').trigger('click');
        } else if (e.keyCode == 39) {
            $('.modal .modal-next').trigger('click');
        }
    });

}

var resize_check = 0;
var rtime = new Date(1, 1, 2000, 12, 00, 00);
var timeout = false;
var delta = 200;

$(window).resize(function () {
    if ($('.select').length) {
        $('.select').selectmenu('destroy');
        $('.select').selectmenu();
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