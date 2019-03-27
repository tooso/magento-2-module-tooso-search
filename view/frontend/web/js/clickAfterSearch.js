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
     * Product links elements
     *
     * @type {Array}
     */
    var elements = [];

    /**
     * Find alla products links into container
     */
    function elaborateProductsElements(parentContainer) {
        elements = [];
        $(parentContainer).find(config.productLinksSelector).each(function () {
            var element = $(this);
            elements.push(element);
            var productSku = element.attr(config.attributeName);
            var linkValue = element.attr('href');
            element.attr('data-href', linkValue);
            element.attr('href', '#');
            attachClickHandler(element, productSku, linkValue);
        });
    }

    /**
     * Attach click handler to element
     *
     * @param {*} element
     * @param {String} productSku
     * @param {String} linkValue
     */
    function attachClickHandler(element, productSku, linkValue) {
        element.click(function () {
            if (window.ta === undefined) {
                console.warn('Tooso: ta is not include but analytics is active');
                document.location.href = linkValue; // this should never happens
                return;
            }

            var product = config.products[productSku];
            if (product === undefined) {
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
        })
    }

    /**
     * Observer for HTML changes
     *
     * @param {*} target
     * @param {function} callback
     */
    function observeForChanges(target, callback) {
        var observer = new MutationObserver(callback);
        observer.observe(target, { attributes: true, childList: true, characterData: true });
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
     * @param {Object} config
     * @param {*} element
     */
    return function (configParams, parentContainer) {
        config = Object.assign({}, configParams);
        config.pageSize = config.pageSize || 0;
        config.currentPage = config.currentPage || 1;

        // Elaborate child elements
        elaborateProductsElements(parentContainer);

        // Observer for HTML changes (like AJAX pagination,filters..)
        observeForChanges(parentContainer, function () {
            if (config.debug) {
                console.debug('Tooso: products container changed');
            }

            // Check for pagination change
            elaboratePaginationFromURL();

            // Re-elaborate child elements
            elaborateProductsElements(parentContainer);
        })

    }
});
