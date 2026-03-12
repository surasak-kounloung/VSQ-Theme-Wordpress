jQuery(document).ready(function($) {
    // Media Uploader Logic
    var mediaUploader;
    var currentButton;
    
    $(document).on('click', '.al-upload-image', function(e) {
        e.preventDefault();
        currentButton = $(this);

        // Reuse Media Frame Logic
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
            var wrapper = currentButton.closest('.al-image-preview-wrapper');
            var inputField = wrapper.find('.al-image-url');
            var idField = wrapper.find('.al-image-id');
            var previewDiv = wrapper.find('.al-image-preview');
            var removeBtn = wrapper.find('.al-remove-image');
            
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

    $(document).on('click', '.al-remove-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var wrapper = button.closest('.al-image-preview-wrapper');
        var inputField = wrapper.find('.al-image-url');
        var idField = wrapper.find('.al-image-id');
        var previewDiv = wrapper.find('.al-image-preview');
        var uploadBtn = wrapper.find('.al-upload-image');

        inputField.val('');
        if(idField.length) {
            idField.val('');
        }
        
        previewDiv.html('');
        button.hide();
        uploadBtn.show();
    });

    // Repeater Logic
    $('.al-repeater-add').on('click', function(e) {
        e.preventDefault();
        var container = $('.al-repeater-container');
        var index = container.find('.al-repeater-row').length;
        var template = $('#al-repeater-template').html().replace(/{{index}}/g, index);
        container.append(template);
        al_update_indexes();
    });

    $(document).on('click', '.al-remove-row', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to remove this item?')) {
            $(this).closest('.al-repeater-row').remove();
            al_update_indexes();
        }
    });

    // Sortable
    if ($.fn.sortable) {
        $('.al-repeater-container').sortable({
            handle: '.al-row-header',
            placeholder: 'ui-sortable-placeholder',
            forcePlaceholderSize: true,
            update: function(event, ui) {
                al_update_indexes();
            }
        });
    }

    // Helper: Update Row Numbers and Input Names
    function al_update_indexes() {
        $('.al-repeater-container .al-repeater-row').each(function(i) {
            var row = $(this);
            
            // Update Row Number
            row.find('.al-row-number').text(i + 1);

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

    // Toggle Publish Box
    $('.handlediv').on('click', function() {
        $(this).closest('.postbox').toggleClass('closed');
    });
});
