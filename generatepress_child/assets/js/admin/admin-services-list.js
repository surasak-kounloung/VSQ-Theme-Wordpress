jQuery(document).ready(function($) {
    // Media Uploader Logic
    var mediaUploader;
    var currentButton;
    
    $(document).on('click', '.sl-upload-image', function(e) {
        e.preventDefault();
        currentButton = $(this);

        // If the media frame already exists, reopen it.
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        // Create the media frame.
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select Image',
            button: {
                text: 'Select Image'
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Use currentButton to find the wrapper and fields
            var wrapper = currentButton.closest('.sl-image-preview-wrapper');
            var inputField = wrapper.find('.sl-image-url');
            var idField = wrapper.find('.sl-image-id'); // ID field selector
            var previewDiv = wrapper.find('.sl-image-preview');
            var removeBtn = wrapper.find('.sl-remove-image');
            
            inputField.val(attachment.url); // Save URL
            if(idField.length) {
                idField.val(attachment.id); // Save ID
            }
            
            previewDiv.html('<img src="' + attachment.url + '">');
            
            currentButton.hide();
            removeBtn.show();
        });

        // Finally, open the modal
        mediaUploader.open();
    });

    $(document).on('click', '.sl-remove-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var wrapper = button.closest('.sl-image-preview-wrapper');
        var inputField = wrapper.find('.sl-image-url');
        var idField = wrapper.find('.sl-image-id');
        var previewDiv = wrapper.find('.sl-image-preview');
        var uploadBtn = wrapper.find('.sl-upload-image');

        inputField.val('');
        if(idField.length) {
            idField.val('');
        }
        
        previewDiv.html('');
        button.hide();
        uploadBtn.show();
    });

    // Toggle URL Placeholder
    $(document).on('change', '.sl-external-url-checkbox', function() {
        var checkbox = $(this);
        // Navigate up to the column container that holds both fields
        var container = checkbox.closest('.sl-column');
        var urlInput = container.siblings('.sl-column').find('.sl-url-input'); // In case it's in sibling
        
        // Wait, in my PHP structure, they are in the SAME column div.
        // Structure: .sl-column > .sl-field > checkbox
        // Structure: .sl-column > .sl-field > input.sl-url-input
        
        // So we need to go up to column, then find the input.
        var column = checkbox.closest('.sl-column');
        var urlInput = column.find('.sl-url-input');

        if (checkbox.is(':checked')) {
            urlInput.attr('placeholder', 'https://...');
        } else {
            urlInput.attr('placeholder', '/service-path/');
        }
        // Optional: Clear value on toggle? Maybe not, user might want to keep it.
        // urlInput.val(''); 
    });

    // Repeater Logic
    $('.sl-repeater-add').on('click', function(e) {
        e.preventDefault();
        var container = $('.sl-repeater-container');
        var index = container.find('.sl-repeater-row').length;
        var template = $('#sl-repeater-template').html().replace(/{{index}}/g, index);
        container.append(template);
        sl_update_indexes();
    });

    $(document).on('click', '.sl-remove-row', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to remove this item?')) {
            $(this).closest('.sl-repeater-row').remove();
            sl_update_indexes();
        }
    });

    // Sortable
    if ($.fn.sortable) {
        $('.sl-repeater-container').sortable({
            handle: '.sl-row-header', // Drag handle is the left column
            placeholder: 'ui-sortable-placeholder',
            forcePlaceholderSize: true,
            update: function(event, ui) {
                sl_update_indexes();
            }
        });
    }

    // Helper: Update Row Numbers and Input Names
    function sl_update_indexes() {
        $('.sl-repeater-container .sl-repeater-row').each(function(i) {
            var row = $(this);
            
            // Update Row Number
            row.find('.sl-row-number').text(i + 1);

            // Update Input Names
            row.find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    // Replace [0], [1] etc with new index
                    var newName = name.replace(/\[\d+\]/, '[' + i + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // Toggle Publish Box (Native WP behavior simulation)
    $('.handlediv').on('click', function() {
        $(this).closest('.postbox').toggleClass('closed');
    });
});
