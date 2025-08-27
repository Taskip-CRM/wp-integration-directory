/**
 * Admin JavaScript for WP Integrations Directory
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Integration admin script loaded');
        console.log('wp.media available:', typeof wp !== 'undefined' && typeof wp.media !== 'undefined');
        
        initializeMediaUploader();
        initializeFeatureRepeater();
        initializeScreenshotsGallery();
        initializeBulkEdit();
        initializePreview();
        initializeColorPicker();
        initializeFormValidation();
    });

    /**
     * Initialize media uploader for integration logo
     */
    function initializeMediaUploader() {
        let mediaUploader;

        // Logo uploader
        $(document).on('click', '#integration_logo_button', function(e) {
            e.preventDefault();

            // If the uploader object has already been created, reopen the dialog
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            // Extend the wp.media object
            mediaUploader = wp.media({
                title: integrationAdmin.select_logo,
                button: {
                    text: integrationAdmin.select_logo
                },
                multiple: false
            });

            // When a file is selected
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Update the input and preview
                $('#integration_logo').val(attachment.id);
                $('#integration_logo_preview').html(`<img src="${attachment.url}" alt="${attachment.alt}" style="max-width: 100px; height: auto;" />`);
                $('#integration_logo_button').text(integrationAdmin.change_logo);
                $('#integration_logo_remove').show();
            });

            // Open the uploader dialog
            mediaUploader.open();
        });

        // Logo remover
        $(document).on('click', '#integration_logo_remove', function(e) {
            e.preventDefault();
            
            $('#integration_logo').val('');
            $('#integration_logo_preview').empty();
            $('#integration_logo_button').text(integrationAdmin.select_logo);
            $(this).hide();
        });

        // Hero logo uploader
        let heroLogoUploader;
        $(document).on('click', '.upload-logo-btn', function(e) {
            e.preventDefault();
            console.log('Upload logo button clicked');

            // Check if wp.media is available
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                alert('WordPress media library is not loaded. Please refresh the page and try again.');
                return;
            }

            if (heroLogoUploader) {
                heroLogoUploader.open();
                return;
            }

            heroLogoUploader = wp.media({
                title: 'Select Hero Logo',
                button: {
                    text: 'Select Logo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            heroLogoUploader.on('select', function() {
                const attachment = heroLogoUploader.state().get('selection').first().toJSON();
                console.log('Logo selected:', attachment);
                
                $('#integration_hero_site_logo').val(attachment.id);
                $('.logo-preview img').attr('src', attachment.url);
                $('.logo-preview').show();
                $('.upload-logo-btn').hide();
            });

            heroLogoUploader.open();
        });

        // Remove hero logo
        $(document).on('click', '.remove-logo-btn', function(e) {
            e.preventDefault();
            
            $('#integration_hero_site_logo').val('');
            $('.logo-preview').hide();
            $('.upload-logo-btn').show();
        });
    }

    /**
     * Initialize features repeater field
     */
    function initializeFeatureRepeater() {
        // Add new feature
        $(document).on('click', '#add_feature', function(e) {
            e.preventDefault();

            const $container = $('#integration_features_container');
            const featureHtml = `
                <div class="feature-item" style="margin-bottom: 10px;">
                    <input type="text" name="integration_features[]" value="" class="regular-text" placeholder="Enter a feature" />
                    <button type="button" class="button remove-feature">Remove</button>
                </div>
            `;

            $container.append(featureHtml);
            updateRemoveButtons();
        });

        // Remove feature
        $(document).on('click', '.remove-feature', function(e) {
            e.preventDefault();
            
            $(this).closest('.feature-item').remove();
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const $items = $('.feature-item');
            const $removeButtons = $('.remove-feature');

            if ($items.length <= 1) {
                $removeButtons.hide();
            } else {
                $removeButtons.show();
            }
        }

        // Initialize on load
        updateRemoveButtons();
    }

    /**
     * Initialize screenshots gallery uploader
     */
    function initializeScreenshotsGallery() {
        let screenshotsUploader;

        $(document).on('click', '#integration_screenshots_button', function(e) {
            e.preventDefault();

            if (screenshotsUploader) {
                screenshotsUploader.open();
                return;
            }

            screenshotsUploader = wp.media({
                title: integrationAdmin.select_screenshots,
                button: {
                    text: integrationAdmin.select_screenshots
                },
                multiple: true
            });

            screenshotsUploader.on('select', function() {
                const selection = screenshotsUploader.state().get('selection');
                const ids = [];
                
                selection.map(function(attachment) {
                    const attachmentData = attachment.toJSON();
                    ids.push(attachmentData.id);
                    
                    // Add to preview
                    const screenshotHtml = `
                        <div class="screenshot-item" data-id="${attachmentData.id}">
                            <img src="${attachmentData.sizes.thumbnail ? attachmentData.sizes.thumbnail.url : attachmentData.url}" alt="${attachmentData.alt}" />
                            <button type="button" class="remove-screenshot">×</button>
                        </div>
                    `;
                    $('#screenshots_preview').append(screenshotHtml);
                });

                // Update hidden field
                const currentIds = $('#integration_screenshots').val();
                const allIds = currentIds ? currentIds.split(',').concat(ids) : ids;
                $('#integration_screenshots').val(allIds.join(','));
            });

            screenshotsUploader.open();
        });

        // Remove screenshot
        $(document).on('click', '.remove-screenshot', function(e) {
            e.preventDefault();
            
            const $item = $(this).closest('.screenshot-item');
            const idToRemove = $item.data('id');
            
            // Remove from preview
            $item.remove();
            
            // Update hidden field
            const currentIds = $('#integration_screenshots').val().split(',');
            const filteredIds = currentIds.filter(id => id != idToRemove);
            $('#integration_screenshots').val(filteredIds.join(','));
        });
    }

    /**
     * Initialize bulk edit functionality
     */
    function initializeBulkEdit() {
        $(document).on('click', '#doaction, #doaction2', function(e) {
            const action = $(this).siblings('select').val();
            
            if (action === 'edit') {
                // Delay to allow WordPress to render the bulk edit form
                setTimeout(function() {
                    initializeBulkEditForm();
                }, 100);
            }
        });

        function initializeBulkEditForm() {
            // Handle bulk edit save
            $('.bulk-edit-save .button').off('click.integration').on('click.integration', function(e) {
                const $form = $(this).closest('tr');
                const postIds = [];
                
                // Collect selected post IDs
                $('tbody th.check-column input[type="checkbox"]:checked').each(function() {
                    if ($(this).val() !== 'on') {
                        postIds.push($(this).val());
                    }
                });

                if (postIds.length === 0) return;

                // Collect form data
                const integrationType = $form.find('select[name="integration_type"]').val();

                // Send AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_bulk_edit',
                        post_ids: postIds,
                        integration_type: integrationType,
                        _ajax_nonce: $('#_wpnonce').val()
                    },
                    success: function() {
                        // Refresh the page to show changes
                        window.location.reload();
                    }
                });
            });
        }
    }

    /**
     * Initialize integration preview
     */
    function initializePreview() {
        $(document).on('click', '.integration-preview-btn', function(e) {
            e.preventDefault();
            
            const postId = $(this).data('post-id');
            openPreviewModal(postId);
        });

        $(document).on('click', '.integration-preview-close, .integration-preview-modal', function(e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });

        // Escape key to close
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && $('.integration-preview-modal').hasClass('active')) {
                closePreviewModal();
            }
        });

        function openPreviewModal(postId) {
            // Create modal if it doesn't exist
            if ($('.integration-preview-modal').length === 0) {
                const modalHtml = `
                    <div class="integration-preview-modal">
                        <div class="integration-preview-content">
                            <div class="integration-preview-header">
                                <h2>Integration Preview</h2>
                                <button class="integration-preview-close">×</button>
                            </div>
                            <div class="integration-preview-body">
                                <div class="integration-admin-loading">Loading preview...</div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(modalHtml);
            }

            const $modal = $('.integration-preview-modal');
            const $body = $modal.find('.integration-preview-body');

            $modal.addClass('active');
            $body.html('<div class="integration-admin-loading">Loading preview...</div>');

            // Load preview content via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_integration_preview',
                    post_id: postId,
                    _ajax_nonce: $('#_wpnonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        $body.html(response.data);
                    } else {
                        $body.html('<p>Error loading preview.</p>');
                    }
                },
                error: function() {
                    $body.html('<p>Error loading preview.</p>');
                }
            });
        }

        function closePreviewModal() {
            $('.integration-preview-modal').removeClass('active');
        }
    }

    /**
     * Initialize color picker for taxonomy
     */
    function initializeColorPicker() {
        if ($('.color-picker').length) {
            $('.color-picker').wpColorPicker({
                defaultColor: '#0073aa',
                change: function(event, ui) {
                    // Handle color change
                },
                clear: function() {
                    // Handle color clear
                }
            });
        }
    }

    /**
     * Initialize form validation
     */
    function initializeFormValidation() {
        // Validate required fields before publishing
        $(document).on('click', '#publish', function(e) {
            let hasErrors = false;
            const errors = [];

            // Check if logo is uploaded
            const logoId = $('#integration_logo').val();
            if (!logoId) {
                errors.push('Integration logo is required.');
                hasErrors = true;
            }

            // Check if title is not empty
            const title = $('#title').val().trim();
            if (!title) {
                errors.push('Integration title is required.');
                hasErrors = true;
            }

            // Check if at least one category is selected
            const categories = $('input[name="tax_input[integration_category][]"]:checked');
            if (categories.length === 0) {
                errors.push('At least one category must be selected.');
                hasErrors = true;
            }

            if (hasErrors) {
                e.preventDefault();
                showValidationErrors(errors);
                return false;
            }
        });

        function showValidationErrors(errors) {
            // Remove existing error notices
            $('.integration-validation-error').remove();

            // Create error notice
            const errorHtml = `
                <div class="notice notice-error integration-validation-error">
                    <p><strong>Please correct the following errors:</strong></p>
                    <ul>
                        ${errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                </div>
            `;

            // Insert after the title
            $('#titlediv').after(errorHtml);

            // Scroll to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        }
    }

    /**
     * Auto-save integration data
     */
    function initializeAutoSave() {
        let autoSaveTimeout;

        // Auto-save on input change
        $(document).on('input', '#integration-form input, #integration-form textarea, #integration-form select', function() {
            clearTimeout(autoSaveTimeout);
            
            autoSaveTimeout = setTimeout(function() {
                saveIntegrationDraft();
            }, 2000);
        });

        function saveIntegrationDraft() {
            const $form = $('#post');
            const formData = $form.serialize();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData + '&action=save_integration_draft',
                success: function(response) {
                    if (response.success) {
                        showAutoSaveFeedback();
                    }
                }
            });
        }

        function showAutoSaveFeedback() {
            const $feedback = $('<span class="auto-save-feedback">Draft saved</span>');
            $('#title').after($feedback);
            
            $feedback.fadeIn(200).delay(2000).fadeOut(200, function() {
                $feedback.remove();
            });
        }
    }

    /**
     * Character counter for text areas
     */
    function initializeCharacterCounter() {
        $(document).on('input', 'textarea[data-max-length]', function() {
            const $textarea = $(this);
            const maxLength = parseInt($textarea.data('max-length'));
            const currentLength = $textarea.val().length;
            
            let $counter = $textarea.siblings('.character-counter');
            if ($counter.length === 0) {
                $counter = $('<div class="character-counter"></div>');
                $textarea.after($counter);
            }

            $counter.text(`${currentLength}/${maxLength} characters`);
            
            if (currentLength > maxLength) {
                $counter.addClass('over-limit');
                $textarea.addClass('over-limit');
            } else {
                $counter.removeClass('over-limit');
                $textarea.removeClass('over-limit');
            }
        });

        // Initialize counters
        $('textarea[data-max-length]').trigger('input');
    }

    /**
     * Sortable features list
     */
    function initializeSortableFeatures() {
        if ($.fn.sortable) {
            $('#integration_features_container').sortable({
                handle: '.sort-handle',
                placeholder: 'sort-placeholder',
                tolerance: 'pointer',
                axis: 'y'
            });
        }
    }

    /**
     * Quick actions for integration list
     */
    function initializeQuickActions() {
        // Duplicate integration
        $(document).on('click', '.duplicate-integration', function(e) {
            e.preventDefault();
            
            const postId = $(this).data('post-id');
            
            if (confirm('Are you sure you want to duplicate this integration?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'duplicate_integration',
                        post_id: postId,
                        _ajax_nonce: $('#_wpnonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.data.edit_url;
                        } else {
                            alert('Error duplicating integration.');
                        }
                    }
                });
            }
        });

        // Quick status change
        $(document).on('change', '.quick-status-change', function() {
            const $select = $(this);
            const postId = $select.data('post-id');
            const newStatus = $select.val();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'quick_status_change',
                    post_id: postId,
                    status: newStatus,
                    _ajax_nonce: $('#_wpnonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        // Update row class
                        $select.closest('tr').removeClass().addClass('status-' + newStatus);
                        showQuickActionFeedback('Status updated successfully');
                    } else {
                        showQuickActionFeedback('Error updating status', 'error');
                    }
                }
            });
        });

        function showQuickActionFeedback(message, type = 'success') {
            const $notice = $(`<div class="notice notice-${type} is-dismissible quick-action-notice"><p>${message}</p></div>`);
            $('.wp-header-end').after($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $notice.remove();
                });
            }, 3000);
        }
    }

    /**
     * Help tooltips
     */
    function initializeHelpTooltips() {
        // Add help icons with tooltips
        $('[data-help]').each(function() {
            const $element = $(this);
            const helpText = $element.data('help');
            
            const $helpIcon = $('<span class="help-icon dashicons dashicons-editor-help"></span>');
            const $tooltip = $('<div class="help-tooltip">' + helpText + '</div>');
            
            $element.append($helpIcon);
            $helpIcon.after($tooltip);
            
            $helpIcon.on('mouseenter', function() {
                $tooltip.addClass('show');
            }).on('mouseleave', function() {
                $tooltip.removeClass('show');
            });
        });
    }

    // Initialize additional features
    initializeAutoSave();
    initializeCharacterCounter();
    initializeSortableFeatures();
    initializeQuickActions();
    initializeHelpTooltips();

    // Handle media library state
    $(window).on('beforeunload', function() {
        if (typeof wp !== 'undefined' && wp.media) {
            // Clean up media library instances
        }
    });

})(jQuery);

// Global functions accessible from other scripts
window.IntegrationAdmin = {
    refreshIntegrationsList: function() {
        if (window.location.href.indexOf('edit.php') !== -1) {
            window.location.reload();
        }
    },
    
    showNotice: function(message, type = 'success') {
        const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
        $('.wp-header-end').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        }, 5000);
    }
};