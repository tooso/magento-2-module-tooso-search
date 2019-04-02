/* global ta */

define([
    'jquery',
], function($) {

    /**
     * Global settings
     *
     * @type {*}
     */
    var config = {};

    /**
     * Get element position
     *
     * @param {*} element
     * @param {*} parent
     * @return {Number}
     */
    function getElementPosition(element, parent) {
        var links = $(parent).find(config.productLinksSelector);
        return links.toArray().indexOf(element);
    }

    /**
     * Click handler for product link elements
     *
     * @param {*} event
     */
    function clickHandler(event) {
        var element = $(this);
        var linkValue = element.attr('href');
        var productSku = element.attr(config.attributeName);

        if (config.debug) {
            console.debug('Tooso: click after search captured');
        }

        if (window.ta === undefined) {
            console.warn('Tooso: ta is not include but analytics is active');
            return;
        }
        if (linkValue === null || linkValue === undefined) {
            console.warn('Tooso: click handled on a non-link element, href attribute not found');
            return;
        }
        if (productSku === null || productSku === undefined) {
            console.warn('Tooso: product link does not have the attribute '.config.attributeName);
            return;
        }

        elaboratePaginationFromURL();

        var product = config.products[productSku];
        if (product === undefined) {
            product = {
                id: productSku,
                position: getElementPosition(element.get(0), event.data.parent) + (config.pageSize * (config.currentPage - 1))
            }
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

        // Prevent default click behaviour
        event.preventDefault();
        event.stopPropagation();
        return false;
    }

    /**
     * Elaborate pagination from URL query parameters
     *
     */
    function elaboratePaginationFromURL() {
        var queries = {};
        $.each(document.location.search.substr(1).split('&'),function(c,q){
            var i = q.split('=');
            queries[i[0].toString()] = i[1].toString();
        });
        if (queries.product_list_limit !== undefined) {
            config.pageSize = parseInt(queries.product_list_limit);
            if (config.debug) {
                console.debug('Tooso: page size is now '+config.pageSize);
            }
        }
        if (queries.p !== undefined) {
            config.currentPage = parseInt(queries.p);
            if (config.debug) {
                console.debug('Tooso: current page is now '+config.currentPage);
            }
        }
    }

    /**
     * Configuration function
     *
     * @param {Object} configParams
     * @param {*} parentContainer
     */
    return function (configParams, parentContainer) {
        config = Object.assign({}, configParams);
        config.pageSize = config.pageSize || 0;
        config.currentPage = config.currentPage || 1;

        $(parentContainer).on(
            'click',
            config.productLinksSelector,
            {
                parent: parentContainer
            },
            clickHandler
        );
    }
});
