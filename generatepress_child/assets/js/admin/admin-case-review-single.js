jQuery(document).ready(function($) {
    
    // -------------------------------------------------------------------------
    // Case Review Thumbnail Video Uploader
    // -------------------------------------------------------------------------
    var imageFrame;
    var imageAddBtn = $('#case_review_add_thumbnail');
    var imageContainer = $('.thumbnail-image-preview');
    var imageInput = $('#case_review_thumbnail');

    imageAddBtn.on('click', function(e) {
        e.preventDefault();

        if ( imageFrame ) {
            imageFrame.open();
            return;
        }

        imageFrame = wp.media({
            title: 'Select Thumbnail Video',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageFrame.on( 'select', function() {
            var attachment = imageFrame.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            imageInput.val(attachmentId);
            
            // Update Preview
            var html = '<div class="admin-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 200px; width: auto;">';
            html += '<span class="admin-remove-image dashicons dashicons-no-alt thumbnail-remove-image"></span>';
            html += '</div>';
            
            imageContainer.html(html);
            imageAddBtn.text('Change Thumbnail');
        });

        imageFrame.open();
    });

    // Remove Thumbnail Video
    imageContainer.on('click', '.thumbnail-remove-image', function() {
        imageInput.val('');
        imageContainer.empty();
        imageAddBtn.text('Add Thumbnail');
    });


    // -------------------------------------------------------------------------
    // Case Review Image Before & After Uploader
    // -------------------------------------------------------------------------
    var imageFrameBeforeAfter;
    var imageAddBtnBeforeAfter = $('#case_review_add_image_before_after');
    var imageContainerBeforeAfter = $('.image-before-after-image-preview');
    var imageInputBeforeAfter = $('#case_review_image_before_after');

    imageAddBtnBeforeAfter.on('click', function(e) {
        e.preventDefault();

        if ( imageFrameBeforeAfter ) {
            imageFrameBeforeAfter.open();
            return;
        }

        imageFrameBeforeAfter = wp.media({
            title: 'Select Image Before & After',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageFrameBeforeAfter.on( 'select', function() {
            var attachment = imageFrameBeforeAfter.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            imageInputBeforeAfter.val(attachmentId);
            
            // Update Preview
            var html = '<div class="admin-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 200px; width: auto;">';
            html += '<span class="admin-remove-image dashicons dashicons-no-alt image-before-after-remove-image"></span>';
            html += '</div>';
            
            imageContainerBeforeAfter.html(html);
            imageAddBtnBeforeAfter.text('Change Image');
        });

        imageFrameBeforeAfter.open();
    });

    // Remove Image Before & After
    imageContainerBeforeAfter.on('click', '.image-before-after-remove-image', function() {
        imageInputBeforeAfter.val('');
        imageContainerBeforeAfter.empty();
        imageAddBtnBeforeAfter.text('Add Image');
    });


    // -------------------------------------------------------------------------
    // Procedures Table
    // -------------------------------------------------------------------------
    var proceduresBody = $('#procedures-table-body');
    var addProceduresBtn = $('#add-procedures-row');

    function updateProceduresIndexes() {
        proceduresBody.find('tr').each(function(index) {
            // $(this).find('.row-index').text(index + 1); // No longer displaying index number, using drag handle icon
            $(this).find('input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    addProceduresBtn.on('click', function(e) {
        e.preventDefault();
        var rowCount = proceduresBody.find('tr').length;
        var html = '<tr class="admin-table-row">';
        html += '<td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>';
        html += '<td><input type="text" name="case_review_procedures[' + rowCount + '][procedures]" value="" class="widefat"></td>';
        html += '<td><input type="text" name="case_review_procedures[' + rowCount + '][quantity]" value="" class="widefat"></td>';
        html += '<td><span class="remove-table-row dashicons dashicons-no-alt remove-procedures-row"></span></td>';
        html += '</tr>';
        proceduresBody.append(html);
    });

    proceduresBody.on('click', '.remove-procedures-row', function() {
        if (confirm('Are you sure you want to remove this row?')) {
            $(this).closest('tr').remove();
            updateProceduresIndexes();
        }
    });

    // Sortable Rows
    if (proceduresBody.length) {
        proceduresBody.sortable({
            handle: '.row-index', // Drag handle
            cursor: 'move',
            update: function(event, ui) {
                updateProceduresIndexes();
            }
        });
    }
    

    // -------------------------------------------------------------------------
    // Case Review Image Before Uploader
    // -------------------------------------------------------------------------
    var imageFrameBefore;
    var imageAddBtnBefore = $('#case_review_add_image_before');
    var imageContainerBefore = $('.image-before-image-preview');
    var imageInputBefore = $('#case_review_image_before');

    imageAddBtnBefore.on('click', function(e) {
        e.preventDefault();

        if ( imageFrameBefore ) {
            imageFrameBefore.open();
            return;
        }

        imageFrameBefore = wp.media({
            title: 'Select Image Before',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageFrameBefore.on( 'select', function() {
            var attachment = imageFrameBefore.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            imageInputBefore.val(attachmentId);
            
            // Update Preview
            var html = '<div class="admin-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 300px; width: auto;">';
            html += '<span class="admin-remove-image dashicons dashicons-no-alt image-before-remove-image"></span>';
            html += '</div>';
            
            imageContainerBefore.html(html);
            imageAddBtnBefore.text('Change Image');
        });

        imageFrameBefore.open();
    });

    // Remove Image Before
    imageContainerBefore.on('click', '.image-before-remove-image', function() {
        imageInputBefore.val('');
        imageContainerBefore.empty();
        imageAddBtnBefore.text('Add Image');
    });


    // -------------------------------------------------------------------------
    // Case Review Image After Uploader
    // -------------------------------------------------------------------------
    var imageFrameAfter;
    var imageAddBtnAfter = $('#case_review_add_image_after');
    var imageContainerAfter = $('.image-after-image-preview');
    var imageInputAfter = $('#case_review_image_after');

    imageAddBtnAfter.on('click', function(e) {
        e.preventDefault();

        if ( imageFrameAfter ) {
            imageFrameAfter.open();
            return;
        }

        imageFrameAfter = wp.media({
            title: 'Select Image After',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageFrameAfter.on( 'select', function() {
            var attachment = imageFrameAfter.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            imageInputAfter.val(attachmentId);
            
            // Update Preview
            var html = '<div class="admin-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 300px; width: auto;">';
            html += '<span class="admin-remove-image dashicons dashicons-no-alt image-after-remove-image"></span>';
            html += '</div>';
            
            imageContainerAfter.html(html);
            imageAddBtnAfter.text('Change Image');
        });

        imageFrameAfter.open();
    });

    // Remove Image After
    imageContainerAfter.on('click', '.image-after-remove-image', function() {
        imageInputAfter.val('');
        imageContainerAfter.empty();
        imageAddBtnAfter.text('Add Image');
    });

});
