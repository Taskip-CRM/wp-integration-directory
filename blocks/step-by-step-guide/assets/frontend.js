/**
 * Step-by-Step Guide Block - Frontend JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initializeStepGuides();
    });

    /**
     * Initialize all step guides on the page
     */
    function initializeStepGuides() {
        $('.wp-block-step-by-step-guide').each(function() {
            const $guide = $(this);
            new StepGuide($guide);
        });
    }

    /**
     * StepGuide Class
     */
    class StepGuide {
        constructor($container) {
            this.$container = $container;
            this.$steps = $container.find('.step-item');
            this.$prevBtn = $container.find('.prev-btn');
            this.$nextBtn = $container.find('.next-btn');
            this.$progressFill = $container.find('.progress-fill');
            this.$currentStepText = $container.find('.current-step');
            this.$copyBtns = $container.find('.copy-code-btn');

            this.currentStep = 1;
            this.totalSteps = this.$steps.length;

            this.init();
        }

        init() {
            this.setActiveStep(1);
            this.bindEvents();
            this.initializeCopyButtons();
            this.initializeIntersectionObserver();
        }

        bindEvents() {
            // Navigation buttons
            this.$prevBtn.on('click', () => this.goToPreviousStep());
            this.$nextBtn.on('click', () => this.goToNextStep());

            // Keyboard navigation
            $(document).on('keydown', (e) => this.handleKeyNavigation(e));

            // Step clicking
            this.$steps.on('click', (e) => {
                const stepIndex = $(e.currentTarget).index() + 1;
                this.goToStep(stepIndex);
            });
        }

        initializeCopyButtons() {
            this.$copyBtns.on('click', (e) => {
                const $btn = $(e.currentTarget);
                const code = $btn.data('code');
                this.copyToClipboard(code, $btn);
            });
        }

        initializeIntersectionObserver() {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const stepIndex = $(entry.target).index() + 1;
                            this.setActiveStep(stepIndex, false);
                        }
                    });
                }, {
                    threshold: 0.7,
                    rootMargin: '-20% 0px -20% 0px'
                });

                this.$steps.each(function() {
                    observer.observe(this);
                });
            }
        }

        goToStep(stepIndex) {
            if (stepIndex < 1 || stepIndex > this.totalSteps) {
                return;
            }

            this.setActiveStep(stepIndex);
            this.scrollToStep(stepIndex);
        }

        goToNextStep() {
            if (this.currentStep < this.totalSteps) {
                this.goToStep(this.currentStep + 1);
            }
        }

        goToPreviousStep() {
            if (this.currentStep > 1) {
                this.goToStep(this.currentStep - 1);
            }
        }

        setActiveStep(stepIndex, updateProgress = true) {
            this.currentStep = stepIndex;

            // Update step states
            this.$steps.removeClass('active completed');
            
            this.$steps.each((index, step) => {
                const $step = $(step);
                if (index + 1 < stepIndex) {
                    $step.addClass('completed');
                } else if (index + 1 === stepIndex) {
                    $step.addClass('active');
                }
            });

            // Update navigation buttons
            this.$prevBtn.prop('disabled', stepIndex === 1);
            this.$nextBtn.prop('disabled', stepIndex === this.totalSteps);

            if (updateProgress) {
                this.updateProgress();
            }

            // Trigger custom event
            this.$container.trigger('stepChanged', [stepIndex]);
        }

        updateProgress() {
            const progressPercent = (this.currentStep / this.totalSteps) * 100;
            
            this.$progressFill.css({
                'width': progressPercent + '%',
                'transition': 'width 0.5s ease'
            });
            
            this.$currentStepText.text(this.currentStep);

            // Animate progress fill
            setTimeout(() => {
                this.$progressFill.addClass('animated');
            }, 100);
        }

        scrollToStep(stepIndex) {
            const $targetStep = this.$steps.eq(stepIndex - 1);
            
            if ($targetStep.length) {
                const offset = $targetStep.offset().top - 100;
                
                $('html, body').animate({
                    scrollTop: offset
                }, 600, 'easeInOutCubic');
            }
        }

        handleKeyNavigation(e) {
            // Only handle keys when the guide container is in focus
            if (!this.$container.is(':focus-within')) {
                return;
            }

            switch(e.keyCode) {
                case 37: // Left arrow
                case 38: // Up arrow
                    e.preventDefault();
                    this.goToPreviousStep();
                    break;
                    
                case 39: // Right arrow
                case 40: // Down arrow
                    e.preventDefault();
                    this.goToNextStep();
                    break;
                    
                case 36: // Home
                    e.preventDefault();
                    this.goToStep(1);
                    break;
                    
                case 35: // End
                    e.preventDefault();
                    this.goToStep(this.totalSteps);
                    break;
            }
        }

        copyToClipboard(text, $btn) {
            // Modern clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showCopyFeedback($btn);
                }).catch(() => {
                    this.fallbackCopy(text, $btn);
                });
            } else {
                this.fallbackCopy(text, $btn);
            }
        }

        fallbackCopy(text, $btn) {
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
                this.showCopyFeedback($btn);
            } catch (err) {
                console.error('Failed to copy text: ', err);
                this.showCopyError($btn);
            }
            
            document.body.removeChild(textArea);
        }

        showCopyFeedback($btn) {
            const originalText = $btn.html();
            const originalClass = $btn.attr('class');
            
            $btn.addClass('copied')
                .html('<span class="dashicons dashicons-yes"></span> Copied!');
            
            setTimeout(() => {
                $btn.attr('class', originalClass)
                    .html(originalText);
            }, 2000);
        }

        showCopyError($btn) {
            const originalText = $btn.html();
            const originalClass = $btn.attr('class');
            
            $btn.addClass('error')
                .html('<span class="dashicons dashicons-no"></span> Failed');
            
            setTimeout(() => {
                $btn.attr('class', originalClass)
                    .html(originalText);
            }, 2000);
        }
    }

    /**
     * Custom easing function for smooth scrolling
     */
    $.easing.easeInOutCubic = function (x, t, b, c, d) {
        if ((t/=d/2) < 1) return c/2*t*t*t + b;
        return c/2*((t-=2)*t*t + 2) + b;
    };

    /**
     * Auto-advance functionality (optional)
     */
    window.StepGuideAutoAdvance = class {
        constructor(guide, interval = 10000) {
            this.guide = guide;
            this.interval = interval;
            this.timer = null;
            this.isPaused = false;

            this.init();
        }

        init() {
            this.startTimer();
            this.bindEvents();
        }

        bindEvents() {
            // Pause on hover
            this.guide.$container.on('mouseenter', () => this.pause());
            this.guide.$container.on('mouseleave', () => this.resume());

            // Pause on focus
            this.guide.$container.on('focusin', () => this.pause());
            this.guide.$container.on('focusout', () => this.resume());

            // Reset timer when user manually navigates
            this.guide.$container.on('stepChanged', () => this.resetTimer());
        }

        startTimer() {
            if (!this.isPaused) {
                this.timer = setTimeout(() => {
                    if (this.guide.currentStep < this.guide.totalSteps) {
                        this.guide.goToNextStep();
                        this.startTimer();
                    }
                }, this.interval);
            }
        }

        resetTimer() {
            this.clearTimer();
            this.startTimer();
        }

        pause() {
            this.isPaused = true;
            this.clearTimer();
        }

        resume() {
            this.isPaused = false;
            this.startTimer();
        }

        clearTimer() {
            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }
        }
    };

    /**
     * Progress tracking and analytics
     */
    window.StepGuideAnalytics = class {
        constructor(guide) {
            this.guide = guide;
            this.startTime = Date.now();
            this.stepTimes = {};
            this.completedSteps = new Set();

            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            this.guide.$container.on('stepChanged', (e, stepIndex) => {
                this.trackStepView(stepIndex);
            });

            // Track completion
            $(window).on('beforeunload', () => {
                this.trackCompletion();
            });
        }

        trackStepView(stepIndex) {
            const currentTime = Date.now();
            this.stepTimes[stepIndex] = currentTime - this.startTime;
            this.completedSteps.add(stepIndex);

            // Send to analytics service (example)
            if (typeof gtag !== 'undefined') {
                gtag('event', 'step_view', {
                    'step_number': stepIndex,
                    'guide_title': this.guide.$container.find('.step-guide-title').text(),
                    'time_on_step': this.stepTimes[stepIndex]
                });
            }
        }

        trackCompletion() {
            const completionRate = (this.completedSteps.size / this.guide.totalSteps) * 100;
            const totalTime = Date.now() - this.startTime;

            // Send completion analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'guide_completion', {
                    'completion_rate': completionRate,
                    'total_time': totalTime,
                    'guide_title': this.guide.$container.find('.step-guide-title').text()
                });
            }
        }
    };

    /**
     * Accessibility enhancements
     */
    function enhanceAccessibility() {
        $('.wp-block-step-by-step-guide').each(function() {
            const $guide = $(this);
            
            // Add ARIA labels
            $guide.attr('role', 'region')
                  .attr('aria-label', 'Step-by-step guide');
            
            // Add step indicators
            $guide.find('.step-item').each(function(index) {
                $(this).attr('role', 'tabpanel')
                       .attr('aria-labelledby', `step-${index + 1}-title`)
                       .attr('id', `step-${index + 1}-panel`);
                       
                $(this).find('.step-title')
                       .attr('id', `step-${index + 1}-title`);
            });

            // Add navigation ARIA labels
            $guide.find('.prev-btn').attr('aria-label', 'Go to previous step');
            $guide.find('.next-btn').attr('aria-label', 'Go to next step');
        });
    }

    // Initialize accessibility enhancements
    enhanceAccessibility();

    // Global API
    window.StepGuide = StepGuide;

})(jQuery);