jQuery(document).ready(function($) {
    
    // -------------------------------------------------------------------------
    // Branch Thumbnail Uploader
    // -------------------------------------------------------------------------
    var imageFrame;
    var imageAddBtn = $('#branch_add_thumbnail');
    var imageContainer = $('.thumbnail-image-preview');
    var imageInput = $('#branch_thumbnail');

    imageAddBtn.on('click', function(e) {
        e.preventDefault();

        if ( imageFrame ) {
            imageFrame.open();
            return;
        }

        imageFrame = wp.media({
            title: 'Select Thumbnail',
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

    // Remove Thumbnail
    imageContainer.on('click', '.thumbnail-remove-image', function() {
        imageInput.val('');
        imageContainer.empty();
        imageAddBtn.text('Add Thumbnail');
    });


    // -------------------------------------------------------------------------
    // Branch Thumbnail Name Uploader
    // -------------------------------------------------------------------------
    var imageFrameThumbnailName;
    var imageAddBtnThumbnailName = $('#branch_add_thumbnail_name');
    var imageContainerThumbnailName = $('.thumbnail-name-image-preview');
    var imageInputThumbnailName = $('#branch_thumbnail_name');

    imageAddBtnThumbnailName.on('click', function(e) {
        e.preventDefault();

        if ( imageFrameThumbnailName ) {
            imageFrameThumbnailName.open();
            return;
        }

        imageFrameThumbnailName = wp.media({
            title: 'Select Thumbnail',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageFrameThumbnailName.on( 'select', function() {
            var attachment = imageFrameThumbnailName.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            imageInputThumbnailName.val(attachmentId);
            
            // Update Preview
            var html = '<div class="admin-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 200px; width: auto;">';
            html += '<span class="admin-remove-image dashicons dashicons-no-alt thumbnail-name-remove-image"></span>';
            html += '</div>';
            
            imageContainerThumbnailName.html(html);
            imageAddBtnThumbnailName.text('Change Thumbnail');
        });

        imageFrameThumbnailName.open();
    });

    // Remove Thumbnail Name
    imageContainerThumbnailName.on('click', '.thumbnail-name-remove-image', function() {
        imageInputThumbnailName.val('');
        imageContainerThumbnailName.empty();
        imageAddBtnThumbnailName.text('Add Thumbnail');
    });
    

    // -------------------------------------------------------------------------
    // Branch Image 360 Uploader
    // -------------------------------------------------------------------------
    var imageFrameImage360;
    var imageAddBtnImage360 = $('#branch_add_image_360');
    var imageContainerImage360 = $('.image-360-preview');
    var imageInputImage360 = $('#branch_image_360');

    imageAddBtnImage360.on('click', function(e) {
        e.preventDefault();

        if ( imageFrameImage360 ) {
            imageFrameImage360.open();
            return;
        }

        imageFrameImage360 = wp.media({
            title: 'Select Image 360',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageFrameImage360.on( 'select', function() {
            var attachment = imageFrameImage360.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            imageInputImage360.val(attachmentId);
            
            // Update Preview
            var html = '<div class="admin-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 500px; width: auto;">';
            html += '<span class="admin-remove-image dashicons dashicons-no-alt image-360-remove-image"></span>';
            html += '</div>';
            
            imageContainerImage360.html(html);
            imageAddBtnImage360.text('Change Image');
        });

        imageFrameImage360.open();
    });

    // Remove Image 360
    imageContainerImage360.on('click', '.image-360-remove-image', function() {
        imageInputImage360.val('');
        imageContainerImage360.empty();
        imageAddBtnImage360.text('Add Image');
    });


    // -------------------------------------------------------------------------
    // Branch Location Image Uploader
    // -------------------------------------------------------------------------
    var imageFrameLocationImage;
    var imageAddBtnLocationImage = $('#branch_add_location_image');
    var imageContainerLocationImage = $('.location-image-preview');
    var imageInputLocationImage = $('#branch_location_image');

    imageAddBtnLocationImage.on('click', function(e) {
        e.preventDefault();

        if ( imageFrameLocationImage ) {
            imageFrameLocationImage.open();
            return;
        }

        imageFrameLocationImage = wp.media({
            title: 'Select Location Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        imageFrameLocationImage.on( 'select', function() {
            var attachment = imageFrameLocationImage.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            imageInputLocationImage.val(attachmentId);
            
            // Update Preview
            var html = '<div class="admin-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 500px; width: auto;">';
            html += '<span class="admin-remove-image dashicons dashicons-no-alt location-remove-image"></span>';
            html += '</div>';
            
            imageContainerLocationImage.html(html);
            imageAddBtnLocationImage.text('Change Image');
        });

        imageFrameLocationImage.open();
    });

    // Remove Location Image
    imageContainerLocationImage.on('click', '.location-remove-image', function() {
        imageInputLocationImage.val('');
        imageContainerLocationImage.empty();
        imageAddBtnLocationImage.text('Add Image');
    });


    // -------------------------------------------------------------------------
    // Opening Time Table
    // -------------------------------------------------------------------------
    var openingTimeBody = $('#opening-time-table-body');
    var addOpeningTimeBtn = $('#add-opening-time-row');

    function updateOpeningTimeIndexes() {
        openingTimeBody.find('tr').each(function(index) {
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

    addOpeningTimeBtn.on('click', function(e) {
        e.preventDefault();
        var rowCount = openingTimeBody.find('tr').length;
        var html = '<tr class="admin-table-row">';
        html += '<td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>';
        html += '<td><input type="text" name="branch_opening_time[' + rowCount + '][day]" value="" class="widefat"></td>';
        html += '<td><input type="text" name="branch_opening_time[' + rowCount + '][time]" value="" class="widefat"></td>';
        html += '<td><span class="remove-table-row dashicons dashicons-no-alt remove-opening-time-row"></span></td>';
        html += '</tr>';
        openingTimeBody.append(html);
    });

    openingTimeBody.on('click', '.remove-opening-time-row', function() {
        if (confirm('Are you sure you want to remove this row?')) {
            $(this).closest('tr').remove();
            updateOpeningTimeIndexes();
        }
    });

    // Sortable Rows
    if (openingTimeBody.length) {
        openingTimeBody.sortable({
            handle: '.row-index', // Drag handle
            cursor: 'move',
            update: function(event, ui) {
                updateOpeningTimeIndexes();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Services Table
    // -------------------------------------------------------------------------
    var servicesBody = $('#services-table-body');
    var addServicesBtn = $('#add-services-row');

    function updateServicesIndexes() {
        servicesBody.find('tr').each(function(index) {
            $(this).find('input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    addServicesBtn.on('click', function(e) {
        e.preventDefault();
        var rowCount = servicesBody.find('tr').length;
        var html = '<tr class="admin-table-row">';
        html += '<td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>';
        html += '<td><input type="text" name="branch_services[' + rowCount + '][name]" value="" class="widefat"></td>';
        html += '<td><span class="remove-table-row dashicons dashicons-no-alt remove-services-row"></span></td>';
        html += '</tr>';
        servicesBody.append(html);
    });

    servicesBody.on('click', '.remove-services-row', function() {
        if (confirm('Are you sure you want to remove this row?')) {
            $(this).closest('tr').remove();
            updateServicesIndexes();
        }
    });

    // Sortable Rows
    if (servicesBody.length) {
        servicesBody.sortable({
            handle: '.row-index', // Drag handle
            cursor: 'move',
            update: function(event, ui) {
                updateServicesIndexes();
            }
        });
    }

});