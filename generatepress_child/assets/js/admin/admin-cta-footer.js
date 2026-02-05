jQuery(document).ready(function($) {
    // Sortable
    $('.cta-list').sortable({
        handle: 'h3',
        placeholder: 'ui-state-highlight',
        update: function(event, ui) {
            // Optional: update indexes if needed
        }
    });

    // Color Picker Init Function
    function initColorPicker(parent) {
        parent.find('.color-field').wpColorPicker();
    }
    
    // Initialize existing
    initColorPicker($('.cta-list'));

    // Add Row
    $('.add-cta-row').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var lang = button.data('lang'); // Get language (th, en, cn)
        var container = button.closest('.cta-list-container').find('.cta-list');
        
        // Generate unique index based on timestamp
        var index = new Date().getTime();
        var template = $('#cta-row-template').html();
        
        // Replace Placeholders
        var newRow = template.replace(/{{index}}/g, index)
                             .replace(/{{lang}}/g, lang)
                             .replace(/{{LANG}}/g, lang.toUpperCase());
                             
        var rowObj = $(newRow);
        
        container.append(rowObj);
        
        // Initialize Color Picker for new row
        initColorPicker(rowObj);
    });

    // Remove Row
    $(document).on('click', '.cta-remove-row', function() {
        if(confirm('Are you sure you want to remove this button?')) {
            $(this).closest('.cta-row').remove();
        }
    });

    // Media Uploader
    $(document).on('click', '.upload-cta-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var container = button.closest('.cta-field');
        var preview = container.find('.cta-image-preview');
        var inputId = container.find('.cta-image-id');
        
        var frame = wp.media({
            title: 'Select Image',
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