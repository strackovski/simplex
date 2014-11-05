
// number of columns
var colCount = 0;

// width of columns
var colWidth = 0;

// window width
var windowWidth = 0;

// array to hold the height of all the columns
var colHeights = [];

function setupMediaGrid(element, numberOfCols) {


    // reset the array
    colHeights.length = 0;

    // get window width
    windowWidth = $(window).innerWidth();

    if (numberOfCols) {
        colCount = numberOfCols;
    }
    else {
        if (windowWidth < 600) {
            colCount = 2;
        } else if (windowWidth < 992) {
            colCount = 3;
        } else {
            colCount = 4;
        }
    }

    // get block width (all blocks have the same width)
    colWidth = element.outerWidth();

    // create elements with value of 20 (margin) in the colHeights[] array
    // create as many elements as there are columns
    // e.g. if there can be 3 columns, create 3 elements with value of 20
    for (var i = 0; i < colCount; i++) {
        colHeights.push(0);
    }
    positionmediagrid(element);
}
/*
navigator.sayswho= (function(){
    var ua= navigator.userAgent, tem,
        M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if(/trident/i.test(M[1])){
        tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
        return 'InternetExplorer '+(tem[1] || '');
    }
    if(M[1]=== 'Chrome'){
        tem= ua.match(/\bOPR\/(\d+)/)
        if(tem!= null) return 'Opera '+tem[1];
    }
    M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
    return M.join(' ');
})();*/

function positionmediagrid(element) {
    element.each(function(){


        // get the lowest value of an element in the colHeights[] array
        var min = Array.min(colHeights);

        // get the index of that element
        var index = $.inArray(min, colHeights);

        // calculate the needed position of the current block being processed
        // e. g. if the block is supposed to go in the 2nd column (index = 1): 20 + (1 * column_width + 20)
        var leftPos = index * (colWidth);

        $(this).css({
            '-webkit-transform': 'translate3d('+leftPos+'px, '+min+'px, 0px)',
            '-moz-transform' : 'translate3d('+leftPos+'px, '+min+'px, 0px)', /* Fx <16 */
        '-ms-transform': 'translate3d('+leftPos+'px, '+min+'px, 0px)', /* IE 9 */
        '-o-transform': 'translate3d('+leftPos+'px, '+min+'px, 0px)', /* Op <12.1 */
        'transform': 'translate3d('+leftPos+'px, '+min+'px, 0px)' /* IE 10, Fx 16+, Op 12.1+ */


        });

        // add the height of the current block to the appropriate element in the colHeights[] array and
        // add the margin, which acts as margin-bottom

        colHeights[index] = min + $(this).outerHeight();

        // get the highest number in the colHeights[] array and set it to the container (.tiles) element
        var max = Array.max(colHeights);
        element.parents('.tiles').height(max);
    })
}

// helpers to get the min and max values in an array
Array.min = function(array) {
    return Math.min.apply(Math, array);
};

Array.max = function(array) {
    return Math.max.apply(Math, array);
};
