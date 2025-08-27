/**
 * Frontend JavaScript for WP Integrations Directory
 */

(function($) {
    'use strict';

    // Global variables
    let isLoading = false;
    let currentFilters = {
        category: '',
        search: '',
        type: '',
        page: 1
    };

    // Initialize when document is ready
    $(document).ready(function() {
        initializeFilters();
        initializeLoadMore();
        initializeLightbox();
        initializeBookmarks();
        initializeShare();
        initializeCopyCode();
        initializeSearchKeyboard();
    });

    /**
     * Initialize filter functionality
     */
    function initializeFilters() {
        // Category filters
        $(document).on('click', '.filter-category', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const category = $this.data('category') || '';
            
            // Update active state
            $('.filter-category').removeClass('active');
            $this.addClass('active');
            
            // Update current filters
            currentFilters.category = category;
            currentFilters.page = 1;
            
            // Apply filters
            applyFilters();
        });

        // Type filter
        $(document).on('change', '#integration-type-filter', function() {
            currentFilters.type = $(this).val();
            currentFilters.page = 1;
            applyFilters();
        });

        // Search
        $(document).on('click', '#search-integrations', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Search on Enter key
        $(document).on('keypress', '#integration-search', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                performSearch();
            }
        });

        // Real-time search with debounce
        let searchTimeout;
        $(document).on('input', '#integration-search', function() {
            const query = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                if (query.length >= 3 || query.length === 0) {
                    currentFilters.search = query;
                    currentFilters.page = 1;
                    applyFilters();
                }
            }, 500);
        });

        // Sort filter
        $(document).on('change', '#integration-sort', function() {
            currentFilters.sort = $(this).val();
            currentFilters.page = 1;
            applyFilters();
        });
    }

    /**
     * Perform search
     */
    function performSearch() {
        const query = $('#integration-search').val().trim();
        currentFilters.search = query;
        currentFilters.page = 1;
        applyFilters();
    }

    /**
     * Apply current filters
     */
    function applyFilters() {
        if (isLoading) return;
        
        isLoading = true;
        showLoading();
        
        const data = {
            action: 'filter_integrations',
            nonce: integrationAjax.nonce,
            category: currentFilters.category,
            search: currentFilters.search,
            type: currentFilters.type,
            sort: currentFilters.sort || 'date',
            page: 1
        };

        $.ajax({
            url: integrationAjax.ajax_url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#integrations-grid').html(response.data.html);
                    updateResultsInfo(response.data);
                    updateLoadMoreButton(response.data);
                    updateURL();
                    
                    // Animate new results
                    animateResults();
                } else {
                    showError(integrationAjax.error_text);
                }
            },
            error: function() {
                showError(integrationAjax.error_text);
            },
            complete: function() {
                isLoading = false;
                hideLoading();
            }
        });
    }

    /**
     * Initialize load more functionality
     */
    function initializeLoadMore() {
        $(document).on('click', '#load-more-integrations', function(e) {
            e.preventDefault();
            
            if (isLoading) return;
            
            const $button = $(this);
            const currentPage = parseInt($button.data('page')) || 2;
            const maxPages = parseInt($button.data('max-pages')) || 1;
            
            if (currentPage > maxPages) {
                $button.hide();
                return;
            }
            
            isLoading = true;
            
            // Update button state
            $button.find('.load-more-text').hide();
            $button.find('.load-more-spinner').show();
            $button.prop('disabled', true);
            
            const data = {
                action: 'load_more_integrations',
                nonce: integrationAjax.nonce,
                category: currentFilters.category,
                search: currentFilters.search,
                type: currentFilters.type,
                sort: currentFilters.sort || 'date',
                page: currentPage
            };

            $.ajax({
                url: integrationAjax.ajax_url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.html.trim() !== '') {
                        // Append new items with animation
                        const $newItems = $(response.data.html);
                        $newItems.css('opacity', '0').css('transform', 'translateY(20px)');
                        $('#integrations-grid').append($newItems);
                        
                        // Animate new items
                        $newItems.each(function(index) {
                            const $item = $(this);
                            setTimeout(function() {
                                $item.css({
                                    'opacity': '1',
                                    'transform': 'translateY(0)',
                                    'transition': 'all 0.3s ease'
                                });
                            }, index * 100);
                        });
                        
                        // Update button
                        const nextPage = currentPage + 1;
                        if (nextPage > response.data.max_pages) {
                            $button.hide();
                        } else {
                            $button.data('page', nextPage);
                        }
                    } else {
                        $button.hide();
                        showNoMoreResults();
                    }
                },
                error: function() {
                    showError(integrationAjax.error_text);
                },
                complete: function() {
                    isLoading = false;
                    $button.find('.load-more-text').show();
                    $button.find('.load-more-spinner').hide();
                    $button.prop('disabled', false);
                }
            });
        });
    }

    /**
     * Initialize lightbox functionality
     */
    function initializeLightbox() {
        let currentImageIndex = 0;
        let images = [];
        
        // Open lightbox
        $(document).on('click', '.screenshot-link[data-lightbox]', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const imageUrl = $this.attr('href');
            const galleryName = $this.data('lightbox');
            
            // Collect all images in the same gallery
            images = [];
            $(`[data-lightbox="${galleryName}"]`).each(function() {
                images.push($(this).attr('href'));
            });
            
            currentImageIndex = images.indexOf(imageUrl);
            openLightbox(imageUrl);
        });
        
        // Close lightbox
        $(document).on('click', '.lightbox-close, .lightbox-backdrop', function(e) {
            e.preventDefault();
            closeLightbox();
        });
        
        // Navigation
        $(document).on('click', '.lightbox-prev', function(e) {
            e.preventDefault();
            if (images.length > 1) {
                currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                updateLightboxImage(images[currentImageIndex]);
            }
        });
        
        $(document).on('click', '.lightbox-next', function(e) {
            e.preventDefault();
            if (images.length > 1) {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                updateLightboxImage(images[currentImageIndex]);
            }
        });
        
        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if ($('#lightbox-modal').is(':visible')) {
                switch(e.keyCode) {
                    case 27: // Escape
                        closeLightbox();
                        break;
                    case 37: // Left arrow
                        if (images.length > 1) {
                            currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                            updateLightboxImage(images[currentImageIndex]);
                        }
                        break;
                    case 39: // Right arrow
                        if (images.length > 1) {
                            currentImageIndex = (currentImageIndex + 1) % images.length;
                            updateLightboxImage(images[currentImageIndex]);
                        }
                        break;
                }
            }
        });
    }

    /**
     * Open lightbox with image
     */
    function openLightbox(imageUrl) {
        const $modal = $('#lightbox-modal');
        const $image = $modal.find('.lightbox-image');
        
        $image.attr('src', imageUrl);
        $modal.fadeIn(300);
        $('body').addClass('lightbox-active');
        
        // Show/hide navigation buttons
        if (images.length > 1) {
            $modal.find('.lightbox-nav').show();
        } else {
            $modal.find('.lightbox-nav').hide();
        }
    }

    /**
     * Update lightbox image
     */
    function updateLightboxImage(imageUrl) {
        const $image = $('#lightbox-modal .lightbox-image');
        $image.fadeOut(150, function() {
            $image.attr('src', imageUrl).fadeIn(150);
        });
    }

    /**
     * Close lightbox
     */
    function closeLightbox() {
        $('#lightbox-modal').fadeOut(300);
        $('body').removeClass('lightbox-active');
    }

    /**
     * Initialize bookmark functionality
     */
    function initializeBookmarks() {
        // Load bookmarks from localStorage
        let bookmarks = JSON.parse(localStorage.getItem('integration_bookmarks') || '[]');
        
        // Update bookmark buttons
        updateBookmarkButtons(bookmarks);
        
        // Handle bookmark clicks
        $(document).on('click', '.bookmark-integration, .bookmark-btn', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const integrationId = $button.data('integration-id');
            
            if (!integrationId) return;
            
            // Toggle bookmark
            const index = bookmarks.indexOf(integrationId);
            if (index > -1) {
                bookmarks.splice(index, 1);
                $button.removeClass('bookmarked');
                $button.find('.dashicons').removeClass('dashicons-star-filled').addClass('dashicons-star-empty');
            } else {
                bookmarks.push(integrationId);
                $button.addClass('bookmarked');
                $button.find('.dashicons').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
            }
            
            // Save to localStorage
            localStorage.setItem('integration_bookmarks', JSON.stringify(bookmarks));
            
            // Show feedback
            showBookmarkFeedback($button, index === -1);
        });
    }

    /**
     * Update bookmark button states
     */
    function updateBookmarkButtons(bookmarks) {
        $('.bookmark-integration, .bookmark-btn').each(function() {
            const $button = $(this);
            const integrationId = $button.data('integration-id');
            
            if (bookmarks.includes(integrationId)) {
                $button.addClass('bookmarked');
                $button.find('.dashicons').removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
            }
        });
    }

    /**
     * Show bookmark feedback
     */
    function showBookmarkFeedback($button, isBookmarked) {
        const message = isBookmarked ? 'Bookmarked!' : 'Bookmark removed';
        const $feedback = $('<span class="bookmark-feedback">' + message + '</span>');
        
        $button.append($feedback);
        $feedback.fadeIn(200).delay(1500).fadeOut(200, function() {
            $feedback.remove();
        });
    }

    /**
     * Initialize share functionality
     */
    function initializeShare() {
        // Copy link functionality
        $(document).on('click', '.copy-link', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const url = $button.data('url') || window.location.href;
            
            // Modern clipboard API
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    showCopyFeedback($button, 'Link copied!');
                }).catch(function() {
                    fallbackCopyText(url, $button);
                });
            } else {
                fallbackCopyText(url, $button);
            }
        });
        
        // Share button (Web Share API if available)
        $(document).on('click', '.share-btn', function(e) {
            e.preventDefault();
            
            const integrationId = $(this).data('integration-id');
            const title = $('.integration-title').text() || document.title;
            const url = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    url: url
                }).catch(function(error) {
                    console.log('Error sharing:', error);
                });
            } else {
                // Fallback - copy to clipboard
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(function() {
                        showCopyFeedback($(this), 'Link copied!');
                    });
                }
            }
        });
    }

    /**
     * Fallback copy text function
     */
    function fallbackCopyText(text, $button) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopyFeedback($button, 'Link copied!');
        } catch (err) {
            showCopyFeedback($button, 'Copy failed');
        }
        
        document.body.removeChild(textArea);
    }

    /**
     * Show copy feedback
     */
    function showCopyFeedback($button, message) {
        const $tooltip = $button.find('.tooltip');
        const originalText = $tooltip.text();
        
        $tooltip.text(message).addClass('show');
        
        setTimeout(function() {
            $tooltip.text(originalText).removeClass('show');
        }, 2000);
    }

    /**
     * Initialize copy code functionality
     */
    function initializeCopyCode() {
        $(document).on('click', '.copy-code-btn', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const target = $button.data('clipboard-target');
            const $target = $(target);
            
            if ($target.length) {
                const code = $target.text();
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(code).then(function() {
                        showCodeCopyFeedback($button, 'Copied!');
                    }).catch(function() {
                        fallbackCopyCode(code, $button);
                    });
                } else {
                    fallbackCopyCode(code, $button);
                }
            }
        });
    }

    /**
     * Fallback copy code function
     */
    function fallbackCopyCode(code, $button) {
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCodeCopyFeedback($button, 'Copied!');
        } catch (err) {
            showCodeCopyFeedback($button, 'Copy failed');
        }
        
        document.body.removeChild(textArea);
    }

    /**
     * Show code copy feedback
     */
    function showCodeCopyFeedback($button, message) {
        const originalText = $button.text();
        $button.text(message);
        
        setTimeout(function() {
            $button.html('<i class="dashicons dashicons-clipboard"></i> Copy');
        }, 2000);
    }

    /**
     * Initialize search keyboard shortcuts
     */
    function initializeSearchKeyboard() {
        // Focus search on Ctrl+K or Cmd+K
        $(document).on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 75) {
                e.preventDefault();
                $('#integration-search').focus();
            }
        });
    }

    /**
     * Show loading state
     */
    function showLoading() {
        $('#integration-loading').show();
        $('#integrations-grid').addClass('loading');
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        $('#integration-loading').hide();
        $('#integrations-grid').removeClass('loading');
    }

    /**
     * Show error message
     */
    function showError(message) {
        const $error = $('<div class="integration-error"><p>' + message + '</p></div>');
        $('#integrations-grid').html($error);
    }

    /**
     * Show no more results message
     */
    function showNoMoreResults() {
        const $message = $('<div class="integration-no-more"><p>' + integrationAjax.no_more_text + '</p></div>');
        $('.integration-load-more-container').append($message);
        
        setTimeout(function() {
            $message.fadeOut();
        }, 3000);
    }

    /**
     * Update results info
     */
    function updateResultsInfo(data) {
        const $resultsShowing = $('#results-showing');
        if (data.found_posts) {
            let text = 'Showing ' + data.found_posts + ' integrations';
            if (currentFilters.search) {
                text += ' for "' + currentFilters.search + '"';
            }
            if (currentFilters.category) {
                const categoryName = $('.filter-category.active').text().replace(/\(\d+\)/, '').trim();
                text += ' in ' + categoryName;
            }
            $resultsShowing.text(text);
        }
    }

    /**
     * Update load more button
     */
    function updateLoadMoreButton(data) {
        const $loadMore = $('#load-more-integrations');
        if (data.max_pages > 1) {
            $loadMore.data('max-pages', data.max_pages).data('page', 2).show();
        } else {
            $loadMore.hide();
        }
    }

    /**
     * Animate new results
     */
    function animateResults() {
        const $cards = $('#integrations-grid .integration-card');
        $cards.each(function(index) {
            const $card = $(this);
            $card.css({
                'opacity': '0',
                'transform': 'translateY(30px)'
            });
            
            setTimeout(function() {
                $card.css({
                    'opacity': '1',
                    'transform': 'translateY(0)',
                    'transition': 'all 0.4s ease'
                });
            }, index * 50);
        });
    }

    /**
     * Update URL with current filters (without page refresh)
     */
    function updateURL() {
        if (typeof history === 'undefined' || !history.pushState) return;
        
        const url = new URL(window.location);
        const params = new URLSearchParams();
        
        if (currentFilters.category) {
            params.set('category', currentFilters.category);
        }
        if (currentFilters.search) {
            params.set('search', currentFilters.search);
        }
        if (currentFilters.type) {
            params.set('type', currentFilters.type);
        }
        if (currentFilters.sort && currentFilters.sort !== 'date') {
            params.set('sort', currentFilters.sort);
        }
        
        url.search = params.toString();
        history.pushState(null, '', url);
    }

    /**
     * Initialize filters from URL parameters
     */
    function initializeFromURL() {
        const params = new URLSearchParams(window.location.search);
        
        if (params.has('category')) {
            const category = params.get('category');
            currentFilters.category = category;
            $(`.filter-category[data-category="${category}"]`).addClass('active');
            $('.filter-category[data-category=""]').removeClass('active');
        }
        
        if (params.has('search')) {
            const search = params.get('search');
            currentFilters.search = search;
            $('#integration-search').val(search);
        }
        
        if (params.has('type')) {
            const type = params.get('type');
            currentFilters.type = type;
            $('#integration-type-filter').val(type);
        }
        
        if (params.has('sort')) {
            const sort = params.get('sort');
            currentFilters.sort = sort;
            $('#integration-sort').val(sort);
        }
        
        // Apply filters if any are set
        if (currentFilters.category || currentFilters.search || currentFilters.type || currentFilters.sort) {
            applyFilters();
        }
    }

    /**
     * Handle browser back/forward buttons
     */
    window.addEventListener('popstate', function() {
        initializeFromURL();
    });

    // Initialize from URL on page load
    $(window).on('load', function() {
        initializeFromURL();
    });

    /**
     * Smooth scroll to top when filters change
     */
    function scrollToResults() {
        const $resultsContainer = $('#integrations-grid');
        if ($resultsContainer.length) {
            $('html, body').animate({
                scrollTop: $resultsContainer.offset().top - 100
            }, 300);
        }
    }

    /**
     * Lazy loading for images
     */
    function initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // Initialize lazy loading
    initializeLazyLoading();

    // Reinitialize lazy loading after AJAX content loads
    $(document).on('integrations_loaded', function() {
        initializeLazyLoading();
    });

})(jQuery);