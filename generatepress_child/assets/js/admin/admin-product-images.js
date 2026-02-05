jQuery(document).ready(function($) {

    // --- State Management ---
    var itemsPerPage = 18;
    var currentPage = 1;
    var currentFilter = '';
    var visibleRows = [];

    // --- Select2 Initialization ---
    function initSelect2(element) {
        element.select2({
            width: '100%',
            dropdownAutoWidth: true
        });
    }

    // Init existing selects
    initSelect2($('.category-select'));
    initSelect2($('#product-image-filter-category'));

    // --- Repeater Logic ---
    $('.dt-repeater-add').on('click', function(e) {
        e.preventDefault();
        var container = $('.dt-repeater-container');
        var index = container.find('.dt-repeater-row').length;
        var template = $('#dt-repeater-template').html();
        
        // Regex to replace {{index}}
        template = template.replace(/{{index}}/g, index);
        
        container.append(template);
        dt_update_indexes();
        
        // Init Select2 for new row
        var newRow = container.find('.dt-repeater-row').last();
        initSelect2(newRow.find('.category-select'));

        // Reset Filter and go to last page to see new item
        $('#product-image-filter-category').val('').trigger('change');
        currentPage = Math.ceil(visibleRows.length / itemsPerPage);
        renderPagination();
    });

    // Remove Row
    $(document).on('click', '.dt-remove-row', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to remove this row?')) {
            $(this).closest('.dt-repeater-row').remove();
            dt_update_indexes();
            
            // Re-calculate visible rows
            filterRows();
        }
    });

    // Toggle Row Content
    $(document).on('click', '.dt-toggle-row', function(e) {
        e.preventDefault();
        var row = $(this).closest('.dt-repeater-row');
        var content = row.find('.dt-row-content');
        var icon = $(this);

        content.toggleClass('hidden');
        
        if (content.hasClass('hidden')) {
            icon.removeClass('dashicons-minus').addClass('dashicons-plus');
        } else {
            icon.removeClass('dashicons-plus').addClass('dashicons-minus');
        }
    });

    // --- Filter & Pagination Logic ---

    // Initial Load
    filterRows();

    // Filter Change
    $('#product-image-filter-category').on('change', function() {
        currentFilter = $(this).val();
        currentPage = 1; // Reset to first page
        filterRows();
    });

    // Update Row Category on Select Change
    $(document).on('change', '.category-select', function() {
        var row = $(this).closest('.dt-repeater-row');
        row.attr('data-category', $(this).val());
        // Optional: Re-filter if current filter is active?
        // Usually better not to hide row immediately while editing, so we leave it visible until manual refresh or filter change.
    });

    // Pagination Click
    $('.first-page').on('click', function(e) { e.preventDefault(); if(!$(this).hasClass('disabled')) { currentPage = 1; renderPagination(); } });
    $('.prev-page').on('click', function(e) { e.preventDefault(); if(!$(this).hasClass('disabled')) { currentPage--; renderPagination(); } });
    $('.next-page').on('click', function(e) { e.preventDefault(); if(!$(this).hasClass('disabled')) { currentPage++; renderPagination(); } });
    $('.last-page').on('click', function(e) { e.preventDefault(); if(!$(this).hasClass('disabled')) { currentPage = Math.ceil(visibleRows.length / itemsPerPage); renderPagination(); } });


    function filterRows() {
        var allRows = $('.dt-repeater-container .dt-repeater-row');
        visibleRows = [];

        allRows.each(function() {
            var row = $(this);
            var cat = row.attr('data-category');
            
            if (currentFilter === '' || cat === currentFilter) {
                visibleRows.push(row);
            } else {
                row.hide();
            }
        });

        // Show "No items" message if empty
        if (visibleRows.length === 0) {
            $('.dt-no-items').show();
        } else {
            $('.dt-no-items').hide();
        }

        renderPagination();
    }

    function renderPagination() {
        var totalItems = visibleRows.length;
        var totalPages = Math.ceil(totalItems / itemsPerPage);
        
        // Clamp current page
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages || 1;

        // Update UI Text
        $('#total-items').text(totalItems);
        $('.current-page').text(currentPage);
        $('.total-pages').text(totalPages);

        // Enable/Disable Buttons
        if (currentPage <= 1) {
            $('.first-page, .prev-page').addClass('disabled');
        } else {
            $('.first-page, .prev-page').removeClass('disabled');
        }

        if (currentPage >= totalPages) {
            $('.next-page, .last-page').addClass('disabled');
        } else {
            $('.next-page, .last-page').removeClass('disabled');
        }

        // Show/Hide Rows based on Page
        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;

        // Hide all first (already handled by filter, but need to hide non-page items)
        // Actually, filterRows only pushed to array, we didn't hide them all yet if they matched filter
        // So let's loop visibleRows and show/hide
        
        $.each(visibleRows, function(i, row) {
            if (i >= startIndex && i < endIndex) {
                row.show();
            } else {
                row.hide();
            }
        });
    }


    // --- Media Uploader ---
    var file_frame;
    var current_button;

    $(document).on('click', '.upload-image-button', function(e) {
        e.preventDefault();
        current_button = $(this);
        var row = current_button.closest('.dt-field-col');

        if (file_frame) {
            file_frame.open();
            return;
        }

        file_frame = wp.media.frames.file_frame = wp.media({
            title: 'Select Product Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        file_frame.on('select', function() {
            var attachment = file_frame.state().get('selection').first().toJSON();
            var currentRow = current_button.closest('.dt-field-col');
            currentRow.find('.image-url-field').val(attachment.url);
            currentRow.find('.image-id-field').val(attachment.id);
            currentRow.find('.product-image-preview').attr('src', attachment.url).show();
            current_button = null;
        });

        file_frame.open();
    });

    $(document).on('click', '.remove-image-button', function(e) {
        e.preventDefault();
        var row = $(this).closest('.dt-field-col');
        row.find('.image-url-field').val('');
        row.find('.image-id-field').val('');
        row.find('.product-image-preview').attr('src', '').hide();
    });


    // Update Indexes
    function dt_update_indexes() {
        $('.dt-repeater-container .dt-repeater-row').each(function(i) {
            var row = $(this);
            // Update Input Names
            row.find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var newName = name.replace(/\[items\]\[\d+\]/, '[items][' + i + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // Toggle Publish Box
    $('.handlediv').on('click', function() {
        $(this).closest('.postbox').toggleClass('closed');
    });

    // --- Validation: Check duplicate shortcode names ---
    // $('#submit').on('click', function(e) {
    //     var inputs = $('.dt-repeater-container input[name$="[shortcode_name]"]');
    //     var values = {};
    //     var hasDuplicate = false;

    //     // Reset styles
    //     inputs.css('border', '');

    //     inputs.each(function() {
    //         var val = $(this).val().trim();
    //         if(val !== '') {
    //             if(values[val]) {
    //                 hasDuplicate = true;
    //                 // Highlight current duplicate
    //                 $(this).css('border', '1px solid red');
    //                 // Highlight the first occurrence
    //                 values[val].css('border', '1px solid red'); 
    //             } else {
    //                 values[val] = $(this);
    //             }
    //         }
    //     });

    //     if(hasDuplicate) {
    //         e.preventDefault();
    //         alert('พบชื่อ Shortcode ซ้ำกัน กรุณาแก้ไขก่อนบันทึก (Duplicate Shortcode Names found)');
    //         // Focus first error
    //         $('input[name$="[shortcode_name]"][style*="red"]').first().focus();
    //         return false;
    //     }
    // });

});
