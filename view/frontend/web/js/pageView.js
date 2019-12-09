define([
    'Magento_Customer/js/customer-data',
], function(customerData) {

    // Tracking operation timeout
    var timeout = null;

    // Prevent event send duplication
    var alreadySent = false;

    /**
     * Execute page tracking
     *
     * @param customerInfo
     */
    function executePageTracking(customerInfo) {
        if (alreadySent) {
            return;
        }
        if (customerInfo && customerInfo.customerId) {
            window.ta('set', 'userId', customerInfo.customerId);
        }
        window.ta('send', 'pageview');
        if (timeout !== null) {
            clearTimeout(timeout);
        }
        alreadySent = true;
    }

    /**
     * Configuration function
     */
    return function () {
        var customer = customerData.get('customer');
        if (customerData.needReload() === true) {
            customer.subscribe(executePageTracking);
            timeout = setTimeout(function () {
                executePageTracking(customer());
            }, 1000);
            customerData.reload(['customer']);
        } else {
            executePageTracking(customer());
        }
    }

});
