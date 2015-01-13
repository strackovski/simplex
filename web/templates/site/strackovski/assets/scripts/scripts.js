
var slider_contents;
var slider_wrap;


$(document).ready(function(){

    $('body').removeClass('nojs');
    setTimeout(function(){
        $('.thumbnail').css('opacity', 1);
        $('.footer').css('opacity', 1);
        $('.slides-wrap').css('opacity', 1);
        $('.spinner-wrap').fadeOut(200);
    }, 850);


    $('.popover-link').popover({
        placement: 'top'
    });

    $('#myModal').on('shown.bs.modal', function(){
        centerModal();
    });

    $('.modal').keydown(function (e) {
        if (e.keyCode == 37) {
            $('.modal').find('.modal-prev').trigger('click');
        }else if (e.keyCode == 39) {
            $('.modal .modal-next').trigger('click');
        }

    });

    var currentElement;
    $('.preview').on('click', function(){
        var src = $(this).parent('.block').find('img').attr('src');
        currentElement = $(this);
        $('.modal-body').html('<img src="'+src+'" alt="img" />');
        if(currentElement.attr('data-title') == '' || currentElement.attr('data-title') == undefined) {
            console.log(currentElement.attr('data-title'));
            $('.modal-footer').find('.title').html('/');
        } else {
            $('.modal-footer').find('.title').html(currentElement.attr('data-title'));
        }
        $('#myModal').modal();
    });

    $('.modal-next').on('click', function(){
        currentElement = currentElement.parents('.block').next().find('.preview');
        var src = currentElement.parents('.block').find('img').attr('src');

        if(src == undefined || src == '') {
            currentElement = $('.tiles .block').first().find('.preview');
            src = currentElement.parents('.block').find('img').attr('src');
        }
        $('.modal-body').html('<img src="'+src+'" alt="img" />');
        if(currentElement.attr('data-title') == '' || currentElement.attr('data-title') == undefined) {
            console.log(currentElement.attr('data-title'));
            $('.modal-footer').find('.title').html('/');
        } else {
            $('.modal-footer').find('.title').html(currentElement.attr('data-title'));
        }
        //$('.modal-content').css('margin-top', '0px');
        centerModal();
    });

    $('.modal-prev').on('click', function(){
        currentElement = currentElement.parents('.block').prev().find('.preview');
        var src = currentElement.parents('.block').find('img').attr('src');

        if (src == undefined || src == '') {
            currentElement = $('.tiles .block').last().find('.preview');
            src = currentElement.parents('.block').find('img').attr('src');
        }
        $('.modal-body').html('<img src="'+src+'" alt="img" />');
        if(currentElement.attr('data-title') == '' || currentElement.attr('data-title') == undefined) {
            console.log(currentElement.attr('data-title'));
            $('.modal-footer').find('.title').html('/');
        } else {
            $('.modal-footer').find('.title').html(currentElement.attr('data-title'));
        }
        centerModal();

    });

    // Make sure all images are loaded before calling setUpBlocks()
    /*
     var img = $('.block img');
     var imgLen = img.length;
     $.each(img,function(){
     $(this).load(function(){
     imgLen--;
     if(imgLen == 0) {
     setUpBlocks();
     }
     })
     });
     */

    /*
     if ($('.pages').length) {
     $('.pages').onepage_scroll({
     pagination: false
     });

     $('.scd-link').on('click', function(e) {
     e.preventDefault();
     $('.pages').moveDown();
     });
     $('.scu-link').on('click', function(e) {
     e.preventDefault();
     $('.pages').moveUp();
     })
     }
     */

    if ($('.ld-slider').length) {
        slider_contents = $('.ld-slider').contents();
        slider_wrap = $('.ld-slider').bxSlider({
            slideWidth: 500,
            minSlides: 2,
            maxSlides: 2,
            /*  onSlideBefore: function($slideElement,oldIndex,newIndex) {
             // $slideElement.animate({'opacity': 0.2});

             console.log('onSlideBefore');
             console.log('oldIndex: ' + oldIndex);

             if(oldIndex == 1) {
             console.log('old index = 1')
             $slideElement.next().next().next().next().find('img').removeClass('scaled');

             }
             if (oldIndex == 0) {
             console.log('old index = 0')
             $slideElement.find('img').removeClass('scaled');

             }

             },
             onSlideAfter:function($slideElement, oldIndex, newIndex) {
             console.log('onSlideAfter');
             console.log('oldindex: ' + oldIndex);
             //$slideElement.animate({'opacity': 1});
             if(oldIndex == 0) {
             console.log('RUN 0');
             $slideElement.next().next().next().find('img').addClass('scaled');

             } else if(oldIndex == 1) {
             console.log('RUN 1')
             $slideElement.next().find('img').addClass('scaled');

             }

             },     */
            slideMargin: 10,
            pager: false,
            auto: true,
            easing: 'ease-in-out'
        });
    }


    $('.newsletter-popup').hide();

    $('.newsletter').on('click', function(e){

        $('body').toggleClass('newsletter-active');
        $(this).find('i.fa').toggleClass('rotated');
        e.preventDefault();
        $('.newsletter-popup').slideToggle(200);
    });


    setTimeout(function(){
        $(window).trigger('resize');
    },30)
});

var resize_check = 0;
var rtime = new Date(1, 1, 2000, 12, 00, 00);
var timeout = false;
var delta = 200;

$(window).resize(function(){
    if($('.ld-slider').length) {
        if($(window).width() <= 991) {
            if(resize_check == 0) {
                slider_wrap.reloadSlider({
                    slideWidth: 350,
                    minSlides: 2,
                    maxSlides: 2,
                    slideMargin: 10,
                    pager: false
                });
                resize_check = 1;
            }

        }
        else if($(window).width() > 991 && resize_check == 1) {
            slider_wrap.reloadSlider({
                slideWidth: 600,
                minSlides: 3,
                maxSlides: 3,
                slideMargin: 10,
                pager: false
            });
            resize_check = 0;
        }
    }

    rtime = new Date();
    if (timeout === false) {
        timeout = true;
        setTimeout(resizeend, delta);
    }

    /*
     var fta = $('.fta .center');
     if(fta.length) {
     var ftaWidth = fta.width();
     var ftaHeight = fta.height();
     var ftaMargin;
     var ftaHeightMargin;

     ftaMargin = ftaWidth/2;
     ftaHeightMargin = ftaHeight/2;
     //fta.css({'margin-top': ftaHeightMargin/2 + 'px'});
     console.log('fta height: ' + ftaHeight)

     }
     */


    /*
     // Set .author and .work margin-left
     var author = $('.section.author .main');
     if (author.length) {
     var authorWidth = author.width();
     var authorHeight = author.height();
     var authorMargin;
     var authorHeightMargin;

     var work = $('.section.work .main');
     var workWidth = work.width();
     var workHeight = work.height();
     var workMargin;
     var workHeightMargin;

     authorMargin = authorWidth/2;
     authorHeightMargin = authorHeight/2;
     author.css({'margin-left': -authorMargin + 'px', 'margin-top': -authorHeightMargin + 'px'});

     workMargin = workWidth/2;
     workHeightMargin = workHeight/2;
     work.css({'margin-left': -workMargin + 'px', 'margin-top': -workHeightMargin + 'px'});
     }
     */

//    if(windowWi > author.width()) {
    //      author.css('margin-left', -((windowWi-author.width())/2) + 'px');
    //}


});
function resizeend() {
    var wrap = $('.slides-wrap');
    if (new Date() - rtime < delta) {
        setTimeout(resizeend, delta);
    } else {
        timeout = false;
        if($(window).height() > 680) {
            var h = $(window).height() - 200; //minus footer & nav height
            var m = (h - wrap.height()) / 2;
            wrap.css('margin-top', m + 'px');
        }
        else {
            wrap.removeAttr('style');
            wrap.css('opacity', 1);
        }
    }

    if($('body').hasClass('modal-open')) {
        centerModal();

    }
}

function centerModal() {
    var modalContentHeight = $('.modal-content').height() + 50;
    var windowHeight = $(window).height();
    var m = (windowHeight - modalContentHeight) / 2;
    $('.modal-content').css('margin-top', m);
}


