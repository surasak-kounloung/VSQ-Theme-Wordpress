jQuery(document).ready(function($) {

    var tablePrices = $('.wp-list-table');
    var tablePricesPostion = tablePrices.offset().top;
    var adminbar = $('#wpadminbar').height();
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        var sticky = tablePricesPostion - adminbar;
        if (scrollTop > sticky) { 
            tablePrices.addClass('sticky');
        } else {
            tablePrices.removeClass('sticky');
        }
    });

});
