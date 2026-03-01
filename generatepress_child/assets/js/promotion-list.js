document.addEventListener('DOMContentLoaded', function() {
	var elem = document.querySelector('.promotion-list-slides');
	if (elem) {
		var options = {
			cellAlign: 'left',
			contain: true,
			lazyLoad: true,
			wrapAround: true, 
			pageDots: true,
			prevNextButtons: true,
			autoPlay: 6000,
			setGallerySize: false,
		};
		var flkty = new Flickity( elem, options );
	}
});