jQuery(document).ready(function($) {
    // Media Uploader Logic
    
    $(document).on('click', '.sb-upload-image', function(e) {
        e.preventDefault();
        var button = $(this);
        var wrapper = button.closest('.sb-image-preview-wrapper');
        var inputField = wrapper.find('.sb-image-url');
        var idField = wrapper.find('.sb-image-id'); // ID field selector
        var previewDiv = wrapper.find('.sb-image-preview');
        var removeBtn = wrapper.find('.sb-remove-image');

        // Create the media frame.
        var mediaUploader = wp.media({
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
        var newRow = $(template);
        container.append(newRow);
        sb_update_indexes();
        
        // Init sortable for the new row's slides
        sb_init_slide_sortable(newRow.find('.sb-slides-list'));
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

    // Toggle Type Promotion (Slides vs Single)
    $(document).on('change', '.sb-type-promotion-checkbox', function() {
        var isChecked = $(this).is(':checked');
        var row = $(this).closest('.sb-row-columns');
        if(isChecked) {
            // Show Slides Group
            row.find('.sb-single-image-group').hide();
            row.find('.sb-slides-group').show();

            // Clear Single Image Data
            var singleGroup = row.find('.sb-single-image-group');
            singleGroup.find('input').val('');
            singleGroup.find('.sb-image-preview').empty();
            singleGroup.find('.sb-upload-image').show();
            singleGroup.find('.sb-remove-image').hide();

        } else {
            // Show Single Image Group
            row.find('.sb-single-image-group').show();
            row.find('.sb-slides-group').hide();

            // Clear Slides Data
            row.find('.sb-slides-list').empty();
        }
    });

    // Add Slide Logic
    $(document).on('click', '.sb-add-slide', function(e) {
        e.preventDefault();
        var list = $(this).prev('.sb-slides-list');
        // Find the base name from the checkbox in this row
        // Expected format: promotion_data[INDEX][type_promotion]
        var checkbox = list.closest('.sb-repeater-row').find('.sb-type-promotion-checkbox');
        var baseName = checkbox.attr('name').replace('[type_promotion]', '');
        
        var uniqueKey = Date.now(); // Use timestamp for unique inner index
        
        var html = '<div class="sb-slide-item" style="border: 1px dashed #ccc; padding: 10px; position: relative;">' +
                   '<span class="sb-slide-handle dashicons dashicons-menu"></span>' +
                   '<span class="sb-remove-slide-item dashicons dashicons-no-alt" style="position: absolute; top: 5px; right: 5px; width: 30px; height: 30px; font-size: 30px; cursor: pointer; color: #a00;" title="Remove Slide"></span>' +
                   '<div class="sb-image-preview-wrapper">' +
                   '<div class="sb-image-preview"></div>' +
                   '<input type="hidden" class="sb-image-url" name="' + baseName + '[slides_promotion][' + uniqueKey + '][image]">' +
                   '<input type="hidden" class="sb-image-id" name="' + baseName + '[slides_promotion][' + uniqueKey + '][id]">' +
                   '<button class="button sb-upload-image">Select Image</button>' +
                   '<button class="button sb-remove-image" style="display:none;">Remove Image</button>' +
                   '</div></div>';
        
        list.append(html);
        sb_update_slide_indexes(list.closest('.sb-repeater-row')); // Update to ensure order
    });

    // Remove Slide Logic
    $(document).on('click', '.sb-remove-slide-item', function() {
        if(confirm('Remove this slide?')) {
            var row = $(this).closest('.sb-repeater-row');
            $(this).closest('.sb-slide-item').remove();
            sb_update_slide_indexes(row);
        }
    });

    // Init Inner Sortable
    function sb_init_slide_sortable(elements) {
        if ($.fn.sortable) {
            elements.sortable({
                handle: '.sb-slide-handle',
                placeholder: 'ui-sortable-placeholder',
                forcePlaceholderSize: true,
                update: function(event, ui) {
                    var row = ui.item.closest('.sb-repeater-row');
                    sb_update_slide_indexes(row);
                }
            });
        }
    }

    // Initialize on ready
    sb_init_slide_sortable($('.sb-slides-list'));

    // Helper: Update Slide Indexes
    function sb_update_slide_indexes(row) {
        row.find('.sb-slides-list .sb-slide-item').each(function(index) {
            var item = $(this);
            // We need to update the middle key: promotion_data[ROW][slides_promotion][KEY][image]
            // Regex to find [slides_promotion][ANYTHING]
            
            item.find('input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    // Replace the key inside [slides_promotion][KEY] with index
                    // Matches: [slides_promotion] followed by [something]
                    var newName = name.replace(/(\[slides_promotion\])\[[^\]]+\]/, '$1[' + index + ']');
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
