(function () {
    'use strict';

    function initBranchProductsSliders() {
        if (typeof Swiper === 'undefined') {
            return;
        }

        const sliders = document.querySelectorAll(
            '.abm-branch-products .abm-products-swiper'
        );

        sliders.forEach(function (slider) {
            if (slider.classList.contains('swiper-initialized')) {
                return;
            }

            const container = slider.closest('.abm-branch-products');

            if (!container) {
                return;
            }

            const nextButton = container.querySelector(
                '.swiper-button-next'
            );

            const prevButton = container.querySelector(
                '.swiper-button-prev'
            );

            const pagination = container.querySelector(
                '.swiper-pagination'
            );

            const slideCount = slider.querySelectorAll(
                '.swiper-slide'
            ).length;

            /*
             * Detect the second branch products instance.
             *
             * The Elementor parent container has:
             *
             * .massar-products-grid
             *
             * This instance must always show
             * two products on every screen size.
             */
            const isTwoColumnInstance =
                container.closest('.massar-products-grid') !== null;

            const swiperOptions = {
                slidesPerView: 2,
                spaceBetween: 12,

                watchOverflow: true,

                grabCursor: true,

                loop: slideCount > 4,

                navigation: {
                    nextEl: nextButton,
                    prevEl: prevButton
                },

                pagination: {
                    el: pagination,
                    clickable: true
                }
            };

            /*
             * The default shortcode instance keeps
             * the original responsive behavior.
             *
             * 2 products → mobile
             * 3 products → tablet
             * 4 products → desktop
             *
             * The .massar-products-grid instance
             * intentionally skips these breakpoints
             * and remains at 2 products everywhere.
             */
            if (!isTwoColumnInstance) {
                swiperOptions.breakpoints = {
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 18
                    },

                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 24
                    }
                };
            }

            new Swiper(
                slider,
                swiperOptions
            );
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initBranchProductsSliders
        );
    } else {
        initBranchProductsSliders();
    }

    /*
     * Elementor frontend support.
     */
    window.addEventListener(
        'elementor/frontend/init',
        initBranchProductsSliders
    );
})();