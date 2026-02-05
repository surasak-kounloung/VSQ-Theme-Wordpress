jQuery(document).ready(function($) {

    // Repeater Logic
    $('.dt-repeater-add').on('click', function(e) {
        e.preventDefault();
        var container = $('.dt-repeater-container');
        var index = container.find('.dt-repeater-row').length;
        var template = $('#dt-repeater-template').html();
        
        // Simple replace for index might be risky if we use {{index}} inside value attributes that might contain similar strings, 
        // but for empty template it's fine.
        // We will use a regex to replace index placeholders.
        template = template.replace(/{{index}}/g, index);
        
        container.append(template);
        dt_update_indexes();
    });

    // Remove Row
    $(document).on('click', '.dt-remove-row', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to remove this row?')) {
            $(this).closest('.dt-repeater-row').remove();
            dt_update_indexes();
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

    // Sortable
    if ($.fn.sortable) {
        $('.dt-repeater-container').sortable({
            handle: '.dt-row-header', 
            placeholder: 'ui-sortable-placeholder',
            forcePlaceholderSize: true,
            update: function(event, ui) {
                dt_update_indexes();
            }
        });
    }

    // Update Indexes
    function dt_update_indexes() {
        $('.dt-repeater-container .dt-repeater-row').each(function(i) {
            var row = $(this);
            
            // Update Row Number (if we displayed it, but we might just use the handle)
            // row.find('.dt-row-number').text(i + 1);

            // Update Input Names
            row.find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    // Regex to replace the first index [0], [1] etc.
                    // Assumes format dt_doctors_data[body_list][INDEX][field]
                    var newName = name.replace(/\[body_list\]\[\d+\]/, '[body_list][' + i + ']');
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

