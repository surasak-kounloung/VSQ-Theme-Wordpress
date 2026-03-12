jQuery(document).ready(function($) {
    // Media Uploader
    var popupFrame;
    var popupCurrentButton;

    $(document).on('click', '.upload-popup-image', function(e) {
        e.preventDefault();
        popupCurrentButton = $(this);
        
        if (popupFrame) {
            popupFrame.open();
            return;
        }
        
        popupFrame = wp.media({
            title: 'Select Popup Image',
            multiple: false,
            library: { type: 'image' },
            button: { text: 'Use this Image' }
        });

        popupFrame.on('select', function() {
            var attachment = popupFrame.state().get('selection').first().toJSON();
            
            var container = popupCurrentButton.closest('.popup-field');
            var preview = container.find('.popup-image-preview');
            var inputId = container.find('.popup-image-id');
            
            inputId.val(attachment.id);
            preview.html('<img src="' + attachment.url + '">');
            popupCurrentButton.text('Change Image');
        });

        popupFrame.open();
    });
});
