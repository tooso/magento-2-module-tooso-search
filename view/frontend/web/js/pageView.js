define([
    'Magento_Customer/js/customer-data',
], function(customerData) {

    /**
     * Execute page tracking
     *
     * @param customerInfo
     */
    function executePageTracking(customerInfo) {
        if (customerInfo && customerInfo.customerId) {
            window.ta('set', 'userId', customerInfo.customerId);
        }
        window.ta('send', 'pageview');
    }

    /**
     * Configuration function
     */
    return function () {
        var customer = customerData.get('customer');

        if (customerData.needReload() === true) {
            customer.subscribe(executePageTracking);
        } else {
            var customerInfo = customer();
            executePageTracking(customerInfo);
        }
    }

});
