jQuery(document).ready(function($) {
    // Media Uploader
    $(document).on('click', '.upload-popup-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var container = button.closest('.popup-field');
        var preview = container.find('.popup-image-preview');
        var inputId = container.find('.popup-image-id');
        
        var frame = wp.media({
            title: 'Select Popup Image',
            multiple: false,
            library: { type: 'image' },
            button: { text: 'Use this Image' }
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            inputId.val(attachment.id);
            preview.html('<img src="' + attachment.url + '">');
            button.text('Change Image');
        });

        frame.open();
    });
});
