jQuery(document).ready(function($) {

    // -------------------------------------------------------------------------
    // Doctor Thumbnail Uploader
    // -------------------------------------------------------------------------
    var thumbnailFrame;
    var thumbnailAddBtn = $('#doctor_add_thumbnail');
    var thumbnailContainer = $('.doc-thumbnail-preview');
    var thumbnailInput = $('#doctor_thumbnail');

    thumbnailAddBtn.on('click', function(e) {
        e.preventDefault();

        if ( thumbnailFrame ) {
            thumbnailFrame.open();
            return;
        }

        thumbnailFrame = wp.media({
            title: 'Select Doctor Thumbnail',
            button: {
                text: 'Use this Thumbnail'
            },
            multiple: false
        });

        thumbnailFrame.on( 'select', function() {
            var attachment = thumbnailFrame.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            thumbnailInput.val(attachmentId);
            
            // Update Preview
            var html = '<div class="doc-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 250px; width: auto;">';
            html += '<span class="doc-remove-thumbnail dashicons dashicons-no-alt"></span>';
            html += '</div>';
            
            thumbnailContainer.html(html);
            thumbnailAddBtn.text('Change Thumbnail');
        });

        thumbnailFrame.open();
    });

    // Remove Doctor Thumbnail
    thumbnailContainer.on('click', '.doc-remove-thumbnail', function() {
        thumbnailInput.val('');
        thumbnailContainer.empty();
        thumbnailAddBtn.text('Add Thumbnail');
    });


    // -------------------------------------------------------------------------
    // Doctor Thumbnail Name Uploader
    // -------------------------------------------------------------------------
    var thumbnailNameFrame;
    var thumbnailNameAddBtn = $('#doctor_add_thumbnail_name');
    var thumbnailNameContainer = $('.doc-thumbnail-name-preview');
    var thumbnailNameInput = $('#doctor_thumbnail_name');

    thumbnailNameAddBtn.on('click', function(e) {
        e.preventDefault();

        if ( thumbnailNameFrame ) {
            thumbnailNameFrame.open();
            return;
        }

        thumbnailNameFrame = wp.media({
            title: 'Select Doctor Thumbnail',
            button: {
                text: 'Use this Thumbnail'
            },
            multiple: false
        });

        thumbnailNameFrame.on( 'select', function() {
            var attachment = thumbnailNameFrame.state().get('selection').first().toJSON();
            var attachmentId = attachment.id;
            var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

            // Update Input
            thumbnailNameInput.val(attachmentId);
            
            // Update Preview
            var html = '<div class="doc-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 250px; width: auto;">';
            html += '<span class="doc-remove-thumbnail-name dashicons dashicons-no-alt"></span>';
            html += '</div>';
            
            thumbnailNameContainer.html(html);
            thumbnailNameAddBtn.text('Change Thumbnail');
        });

        thumbnailNameFrame.open();
    });

    // Remove Doctor Thumbnail
    thumbnailNameContainer.on('click', '.doc-remove-thumbnail-name', function() {
        thumbnailNameInput.val('');
        thumbnailNameContainer.empty();
        thumbnailNameAddBtn.text('Add Thumbnail');
    });

    
    // -------------------------------------------------------------------------
    // Doctor Single Image Uploader
    // -------------------------------------------------------------------------
    var imageFrame;
    var imageAddBtn = $('#doctor_add_image');
    var imageContainer = $('.doc-image-preview');
    var imageInput = $('#doctor_image');

    imageAddBtn.on('click', function(e) {
        e.preventDefault();

        if ( imageFrame ) {
            imageFrame.open();
            return;
        }

        imageFrame = wp.media({
            title: 'Select Doctor Image',
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
            var html = '<div class="doc-image-item" data-id="'+attachmentId+'">';
            html += '<img src="'+url+'" style="max-height: 250px; width: auto;">';
            html += '<span class="doc-remove-image dashicons dashicons-no-alt"></span>';
            html += '</div>';
            
            imageContainer.html(html);
            imageAddBtn.text('Change Image');
        });

        imageFrame.open();
    });

    // Remove Doctor Image
    imageContainer.on('click', '.doc-remove-image', function() {
        imageInput.val('');
        imageContainer.empty();
        imageAddBtn.text('Add Image');
    });


    // -------------------------------------------------------------------------
    // Certificates Gallery Uploader
    // -------------------------------------------------------------------------
    var frame;
    var addBtn = $('#doctor_add_certificate');
    var container = $('.doc-certs-preview');
    var input = $('#doctor_certificate_gallery');

    // Initialize Sortable
    if ( container.length ) {
        container.sortable({
            items: '.doc-cert-item',
            cursor: 'move',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            opacity: 0.65,
            update: function(event, ui) {
                var ids = [];
                container.find('.doc-cert-item').each(function() {
                    ids.push($(this).data('id'));
                });
                input.val(ids.join(','));
            }
        });
    }

    addBtn.on('click', function(e) {
        e.preventDefault();

        // If the frame already exists, open it.
        if ( frame ) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select Certificate Gallery',
            button: {
                text: 'Add to Gallery'
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            var selection = frame.state().get('selection');
            var ids = input.val() ? input.val().split(',') : [];

            selection.map( function( attachment ) {
                attachment = attachment.toJSON();
                var attachmentId = String(attachment.id);

                // Check for duplicates
                if(ids.indexOf(attachmentId) === -1) {
                    ids.push(attachmentId);

                    // Append to preview
                    var url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    var html = '<div class="doc-cert-item" data-id="'+attachmentId+'">';
                    html += '<img src="'+url+'" style="max-height: 150px; width: auto;">';
                    html += '<span class="doc-remove-cert dashicons dashicons-no-alt"></span>';
                    html += '</div>';
                    container.append(html);
                }
            });

            // Clean up empty strings if any
            ids = ids.filter(function(e){return e}); 
            input.val(ids.join(','));
        });

        // Finally, open the modal on click
        frame.open();
    });

    // Remove Certificate Gallery Image
    container.on('click', '.doc-remove-cert', function() {
        var item = $(this).closest('.doc-cert-item');
        var id = String(item.data('id'));
        var ids = input.val() ? input.val().split(',') : [];

        // Remove id from array
        var index = ids.indexOf(id);
        if (index > -1) {
            ids.splice(index, 1);
        }

        ids = ids.filter(function(e){return e});
        input.val(ids.join(','));
        item.remove();
    });


    // -------------------------------------------------------------------------
    // Training Gallery Uploader
    // -------------------------------------------------------------------------
    var trainingFrame;
    var addBtnTraining = $('#doctor_add_training');
    var containerTraining = $('.doc-training-preview');
    var inputTraining = $('#doctor_training_gallery');

    // Initialize Sortable
    if ( containerTraining.length ) {
        containerTraining.sortable({
            items: '.doc-training-item',
            cursor: 'move',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            opacity: 0.65,
            update: function(event, ui) {
                var ids = [];
                containerTraining.find('.doc-training-item').each(function() {
                    ids.push($(this).data('id'));
                });
                inputTraining.val(ids.join(','));
            }
        });
    }

    addBtnTraining.on('click', function(e) {
        e.preventDefault();

        // If the frame already exists, open it.
        if ( trainingFrame ) {
            trainingFrame.open();
            return;
        }

        // Create a new media frame
        trainingFrame = wp.media({
            title: 'Select Training Gallery',
            button: {
                text: 'Add to Gallery'
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });

        // When an image is selected in the media frame...
        trainingFrame.on( 'select', function() {
            var selection = trainingFrame.state().get('selection');
            var ids = inputTraining.val() ? inputTraining.val().split(',') : [];

            selection.map( function( attachment ) {
                attachment = attachment.toJSON();
                var attachmentId = String(attachment.id);

                // Check for duplicates
                if(ids.indexOf(attachmentId) === -1) {
                    ids.push(attachmentId);

                    // Append to preview
                    var url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    var html = '<div class="doc-training-item" data-id="'+attachmentId+'">';
                    html += '<img src="'+url+'" style="max-height: 150px; width: auto;">';
                    html += '<span class="doc-remove-training dashicons dashicons-no-alt"></span>';
                    html += '</div>';
                    containerTraining.append(html);
                }
            });

            // Clean up empty strings if any
            ids = ids.filter(function(e){return e}); 
            inputTraining.val(ids.join(','));
        });

        // Finally, open the modal on click
        trainingFrame.open();
    });

    // Remove Training Gallery Image
    containerTraining.on('click', '.doc-remove-training', function() {
        var item = $(this).closest('.doc-training-item');
        var id = String(item.data('id'));
        var ids = inputTraining.val() ? inputTraining.val().split(',') : [];

        // Remove id from array
        var index = ids.indexOf(id);
        if (index > -1) {
            ids.splice(index, 1);
        }

        ids = ids.filter(function(e){return e});
        inputTraining.val(ids.join(','));
        item.remove();
    });


    // -------------------------------------------------------------------------
    // Schedule Table
    // -------------------------------------------------------------------------
    var scheduleBody = $('#doc-schedule-body');
    var addScheduleBtn = $('#add-schedule-row');

    function updateScheduleIndexes() {
        scheduleBody.find('tr').each(function(index) {
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

    addScheduleBtn.on('click', function(e) {
        e.preventDefault();
        var rowCount = scheduleBody.find('tr').length;
        var html = '<tr class="doc-schedule-row">';
        html += '<td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>';
        html += '<td><input type="text" name="doctor_schedule[' + rowCount + '][date]" value="" class="widefat"></td>';
        html += '<td><input type="text" name="doctor_schedule[' + rowCount + '][branch]" value="" class="widefat"></td>';
        html += '<td><span class="remove-schedule-row dashicons dashicons-no-alt"></span></td>';
        html += '</tr>';
        scheduleBody.append(html);
    });

    scheduleBody.on('click', '.remove-schedule-row', function() {
        if (confirm('Are you sure you want to remove this row?')) {
            $(this).closest('tr').remove();
            updateScheduleIndexes();
        }
    });

    // Sortable Rows
    if (scheduleBody.length) {
        scheduleBody.sortable({
            handle: '.row-index', // Drag handle
            cursor: 'move',
            update: function(event, ui) {
                updateScheduleIndexes();
            }
        });
    }


    // -------------------------------------------------------------------------
    // Case Review Search & Select
    // -------------------------------------------------------------------------
    var searchInput = $('#doctor_search_case_review');
    var searchResults = $('#doctor_case_review_search_results');
    var selectedContainer = $('.doc-case-review-selected');
    var hiddenInput = $('#doctor_case_reviews');
    var searchTimer;

    // Search
    searchInput.on('keyup', function() {
        var term = $(this).val();
        
        clearTimeout(searchTimer);
        
        if (term.length < 2) {
            searchResults.hide();
            return;
        }

        searchTimer = setTimeout(function() {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'doctor_search_case_reviews',
                    term: term
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        var html = '';
                        $.each(response.data, function(index, item) {
                            html += '<div class="doc-search-result-item" data-id="' + item.id + '" style="padding: 8px; cursor: pointer; border-bottom: 1px solid #eee;">' + item.title + '</div>';
                        });
                        searchResults.html(html).show();
                    } else {
                        searchResults.html('<div style="padding: 8px;">No results found</div>').show();
                    }
                }
            });
        }, 500);
    });

    // Add Item
    searchResults.on('click', '.doc-search-result-item', function() {
        var id = $(this).data('id');
        var title = $(this).text();
        
        // Check if already selected
        var currentIds = hiddenInput.val() ? hiddenInput.val().split(',') : [];
        if (currentIds.indexOf(String(id)) === -1) {
            currentIds.push(id);
            // remove empty string if any
            currentIds = currentIds.filter(function(e){return e});
            hiddenInput.val(currentIds.join(','));

            var html = '<div class="doc-case-review-item" data-id="' + id + '" style="display: flex; align-items: center; padding: 5px; background: #f0f0f0; margin-bottom: 5px; border: 1px solid #ddd;">';
            html += '<span class="dashicons dashicons-move" style="cursor: move; margin-right: 5px; color: #ccc;"></span>';
            html += '<span class="doc-case-review-title" style="flex-grow: 1;">' + title + '</span>';
            html += '<span class="doc-remove-case-review dashicons dashicons-no-alt" style="cursor: pointer; color: #d63638;"></span>';
            html += '</div>';
            
            selectedContainer.append(html);
        }
        
        searchInput.val('');
        searchResults.hide();
    });

    // Remove Item
    selectedContainer.on('click', '.doc-remove-case-review', function() {
        var item = $(this).closest('.doc-case-review-item');
        var id = String(item.data('id'));
        var currentIds = hiddenInput.val() ? hiddenInput.val().split(',') : [];
        
        var index = currentIds.indexOf(id);
        if (index > -1) {
            currentIds.splice(index, 1);
        }
        
        currentIds = currentIds.filter(function(e){return e});
        hiddenInput.val(currentIds.join(','));
        item.remove();
    });

    // Sortable
    if (selectedContainer.length) {
        selectedContainer.sortable({
            handle: '.dashicons-move',
            cursor: 'move',
            update: function(event, ui) {
                var ids = [];
                selectedContainer.find('.doc-case-review-item').each(function() {
                    ids.push($(this).data('id'));
                });
                hiddenInput.val(ids.join(','));
            }
        });
    }

    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.doc-case-review-search').length) {
            searchResults.hide();
        }
    });

});
