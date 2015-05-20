$(document).ready(function () {
    $("form").each(function() {
        $(this).validate({
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    // arr, $form, options
                    beforeSubmit: function() {
                        $(form).addClass('fadeOutScale');
                        $(form).parent().find('.form-error-box').addClass('fadeInScale');
                    },
                    error: function() {
                        $(form).parent().find('.form-loader').animate({
                            opacity: 0
                        }, 200, function () {
                            $(form).parent().find('.form-loader').remove();
                            $(form).parent().find('.form-error-box').html('<p>AN ERROR OCCURED</p>');
                            setTimeout(function(){
                                $(form).parent().find('.form-error-box').removeClass('fadeInScale').addClass('fadeOutScale');
                                $(form).removeClass('fadeOutScale').addClass('fadeInScale');
                            }, 3000);
                        });
                    },
                    success: function() {
                        $(form).resetForm();
                        $(form).parent().find('.form-loader').animate({
                            opacity: 0
                        }, 200, function () {
                            $(form).parent().find('.form-loader').remove();
                            $(form).parent().find('.form-error-box').html('<p>FORM SENT</p>');
                        });
                    }
                });
            },
            // element, errorClass
            highlight: function(element) {
                $(element).addClass('error');
            }
        });
    });
});

