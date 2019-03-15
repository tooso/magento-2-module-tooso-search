/* global varienGlobalEvents, ta */

define([
    "domReady!",
], function() {

    /**
     * Elements
     * @type {Array}
     */
    var elements = [];

    /**
     * Configuration function
     *
     * @param {Object} config
     * @param {*} element
     */
    return function (config, element) {
        var linkValue = element.getAttribute('href');
        var productSku = element.getAttribute(config.attributeName);

        config.pageSize = config.pageSize || 0;
        config.currentPage = config.currentPage || 1;

        elements.push(element);

        element.setAttribute('data-href', linkValue);
        element.setAttribute('href', '#');
        element.addEventListener('click', function () {
            var product = config.products[productSku];
            if (product !== undefined) {
                product = {
                    id: productSku,
                    position: elements.indexOf(element) + (config.pageSize * (config.currentPage - 1))
                }
            }
            if (config.debug) {
                console.debug('Tooso: click after search captured');
            }
            ta('ec:addProduct', product);
            if (config.debug) {
                console.debug('Tooso: tracked product:', product);
            }
            ta('ec:setAction', 'click', {
                'list': config.searchId
            });
            var timeout = setTimeout(function () {
                console.warn('Tooso: tracking system does not respond in time');
                document.location.href = linkValue; // fallback in case the library did not respond in time
            }, 1000);
            ta('send', 'event', 'cart', 'click', {
                hitCallback: function () {
                    clearTimeout(timeout);
                    if (config.debug) {
                        console.debug('Tooso: redirecting to ' + linkValue);
                    }
                    document.location.href = linkValue;
                },
            });
        });
    }
});
