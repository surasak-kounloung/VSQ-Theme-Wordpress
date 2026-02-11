jQuery(document).ready(function($) {

    var tablePrices = $('.wp-list-table');
    var tablePricesHead = $('.wp-list-table-head');
    var tablePricesBody = $('.wp-list-table-body');
    var tablePricesPostion = tablePrices.offset().top;
    var adminbar = $('#wpadminbar').height();
    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        var sticky = tablePricesPostion - adminbar;
        if (scrollTop > sticky) { 
            tablePricesHead.addClass('sticky');
            tablePricesBody.addClass('sticky');
        } else {
            tablePricesHead.removeClass('sticky');
            tablePricesBody.removeClass('sticky');
        }
    });

});
