/*****************************************************
 Simplex UI script for Material Theme
 Neja Dolinar <dolinar.neja@gmail.com>
 Vladimir Stračkovski <vlado@nv3.org>
 2014
 *****************************************************/
var baseURL;
var debug;

// Dropzone
var theDropzone;
Dropzone.autoDiscover = false;

$(function() {
    $('body').on('click', '.section-link', function() {
        if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
            if (target.length) {
                $('html,body').animate({
                    scrollTop: target.offset().top - 240
                }, 250);
                return false;
            }
        }
    });
});

/**
 * Sidebar detail-panel handlers
 */
function deactivateRightBar() {
    $('.grid-outer').removeClass('active-right-bar');
    $('.item-fixed-header').removeClass('active-right-bar');
    $('.grid-outer-full').removeClass('active-right-bar');
    $('.right-toolbar').removeClass('active-right-bar');
    $('.float-btn').removeClass('active-right-bar');
    $('.right-bar').removeClass('active-right-bar');
    $('.meta-btn').removeClass('active'); //post meta-btn
}

function activateRightBar() {
    $('.grid-outer').addClass('active-right-bar');
    $('.item-fixed-header').addClass('active-right-bar');
    $('.grid-outer-full').addClass('active-right-bar');
    $('.right-toolbar').addClass('active-right-bar');
    $('.float-btn').addClass('active-right-bar');
    $('.right-bar').addClass('active-right-bar');
}

/**
 * AJAX ERROR LOGGER for debug mode
 * @param msg   The error message
 * @param xhr   The xhr object
 */
function logXhrError(msg, xhr) {
    if (debug == 1) {
        console.log('*** ERROR ***');
        console.log(msg);
        console.log(xhr);
    }
}

/**
 * MEDIA DROPZONE HANDLER
 */
function handleDropzone() {
    var defaultDropzoneControlHtml = '<div class="dropzone dropzone-clickable"><div class="dz-message">' +
        '<i class="fa fa-upload fa-3x"></i>' +
        '<p>Drag items here or click to open the file browser.'+
        '</p></div></div>';

    var mediaDropzonePreviewHtml = '<div class="dz-preview dz-file-preview col-xs-3">'+
        '<div class="dz-details">'+
        '<img data-dz-thumbnail />' +
        '</div>' +
        '<div class="dz-progress">'+'' +
        '<span class="dz-upload" data-dz-uploadprogress></span>' +
        '</div>' +
        '</div>';

    $('input[type="file"]').remove();
    $('.dropzone').remove();
    $('.upload-modal .modal-body').append(defaultDropzoneControlHtml);

    var name, type, urlType, allowedFiles;

    if($('.media-tabs a[href="#images"]').closest('li').hasClass('active')) {
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

    theDropzone = new Dropzone('div.dropzone', {
        url: type + '/upload',
        clickable: '.dropzone-clickable',
        autoProcessQueue: true,
        thumbnailWidth: null,
        thumbnailHeight: 200,
        previewTemplate: mediaDropzonePreviewHtml,
        previewsContainer: '.dropzone',
        createImageThumbnails: true,
        acceptedFiles: allowedFiles
    });

    theDropzone.on("addedfile", function (file) {
        $('.progress').removeClass('hidden');
        $('.dz-message').html('<h2>Uploading</h2><p><i class="fa fa-spin fa-2x fa-spinner"></i></p>');
        $('.upload-modal .modal-footer .btn').prop('disabled', true);
        $('.modal-header .sr-only').prop('disabled', true);
        $('.page-loader').show();
    });

    theDropzone.on("queuecomplete", function (file) {
        $.ajax({
            beforeSend: function () {
                // $('.main-content .tab-pane.active').animate({opacity: 0});
            },
            url: '/admin/media/' + urlType
        }).done(function (data) {
            $('.page-loader').hide();
            $('.progress').addClass('hidden');
            $('.progress-bar').attr('style', 'width: 1%');
            $('.tab-content .tab-pane.active').html(data);
            $('.upload-modal .modal-footer .btn').prop('disabled', false).click();
            $('.upload-modal .modal-header .sr-only').prop('disabled', false);
        }).fail(function (xhr, unknown, error) {
            var flash = '<div class="flash-error">Oh-oooh. QueueComplete failed.</div>';
            logXhrError(error, xhr);
            $('body').append(flash);
            $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                $('.flash-error').remove();
            });
        });
    });

    theDropzone.on("error", function (file, msg, xhr) {
        logXhrError(msg, xhr);
        var flash = '<div class="flash-error">Oh-oooh. Dropzone Error.</div>';
        $('body').append(flash);
        $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
            $('.flash-error').remove();
        });
    });

    theDropzone.on("totaluploadprogress", function (progress, totalBytes, bytesSent) {
        $('.progress .progress-bar').attr('style', 'width: ' + progress + '%');
    });
}

/**
 * ADD (PAGE) QUERY FORM
 *
 * Dynamically add a new form when this link is clicked,
 * using the data-prototype attribute. The data-prototype HTML
 * contains the tag text input element with a name of
 * page[queries][__name__][name] and id of page_queries___name___name.
 *
 * The __name__ is a little "placeholder", which you'll
 * replace with a unique, incrementing number
 * (e.g. page[queries][3][name]).
 *
 * @link http://symfony.com/doc/current/cookbook/form/form_collections.html
 * @param $collectionHolder The container of forms
 * @param $newLinkLi The new query link
 */
function addQueryForm($collectionHolder, $newLinkLi) {
    // Add new form
    var prototype = $collectionHolder.data('prototype');
    var index = $collectionHolder.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $collectionHolder.data('index', index + 1);
    var $newFormLi = $('<li class="clearfix query-form"></li>').append(newForm);
    $newLinkLi.after($newFormLi);
    addQueryFormDeleteLink($newFormLi.find('.fieldForm-right'));
    // Init collection field
    var count = $newFormLi.find('input[data-opt="value"]').length;
    var valuePrototype = $(newForm).find('.value-box').attr('data-prototype');
    var newValueForm = valuePrototype.replace(/\[value\]\[[0-9]+\]/g, '[value]['+count+']');
    newValueForm = newValueForm.replace(/value_[0-9]+/g, 'value_'+count);
    $newFormLi.find('.value-box').prepend(newValueForm);
    $newFormLi.find('.val').find('.form-group').addClass('form-element');
}

/**
 * ADD (PAGE) QUERY FORM DELETE LINK
 *
 * Remove the query form from DOM
 *
 * @link http://symfony.com/doc/current/cookbook/form/form_collections.html
 * @param $queryFormLi
 */
function addQueryFormDeleteLink($queryFormLi) {
    var $removeFormA = $('<a href="#" class="delete_query_link"><i class="fa fa-minus-square-o"></i></a>');
    $queryFormLi.append($removeFormA);
    $removeFormA.on('click', function(e) {
        e.preventDefault();
        console.log($queryFormLi.closest('li.query-form').siblings());
        if ($queryFormLi.siblings().length===1) {
            $('.li-empty > div').removeClass('hidden');
        }
        $queryFormLi.closest('li.query-form').remove();
    });
}

/**
 * ADD FORM FIELD FORM
 */
function addFieldForm($fieldCollectionHolder, $newLinkLi) {
    var prototype = $fieldCollectionHolder.data('prototype');
    var index = $fieldCollectionHolder.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $fieldCollectionHolder.data('index', index + 1);
    var $newFormLi = $('<div></div>').append(newForm);
    $newLinkLi.after($newFormLi);
    addFieldFormDeleteLink($newFormLi);
}

/**
 * ADD FORM FIELD FORM DELETE LINK
 */
function addFieldFormDeleteLink($fieldFormLi) {
    var $removeFormA = $('<a href="#"><i class="fa fa-minus-square"></i></a>');
    $fieldFormLi.find('.fieldForm-right').html($removeFormA);
    $removeFormA.on('click', function(e) {
        e.preventDefault();
        if ($fieldFormLi.siblings().length===1) {
            $('.li-empty > div').removeClass('hidden');
        }
        console.log($fieldFormLi)
        $fieldFormLi.remove();
    });
}

/**
 * Get query result
 * @param queryId
 */
function getQueryResult(queryId) {
    $.ajax({
        url: baseURL + '/admin/page/query/' + queryId,
        method: "POST"
    }).done(function(data){
        return data;
    });
}

/**
 * Initialize the rich text editor
 *
 * @param height Initial editor height
 */
function initRichEditor(height) {
    if (!height) { height = 300; }

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

/**
 *
 */
function fileInputAction() {
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
                    $('.user-form-image img').attr('src', e.target.result).width(300);
                }
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
            $('.confirm-modal').find('.modal-body').html('Are you sure you want to remove the image?');
            $('.confirm-modal').modal();
        } else {
            fileInput.click();
        }
    });
}

/**
 * Modal center
 */
function centerModal() {
    var modalContentHeight = $('.media-modal .modal-content').height() + 50;
    var windowHeight = $(window).height();
    var m = (windowHeight - modalContentHeight) / 2 + 15;
    $('.media-modal .modal-content').css('margin-top', m);
}

/**
 * Media modal handler
 */
function mediaModalAction() {
    $('.media-modal .modal-body img').on('load', function () {
        centerModal();
        $(this).animate({opacity: 1});
    });
    centerModal();
    setTimeout(function(){
        centerModal();
    }, 200);

    $('.modal').keydown(function (e) {
        if (e.keyCode == 37) {
            $('.modal').find('.modal-prev').trigger('click');
        } else if (e.keyCode == 39) {
            $('.modal .modal-next').trigger('click');
        }
    });
}

/**
 * Show help display
 */
function showSectionHelper() {
    var sectionHref = window.location.href;
    var section = sectionHref.substr(sectionHref.lastIndexOf('/') + 1);
    var sections = ['posts', 'pages', 'content', 'media'];

    if (sections.indexOf(section) != -1 ) {
        setTimeout(function() {
            $.ajax({
                url: baseURL + section + '/help'
            }).done(function (data) {
                $('.page-loader').hide(0);
                $('.help-modal .modal-content').html(data);
                $('.help-modal').modal();
                $('.help-modal .btn-confirm').off('click').on('click', function (e) {
                })
            });
        }, 500);
    }
}

/**
 * On document ready...
 */
$(document).ready(function ()
{
    if ($('.label-flash').length) {
        var $flashLabel = $('.label-flash');
        $flashLabel.css('margin-left', - $flashLabel.width() / 2);
        $flashLabel.animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
            $('.label-flash').remove();
        });
    }

    /*
    if ($('#testChart').length) {
        var ctx = $("#testChart").get(0).getContext("2d");
        var data = $.get(baseURL + "/admin/service/analytics/test", function(data) {
            var myLineChart = new Chart(ctx).Line(data);
        });
    }
    */

    $('.datepicker').datepicker();

    /**
     * PAGE FORM HANDLERS
     */
    if ($('form[name="page"]').length) {
        var queryForms = $('li.query-form');
        // Valid 'operator' options by 'column' value
        var dateOpSelectValues = {after: 'After', before: 'Before', between: 'Between', eq: 'Equals'};
        var stringOpSelectValues = {eq: 'Equals', 'in': 'Contains'};
        var tagOpSelectValues = {eq: 'Has exact tags', 'in': 'Has any of tags'};
        var boolOpSelectValues = {'true': 'True', 'false': 'False'};
        var authorOpSelectValues = {'eq': 'Is', 'neq': 'Is not'};

        // Valid 'column' values
        var stringColValues = ['contentLabel', 'title'];
        var boolColValues = ['exposed'];
        var tagColValues = ['tags'];
        var dateColValues = ['updated_at', 'created_at'];

        // Check if any date fields and apply datepicker
        queryForms.each(function() {
            if (dateColValues.indexOf($(this).find('select[data-opt="column"]').val()) != -1) {
                $(this).find('.val').find('input').datepicker();
            }
        });

        /**
         * PageQuery 'column' option change handler
         *
         * Modify operator select options list according to selected column value
         * Populate operator select with options for each column type defined above
         */
        $(document.body).on('change', 'select[data-opt="column"]', function () {
            var $column = $(this);
            var $opSelect = $column.closest('.data-group').find('select[data-opt="operator"]');
            var $valText = $column.closest('.data-group').find('input[data-opt="value"]');
            $valText.datepicker('destroy');

            // Detect 'column' value and populate select with correct options
            if (stringColValues.indexOf($column.val()) != -1) {
                $opSelect.find('option').each(function () { $(this).remove(); });
                $.each(stringOpSelectValues, function (key, value) {
                    $opSelect.append($('<option>', {value: key}).text(value));
                });
            } else if (dateColValues.indexOf($column.val()) != -1) {
                $opSelect.find('option').each(function () { $(this).remove(); });
                $.each(dateOpSelectValues, function (key, value) {
                    $opSelect.append($('<option>', {value: key}).text(value));
                });
                $valText.datepicker();
            } else if (tagColValues.indexOf($column.val()) != -1) {
                $opSelect.find('option').each(function () { $(this).remove(); });
                $.each(tagOpSelectValues, function (key, value) {
                    $opSelect.append($('<option>', {value: key}).text(value));
                });
            } else if (boolColValues.indexOf($column.val()) != -1) {
                $opSelect.find('option').each(function () { $(this).remove(); });
                $.each(boolOpSelectValues, function (key, value) {
                    $opSelect.append($('<option>', {value: key}).text(value));
                });
            } else if ($column.val() == 'author') {
                $opSelect.find('option').each(function () { $(this).remove(); });
                $.each(authorOpSelectValues, function (key, value) {
                    $opSelect.append($('<option>', {value: key}).text(value));
                });
            }
            $opSelect.trigger('change');
        });

        /**
         * PageQuery 'operator' option change handler
         *
         * When 'operator' changes, check if more value fields are needed
         */
        $(document.body).on('change', 'select[data-opt="operator"]', function () {
            var $parentGroup = $(this).closest('.variable');
            // Range value: add new 'value' field
            if ($(this).val() == 'between') {
                var count = $parentGroup.find('input[data-opt="value"]').length;
                var valuePrototype = $parentGroup.find('.value-box').attr('data-prototype');
                var newValueForm = valuePrototype.replace(/\[value\]\[[0-9]+\]/g, '[value]['+count+']');
                newValueForm = newValueForm.replace(/value_[0-9]+/g, 'value_'+count);
                var $newValueField = $('<div class="col-xs-2 col val"></div>').append(newValueForm);
                $newValueField.insertAfter($parentGroup.find('.val'));
                $parentGroup.find('.val').find('.form-group').addClass('form-element');
                // Init Datepicker if field is a date
                if (dateColValues.indexOf($parentGroup.find('select[data-opt="column"]').val()) != -1) {
                    $newValueField.find('input').datepicker();
                }
            } else {
                $parentGroup.find('.val').next('.val').remove();
            }
        });

        /**
         * ADD NEW QUERY FORM
         *
         *  Read data-prototype attribute and dynamically add new
         *  query forms when the user clicks on "Add a query" link.
         */
        var $collectionHolder;
        var $addQueryLink = $('<a href="#" class="add_query_link pull-right"><i class="fa fa-plus-square"></i></a>');
        var $newLinkLi = $('<li class="data-header clearfix"><h5>Page Queries</h5></li>').append($addQueryLink);
        $collectionHolder = $('ul.queries');
        $collectionHolder.find('li').each(function() {
            addQueryFormDeleteLink($(this).find('.fieldForm-right'));
        });
        $collectionHolder.prepend($newLinkLi);
        $collectionHolder.data('index', $collectionHolder.find(':input').length);

        $addQueryLink.on('click', function(e) {
            e.preventDefault();
            $('.li-empty > div').addClass('hidden');
            addQueryForm($collectionHolder, $newLinkLi);
        });
    }

    /**
     * FORM FORM HANDLERS
     */
    if ($('form[name="form"]').length) {
        var $fieldCollectionHolder;
        var $addFieldLink = $('<a href="#" class="add_field_link pull-right"><i class="fa fa-plus-square"></i></a>');
        var $newFormLinkLi = $('<div class="data-header clearfix"><h5>Form Fields</h5></div>').append($addFieldLink);
        $fieldCollectionHolder = $('div.fields');

        $(document.body).on('change', '.trigger', function() {
            var $trigger = $(this);
            var $triggerGroup = $trigger.closest('.data-group');
            var $formGroup = $trigger.closest('.field-form');
            $triggerGroup.find('.cx-hidden').addClass('hidden');
            if ($trigger.val() == 'select') {
                $triggerGroup.find('.select-hidden').removeClass('hidden');
            } else if ($trigger.val() == 'text') {
                //
            } else if ($trigger.val() == 'checkbox' || $trigger.val() == 'radio') {
                $triggerGroup.find('.checkbox-hidden').removeClass('hidden');
            }
            $formGroup.find('.new').hide();
            $formGroup.find('.pf-header .type').html($trigger.val());
        });

        $(document.body).on('change', '.field-form .name-tg', function() {
            var $formGroup = $(this).closest('.field-form');
            $formGroup.find('.pf-header .name').html($(this).val());
        });

        $fieldCollectionHolder.prepend($newFormLinkLi);
        $fieldCollectionHolder.find('div.field-form').each(function() {
            addFieldFormDeleteLink($(this));
        });
        $fieldCollectionHolder.data('index', $fieldCollectionHolder.find(':input').length);
        $addFieldLink.on('click', function(e) {
            e.preventDefault();
            $('.li-empty > div').addClass('hidden');
            addFieldForm($fieldCollectionHolder, $newFormLinkLi);
        });
    }

    /**
     * Media Settings Image Quality Slider
     */
    $('#imageResampleQuality-slider').slider({
        min: 10,
        max: 100,
        value: $('#media_settings_imageResampleQuality').val(),
        slide: function(event, ui) {
            $('#media_settings_imageResampleQuality').val(ui.value)
        }
    });

    $('body').on('change', '#media_settings_imageResampleQuality', function (e) {
        $('#imageResampleQuality-slider').slider('value', $('#media_settings_imageResampleQuality').val());
    });

    // Status toggles
    $('.toggle-icon').on('click', function(e){
        e.preventDefault();
        var $this = $(this);
        var $title = $(this).attr('title');
        $('.confirm-modal .modal-body').html("<p>This action will <strong>"+ $title +"</strong> this post on your public site. Are you sure?</p>");
        $('.confirm-modal').modal();
        $('.confirm-modal .btn-confirm').off('click').on('click', function (e) {
            $this.find('i.fa').toggleClass('active');
            $.ajax({
                url: $this.attr('data-action'),
                beforeSend: function () {

                }
            }).fail(function (xhr, msg) {logXhrError(msg, xhr);});
            $('.confirm-modal').modal('hide');
        });

    });

    baseURL = $('body').attr('data-base') + '/admin/';
    debug = $('body').attr('data-env');

    if (debug == 1) {
        console.log('*** APPLICATION IS IN DEBUG MODE ***');
        console.log(baseURL);
    }

    $('.grid').on('click', '.dismiss-popover', function(e) {
        e.preventDefault();
        var $this = $(this);
        $this.closest('.chip2-content-info').find('.popover').popover('hide');
    });

    $('.user-popover').on('click', function(e) {
        var $this = $(this);
        if ($this.closest('.chip2-content-info').find('.popover').hasClass('in')) {
            return false;
        }
        e.preventDefault();
        var el = $(this);

        $.ajax({
            url: el.attr('href'),
            data: {'view': 'user-card'}
        }).done(function (d) {
            el.popover({content: d, html: true, viewport: 'body', trigger: 'manual'}).popover('show');
        }).fail(function (xhr, msg) {logXhrError(msg, xhr);});

    });

    /* side-nav submenu */
    var activeSideMenu = $('.side-nav a.submenu-open.active').attr('href');
    $(activeSideMenu).toggleClass('hidden');

    /* @todo
    var activeInnerMenu = $('.side-nav .submenu a.active');
    activeInnerMenu.closest('.submenu').removeClass('hidden');
    */

    //$('.datepicker').datepicker();

    $('.submenu-open').on('click', function(e) {
        e.preventDefault();
        var id = $(this).attr('href');
        $(id).toggleClass('hidden');
    });

    $('.grid').on('click', '.chip-close', function (e) {
        var chip = $(this).closest('.chip2');
        $('.chips .popover').popover('hide');
        chip.removeClass('chip2-active');
        chip.find('.chip2-content').hide();
        chip.find('.chip2-line').hide();
        chip.find('.chip2-summary').show();
        chip.find('.chip2-pre-header').addClass('hidden');

    });

    $('.chip2-header').on('click', function (e) {
        if($('.animated-loader').length) { return false; }

        if (!($(e.target).closest('.chip2-controls').length > 0)) {
            var $this = $(this);
            var chip = $this.closest('.chip2');

            if (chip.hasClass('chip2-active')) {
                return false;
            }
            $('.chips .popover').popover('hide');
            chip.addClass('chip2-active');
            chip.find('.chip2-loader').addClass('animated-loader');

            // reset all chips
            chip.siblings().removeClass('chip2-active');
            chip.siblings().find('.chip2-content').hide();
            chip.siblings().find('.chip2-line').hide();
            chip.siblings().find('.chip2-summary').show();
            chip.siblings().find('.chip2-pre-header').addClass('hidden');

            setTimeout(function(){
                chip.find('.chip2-loader').removeClass('animated-loader');
                chip.find('.chip2-content').slideDown();
                chip.find('.chip2-line').fadeIn();
                chip.find('.chip2-summary').hide();
                chip.find('.chip2-pre-header').removeClass('hidden');
            }, 400);
        }
    });

    $('.grid').on('click', '.chip-controls a', function (e) {
        if ($(this).hasClass('delete-post')) {
            e.preventDefault();
            var href = $(this).attr('href');
            $('.confirm-modal').modal();
            $('.confirm-modal .btn-confirm').off('click').on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url: href,
                    beforeSend: function () {
                        $('.page-loader').show();
                    }
                }).done(function (data) {
                    $('.page-loader').hide(0);
                    deactivateRightBar();
                    $('.grid-content').html(data);

                }).fail(function (xhr, msg) {
                    logXhrError(msg, xhr);
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });
                $('.confirm-modal').modal('hide');
            });
        }
    });

    // Rich text editor
    if ($('textarea.rte').length) {
        initRichEditor();
    }

    // handlers
    if ($('.dash-inner').length || $('.grid-post').length) {
        $('body').addClass('dashboard');
    }

    $('.morph-initial').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        $this.closest('.float-wrap').toggleClass('morphed');
        $this.find('i.fa').toggleClass('fa-plus fa-minus');
    });

    $(document).click(function(event) {
        if(!$(event.target).closest('.float-wrap').length) {
            if($('.controls-hidden').is(":visible")) {
                $('.float-wrap').toggleClass('morphed');
                $('.float-wrap').find('i.fa').toggleClass('fa-plus fa-minus');
            }
        }
    });

    // flat forms
    $('body').on('focus', '.form-element > input', function (e) {
        $(this).parent().addClass('field-focus');
    }).on('blur', '.form-element > input', function (e) {
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
        $('.tooltipped').tooltip({ container: 'body' });
    }

    var opened = 0;
    $('.page-tabs a[href="#data"]').on('show.bs.tab', function (e) {
        if(opened == 0) {
            //querySelection();
            opened = 1;
        }
    });

    $('.new-post-tabs a[href="#media"]').on('click', function() {
        var $this = $('.new-post-tabs a[href="#media"]')
        if (!($this.closest('li').hasClass('active'))) {
            $this.find('.fa').removeClass('fa-camera').addClass('fa-spin fa-spinner');
        }
    });

    $('.new-post-tabs a[href="#media"]').on('shown.bs.tab', function() {
        $(this).find('i.fa').removeClass('fa-spin fa-spinner').addClass('fa-camera');
    })

    $('header').on('show.bs.tab', '.media-tabs a[data-toggle="tab"]', function (e) {
        var tabHref = e.target.href;
        var realTabHref = tabHref.substr(tabHref.indexOf('#') + 1);
        var targetEl = $('#' + realTabHref);
        $('.tab-pane.active').html('');
        $.ajax({
            beforeSend: function () {
                $('.page-loader').show();
            },
            url: 'media/' + realTabHref
        }).done(function (data) {
            $('.page-loader').hide(0);
            targetEl.html(data);
        }).fail(function (xhr, msg) {
            logXhrError(msg, xhr);
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
                        $('.page-loader').show();
                        $('.tab-pane#credentials').html('');
                    },
                    url: 'credentials'
                }).done(function (html) {
                    $('.page-loader').hide(0);
                    $('.tab-pane#credentials').html(html);
                }).fail(function (xhr, msg) {
                    logXhrError(msg, xhr);
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
        $('.tab-pane.active').html('');

        var tabHref = e.target.href;
        var realTabHref = tabHref.substr(tabHref.indexOf('#') + 1);
        var contentUrl = $(this).attr('data-url');

        $.ajax({
            beforeSend: function () {
                $('.page-loader').show();
                $('.tab-wrap .tab-content .tab-pane').html('');
            },
            url: contentUrl
        }).done(function (html) {
            $('.page-loader').hide(0);
            $('#' + realTabHref).html(html);
            if(realTabHref == 'media' || realTabHref == 'settings' || realTabHref == 'services') {
                fileInputAction();
            }
            if(realTabHref == 'themes') {
                //bindButtons();
            }
            if(realTabHref == 'media') {
                $('#imageResampleQuality-slider').slider({
                    min: 10,
                    max: 100,
                    value: $('#media_settings_imageResampleQuality').val(),
                    slide: function(event, ui) {
                        $('#media_settings_imageResampleQuality').val(ui.value)
                    }
                });
            }
        }).fail(function (xhr, msg) {
            logXhrError(msg, xhr);
            var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
            $('body').append(flash);
            $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                $('.flash-error').remove();
            });
        });
    });

    $('.images-filter').on('click', function (e) {
        $('.images-dropdown').addClass('shown');

    });

    /*
    $('.images-dropdown a').on('click', function (e) {
        e.preventDefault();
        $(this).closest('li').addClass('active').siblings().removeClass('active');
        $('.images-dropdown').removeClass('shown');
        var text = $(this).html();
        $('.images-filter').html(text + ' <i class="fa fa-angle-down"></i>');
    });
    */

    $(document).click(function(event) {
        if(!$(event.target).closest('.images-dropdown').length && !$(event.target).closest('.header-title').length ) {
            if($('.images-dropdown').is(":visible")) {
                $('.images-dropdown').removeClass('shown');
            }
        }
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

                $.ajax({
                    url: $this.attr('href'),
                    beforeSend: function () {
                        $('.right-bar-loader').show();
                        $('.right-bar .bottom-header h4').html('');
                        $('.right-bar-content').html('');
                    }
                }).done(function (data) {
                    $('.right-bar-loader').hide(0);
                    $('.right-bar').html(data);
                    if ($('.tooltipped').length) {
                        $('.tooltipped').tooltip({ container: 'body' });
                    }
                }).fail(function (xhr, msg) {
                    logXhrError(msg, xhr);
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });
            } else {
                $this.closest('.item-wrap').addClass('active');
                activateRightBar();
                $.ajax({
                    url: $this.attr('href'),
                    beforeSend: function () {
                        $('.right-bar .bottom-header h4').html('');
                        $('.right-bar-content').html('');
                        $('.right-bar-loader').show();
                    }
                }).done(function (data) {
                    $('.right-bar-loader').hide(0);
                    $('.right-bar').html(data);
                    if ($('.tooltipped').length) {
                        $('.tooltipped').tooltip({ container: 'body' });
                    }
                }).fail(function (xhr, msg) {
                    logXhrError(msg, xhr);
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });
            }
        }
    });

    // right-bar handlers
    $('body').keydown(function (e) {
        if (e.keyCode == 27 && $('.right-bar').hasClass('active-right-bar')) {
            $('.right-bar .close-btn').click();
        }
    });

    $('.grid').on('click', '.modal-right-bar .close-btn', function (e) {
        e.preventDefault();
        $('.media-modal').modal('hide');
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

    $('.grid').on('click', '.upload-theme-btn', function (e) {
        e.preventDefault();
        $('.upload-modal').modal();
    });

    $('.grid').on('click', '.empty-msg .new-media', function (e) {
        e.preventDefault();
        $('.upload-btn').click();
    });

    $('.right-bar').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $('.confirm-modal').modal();
        $('.confirm-modal .modal-body').html("<p>Are you sure you would like to delete this item permanently?</p>");
        $('.confirm-modal .btn-confirm').off('click').on('click', function (e) {
            e.preventDefault();

            $.ajax({
                url: href,
                beforeSend: function () {
                    $('.page-loader').show();
                }
            }).done(function (data) {
                $('.page-loader').hide(0);
                deactivateRightBar();
                $('.grid-content').html(data);

            }).fail(function (xhr, msg) {
                logXhrError(msg, xhr);
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });
            $('.confirm-modal').modal('hide');
        });
    });

    $('.right-toolbar').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        $('.confirm-modal').modal();
        $('.confirm-modal .modal-body').html("<p>Are you sure you would like to delete the selected item(s) permanently?</p>");
        $('.confirm-modal .btn-confirm').off('click').on('click', function (e) {
            e.preventDefault();

            $.ajax({
                url: href,
                beforeSend: function () {
                    $('.page-loader').show();
                }
            }).done(function (data) {
                $('.page-loader').hide(0);
                window.location = baseURL + 'posts';
            }).fail(function (xhr, msg) {
                logXhrError(msg, xhr);
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });
            $('.confirm-modal').modal('hide');
        });
    });

    $('.grid').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if ( $(e.target).closest('.right-aux-toolbar').length > 0 ) {
            $('.confirm-modal').modal();
            $('.confirm-modal .modal-body').html("<p>Are you sure you would like to delete the selected item(s) permanently?</p>");
            $('.confirm-modal .btn-confirm').off('click').on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url: href,
                    beforeSend: function () {
                        $('.page-loader').show();
                    }
                }).done(function (data) {
                    $('.media-modal').modal('hide');
                    $('.confirm-modal').modal('hide');
                    $('.page-loader').hide(0);
                    $('.tab-pane.active').html(data)

                }).fail(function (xhr, msg) {
                    logXhrError(msg, xhr);
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });
            });
        } else {
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
                        $('.page-loader').show();
                    }
                }).done(function (data) {
                    $('.page-loader').hide(0);
                    $('.tab-pane.active').html(data)

                }).fail(function (xhr, msg) {
                    logXhrError(msg, xhr);
                    var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                    $('body').append(flash);
                    $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                        $('.flash-error').remove();
                    });
                });
                $('.thumbnail.active').closest('.removable').remove();
                $('.confirm-modal').modal('hide');
                $('.media-actions').hide();
            });
        }


    });

    $('.grid').on('click','.resample-btn', function (e) {
        e.preventDefault();
        $('.confirm-modal').find('.modal-body').html('<p>Are you sure you want to resample all media?</p>');
        $('.confirm-modal').find('.btn-confirm').text('Resample');
        $('.confirm-modal').modal();
    });

    $('.grid #media').on('click', '.thumbnail', function (e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('active')) {
            $this.removeClass('active');
            $this.find('.thumb-check').removeClass('active');
            $this.find('input').prop('checked', false);
        } else {
            $this.addClass('active');
            $this.find('.thumb-check').addClass('active');
            $this.find('input').prop('checked', true);
        }
    });

    $('.grid .media-content').on('click', '.thumbnail', function (e) {
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

        if($('#images').length || $('#videos').length) {
            $this = $(this);
            $.ajax({
                beforeSend: function () {
                },
                url: $this.find('a.view-link').attr('href')
            }).done(function (data) {
                $('.media-modal').html(data);
                if ($('#example_video_1').length) {
                    var mm = $('.media-modal').find('#example_video_1')[0];
                    videojs(mm, {"controls": true, "autoplay": false, "preload": "auto" });
                }
                $('.media-modal').modal({
                    backdrop: 'static'
                });
            }).fail(function (xhr, msg) {
                logXhrError(msg, xhr);
                var flash = '<div class="flash-error">Whoopsie, looks like something went wrong.</div>';
                $('body').append(flash);
                $('.flash-error').animate({opacity: 1}, 100).delay(3000).fadeOut(function () {
                    $('.flash-error').remove();
                });
            });
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
    fileInputAction();
});

$(window).load(function () {
    var State;
    $(function() {
        var History = window.History;
        if (!History.enabled) {
            return false;
        }
        var anchor;

        History.Adapter.bind(window, 'statechange', function() {
            State = History.getState();
        });

        $('.settings-tabs a').click(function(e) {
            anchor = baseURL + $(this).attr('data-url');
            e.preventDefault();
            History.pushState(null, $(this).text(), $(this).attr('data-url'));
        });

        var displayTab = function () {
            $('.settings-tabs a[data-url="' + anchor + '"]').click();
        };

        window.addEventListener("popstate", function(e) {
            var location1 = document.location.toString();
            anchor = location1.substr(location1.indexOf('13/') + 2);
            if (anchor.length) {
                displayTab(anchor);
            }
        });
    });
});

var resize_check = 0;
var rtime = new Date(1, 1, 2000, 12, 00, 00);
var timeout = false;
var delta = 200;

$(window).resize(function () {
    rtime = new Date();
    if (timeout === false) {
        timeout = true;
        setTimeout(resizeEnd, delta);
    }
});

function resizeEnd() {
    if (new Date() - rtime < delta) {
        setTimeout(resizeEnd, delta);
    } else {
        timeout = false;
        if ($('body').hasClass('modal-open') && $('.media-modal').hasClass('in')) {
            centerModal();
        }
    }
}
