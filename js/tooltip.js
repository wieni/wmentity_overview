(function ($, Drupal, drupalSettings) {
    'use strict';

    Drupal.behaviors.entityOverviewTooltip = {
        attach: function (context) {
            $('[data-tooltip]').each(function (i, tooltip) {
                const target = $(tooltip).siblings('[data-tooltip-target]')[0];

                if (!target) {
                    return;
                }

                let popperInstance = null;

                function create() {
                    popperInstance = Popper.createPopper(target, tooltip, {
                        modifiers: [
                            {
                                name: 'offset',
                                options: {
                                    offset: [0, 8],
                                },
                            },
                        ],
                    });
                }

                function destroy() {
                    if (popperInstance) {
                        popperInstance.destroy();
                        popperInstance = null;
                    }
                }

                function show() {
                    tooltip.setAttribute('data-show', '');
                    create();
                }

                function hide() {
                    tooltip.removeAttribute('data-show');
                    destroy();
                }

                const showEvents = ['mouseenter', 'focus'];
                const hideEvents = ['mouseleave', 'blur'];

                showEvents.forEach(event => {
                    target.addEventListener(event, show);
                });

                hideEvents.forEach(event => {
                    target.addEventListener(event, hide);
                });
            })
        }
    };

}(jQuery, Drupal, drupalSettings));
