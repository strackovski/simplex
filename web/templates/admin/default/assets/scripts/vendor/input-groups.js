$(document).ready(function () {
    $('.flat-control input[type="radio"]').on('change', function(e){

        $(this).closest('.flat-control').siblings('.flat-control').find('.flat-radio').removeClass('flat-radio-selected');


        $(this).next('.flat-radio').addClass('flat-radio-selected');
    });

    $('.flat-group input').focus(function (e) {
        $(this).parent().addClass('input-flat-group-focus');
    }).blur(function (e) {
            $(this).parent().removeClass('input-flat-group-focus');
        });

    $('.flat-control input[type="radio"]').on('change', function(e){

        $(this).closest('.flat-control').siblings().find('.flat-radio').removeClass('flat-radio-selected');

        $(this).next('.flat-radio').addClass('.flat-radio-selected');
    })

    $('.flat-control input[type="checkbox"]').on('change', function (e) {
        $(this).next('.flat-checkbox').toggleClass('flat-checkbox-selected');
    })

});