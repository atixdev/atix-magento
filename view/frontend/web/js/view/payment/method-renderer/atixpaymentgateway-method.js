define(["Magento_Checkout/js/view/payment/default"], function (Component) {
    "use strict";
    return Component.extend({
        defaults: {
            template: "Atix_PaymentGateway/payment/atixpaymentgateway",
        },
        /**
         * Returns payment method instructions.
         *
         * @return {*}
         */
        getInstructions: function () {
            return window.checkoutConfig.payment.instructions[this.item.method];
        },
    });
});
