jQuery(document).ready(function($) {

    // --- State Management ---
    var itemsPerPage = 18;
    var currentPage = 1;
    var currentFilter = '';
    var currentSearch = '';
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

    // Copy Shortcode to Clipboard
    $(document).on('click', '.dt-shortcode-copy', function(e) {
        e.preventDefault();
        var btn = $(this);
        var text = btn.attr('data-clipboard-text') || '';
        if ( ! text ) return;

        var done = function() {
            var label = btn.find('.dt-shortcode-copy-label');
            var originalLabel = label.text();
            btn.addClass('is-copied');
            label.text('Copied!');
            setTimeout(function() {
                btn.removeClass('is-copied');
                label.text(originalLabel || 'Copy');
            }, 1500);
        };

        if ( navigator.clipboard && window.isSecureContext ) {
            navigator.clipboard.writeText(text).then(done).catch(function() {
                fallbackCopy(text, done);
            });
        } else {
            fallbackCopy(text, done);
        }
    });

    function fallbackCopy(text, cb) {
        var $tmp = $('<textarea>').val(text).css({position:'fixed', top:0, left:0, opacity:0}).appendTo('body');
        $tmp[0].select();
        try { document.execCommand('copy'); } catch (err) {}
        $tmp.remove();
        if (typeof cb === 'function') cb();
    }

    // ============================================
    // Find Usage Modal
    // ============================================
    var $modal = $('#dt-find-usage-modal');

    function openModal() {
        $modal.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('dt-modal-open');
    }

    function closeModal() {
        $modal.removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('dt-modal-open');
    }

    function setModalState(state) {
        $modal.find('.dt-modal-state').attr('hidden', true);
        $modal.find('.dt-modal-state--' + state).removeAttr('hidden');
    }

    // Open modal on button click
    $(document).on('click', '.dt-shortcode-find-usage', function(e) {
        e.preventDefault();
        var btn = $(this);
        var shortcodeName = btn.data('shortcodeName') || '';
        var itemId = btn.data('itemId') || '';
        var itemName = btn.data('itemName') || '';
        var $footer = btn.closest('.dt-shortcode-footer');

        if (!shortcodeName && !itemId) {
            return;
        }

        // Show item info in modal header
        var infoHtml = '<div class="dt-modal-tags">';
        if (itemName) {
            infoHtml += '<span class="dt-modal-tag dt-modal-tag--label">' + $('<div>').text(itemName).html() + '</span>';
        }
        if (shortcodeName) {
            infoHtml += '<span class="dt-modal-tag dt-modal-tag--name">name: ' + $('<div>').text(shortcodeName).html() + '</span>';
        }
        if (itemId) {
            infoHtml += '<span class="dt-modal-tag dt-modal-tag--id">id: ' + $('<div>').text(itemId).html() + '</span>';
        }
        infoHtml += '</div>';
        $modal.find('.dt-modal-item-info').html(infoHtml);

        openModal();
        setModalState('loading');

        // AJAX request
        $.post(productImageAdmin.ajaxUrl, {
            action: 'product_image_find_usages',
            _nonce: productImageAdmin.findUsagesNonce,
            shortcode_name: shortcodeName,
            item_id: itemId
        }).done(function(response) {
            if (!response || !response.success) {
                setModalState('error');
                return;
            }
            renderUsageResults(response.data);

            // Sync actual count back to badge
            var total = (response.data.results ? response.data.results.length : 0)
                      + (response.data.options ? response.data.options.length : 0);
            updateUsageBadge($footer, total);
        }).fail(function() {
            setModalState('error');
        });
    });

    function updateUsageBadge($footer, total) {
        if (!$footer || !$footer.length) return;
        var $badge = $footer.find('.dt-usage-count');
        if (!$badge.length) return;

        $badge.attr('data-count', total);
        var $num = $badge.find('.dt-usage-count-num');

        if (total > 0) {
            $badge.removeClass('is-zero').addClass('is-active');
            if ($num.length && $num.is('strong')) {
                $num.text(total);
            } else {
                $badge.html('<span class="dashicons dashicons-admin-links"></span> ใช้งาน <strong class="dt-usage-count-num">' + total + '</strong> ที่');
            }
        } else {
            $badge.removeClass('is-active').addClass('is-zero');
            $badge.html('<span class="dashicons dashicons-admin-links"></span> <span class="dt-usage-count-num">ยังไม่มีการใช้งาน</span>');
        }
    }

    function renderUsageResults(data) {
        var results = data.results || [];
        var options = data.options || [];
        var total = results.length + options.length;

        if (total === 0) {
            setModalState('empty');
            return;
        }

        // Summary
        var summaryHtml = 'พบการใช้งานทั้งหมด <strong>' + total + '</strong> รายการ';
        $modal.find('.dt-modal-summary').html(summaryHtml);

        // Group by post_type
        var grouped = {};
        results.forEach(function(r) {
            var pt = r.post_type || 'other';
            if (!grouped[pt]) grouped[pt] = { label: r.type_label || pt, items: [] };
            grouped[pt].items.push(r);
        });

        var listHtml = '';
        Object.keys(grouped).forEach(function(pt) {
            var g = grouped[pt];
            listHtml += '<li class="dt-usage-group">';
            listHtml += '<div class="dt-usage-group-title">' + escapeHtml(g.label) + ' <span class="dt-usage-count">(' + g.items.length + ')</span></div>';
            listHtml += '<ul class="dt-usage-sublist">';
            g.items.forEach(function(item) {
                listHtml += renderUsageItem(item);
            });
            listHtml += '</ul>';
            listHtml += '</li>';
        });

        // Options section
        if (options.length > 0) {
            listHtml += '<li class="dt-usage-group">';
            listHtml += '<div class="dt-usage-group-title">Widgets / Theme Options <span class="dt-usage-count">(' + options.length + ')</span></div>';
            listHtml += '<ul class="dt-usage-sublist">';
            options.forEach(function(opt) {
                listHtml += '<li class="dt-usage-item dt-usage-item--option">';
                listHtml += '<div class="dt-usage-item-main">';
                listHtml += '<span class="dashicons dashicons-admin-generic"></span>';
                listHtml += '<span class="dt-usage-title">' + escapeHtml(opt.option_name) + '</span>';
                listHtml += '</div>';
                listHtml += '</li>';
            });
            listHtml += '</ul>';
            listHtml += '</li>';
        }

        $modal.find('.dt-usage-list').html(listHtml);
        setModalState('results');
    }

    function renderUsageItem(item) {
        var statusBadge = '';
        if (item.status && item.status !== 'publish') {
            statusBadge = '<span class="dt-usage-status dt-usage-status--' + item.status + '">' + item.status + '</span>';
        }

        var html = '<li class="dt-usage-item">';
        html += '<div class="dt-usage-item-main">';
        html += '<span class="dashicons dashicons-admin-page"></span>';
        html += '<span class="dt-usage-title">' + escapeHtml(item.title) + '</span>';
        html += statusBadge;
        html += '</div>';
        html += '<div class="dt-usage-item-actions">';
        if (item.view_url) {
            html += '<a href="' + escapeAttr(item.view_url) + '" target="_blank" rel="noopener" class="button button-small button-view"><span class="dashicons dashicons-visibility"></span> ดูหน้า</a>';
        }
        if (item.edit_url) {
            html += '<a href="' + escapeAttr(item.edit_url) + '" target="_blank" rel="noopener" class="button button-small button-edit"><span class="dashicons dashicons-edit"></span> แก้ไข</a>';
        }
        html += '</div>';
        html += '</li>';
        return html;
    }

    function escapeHtml(str) {
        return $('<div>').text(String(str == null ? '' : str)).html();
    }

    function escapeAttr(str) {
        return String(str == null ? '' : str).replace(/"/g, '&quot;');
    }

    // Close modal handlers
    $(document).on('click', '[data-modal-close]', function(e) {
        e.preventDefault();
        closeModal();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $modal.hasClass('is-open')) {
            closeModal();
        }
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

    // Search Change
    $('#product-image-search').on('input', function() {
        currentSearch = $(this).val().toLowerCase();
        currentPage = 1; // Reset to first page
        filterRows();
    });

    // Update Row Category on Select Change
    $(document).on('change', '.category-select', function() {
        var row = $(this).closest('.dt-repeater-row');
        var val = $(this).val();
        // Handle multiple select array
        if (Array.isArray(val)) {
            val = val.join(',');
        }
        row.attr('data-category', val);
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
            var name = row.find('input[name*="[name]"]').val().toLowerCase();
            
            var matchCategory = false;
            if (currentFilter === '') {
                matchCategory = true;
            } else {
                var categories = cat ? cat.split(',') : [];
                if (categories.includes(currentFilter)) {
                    matchCategory = true;
                }
            }

            var matchSearch = true;
            if (currentSearch !== '') {
                if (name.indexOf(currentSearch) === -1) {
                    matchSearch = false;
                }
            }

            if (matchCategory && matchSearch) {
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
        $('.total-items').text(totalItems);
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
