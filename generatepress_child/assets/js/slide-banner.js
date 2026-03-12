document.addEventListener('DOMContentLoaded', function() {
	var elem = document.querySelector('.slide-banner-list');
	if (elem) {
		var options = {
			cellAlign: 'left',
			contain: true,
			lazyLoad: true,
			wrapAround: true, 
			pageDots: true,
			prevNextButtons: false,
			autoPlay: 6000,
			setGallerySize: false,
		};
		var flkty = new Flickity( elem, options );
	}
});