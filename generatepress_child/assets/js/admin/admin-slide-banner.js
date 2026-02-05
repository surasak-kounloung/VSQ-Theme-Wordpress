jQuery(document).ready(function($) {
    // Media Uploader Logic
    var mediaUploader;
    
    $(document).on('click', '.sb-upload-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var wrapper = button.closest('.sb-image-preview-wrapper');
        var inputField = wrapper.find('.sb-image-url');
        var idField = wrapper.find('.sb-image-id'); // ID field selector
        var previewDiv = wrapper.find('.sb-image-preview');
        var removeBtn = wrapper.find('.sb-remove-image');

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
            
            inputField.val(attachment.url); // Save URL
            if(idField.length) {
                idField.val(attachment.id); // Save ID
            }
            
            previewDiv.html('<img src="' + attachment.url + '">');
            
            button.hide();
            removeBtn.show();
        });

        // Finally, open the modal
        mediaUploader.open();
    });

    $(document).on('click', '.sb-remove-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var wrapper = button.closest('.sb-image-preview-wrapper');
        var inputField = wrapper.find('.sb-image-url');
        var idField = wrapper.find('.sb-image-id');
        var previewDiv = wrapper.find('.sb-image-preview');
        var uploadBtn = wrapper.find('.sb-upload-image');

        inputField.val('');
        if(idField.length) {
            idField.val('');
        }
        
        previewDiv.html('');
        button.hide();
        uploadBtn.show();
    });

    // Repeater Logic
    $('.sb-repeater-add').on('click', function(e) {
        e.preventDefault();
        var container = $('.sb-repeater-container');
        var index = container.find('.sb-repeater-row').length;
        var template = $('#sb-repeater-template').html().replace(/{{index}}/g, index);
        container.append(template);
        sb_update_indexes();
    });

    $(document).on('click', '.sb-remove-row', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to remove this slide?')) {
            $(this).closest('.sb-repeater-row').remove();
            sb_update_indexes();
        }
    });

    // Sortable
    if ($.fn.sortable) {
        $('.sb-repeater-container').sortable({
            handle: '.sb-row-header', // Drag handle is the left column
            placeholder: 'ui-sortable-placeholder',
            forcePlaceholderSize: true,
            update: function(event, ui) {
                sb_update_indexes();
            }
        });
    }

    // Helper: Update Row Numbers and Input Names
    function sb_update_indexes() {
        $('.sb-repeater-container .sb-repeater-row').each(function(i) {
            var row = $(this);
            
            // Update Row Number
            row.find('.sb-row-number').text(i + 1);

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
