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

            new Swiper(slider, {
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
                },

                breakpoints: {
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 18
                    },

                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 24
                    }
                }
            });
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