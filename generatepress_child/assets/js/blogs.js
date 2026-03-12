// Before After Image
jQuery(function($) {
	$(document).ready(function() {
		if ($('.img-before-after .kt-row-column-wrap').length > 0) {
			$(window).on('load', function() {
				$(function(){
					$('.img-before-after .kt-row-column-wrap').twentytwenty({
						no_overlay: false,
						before_label: '',
						after_label: '',
					});
				});
			});
		}
	});
}(jQuery));
// End Before After Image