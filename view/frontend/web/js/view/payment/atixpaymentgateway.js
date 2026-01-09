define([
    "uiComponent",
    "Magento_Checkout/js/model/payment/renderer-list",
], function (Component, rendererList) {
    "use strict";
    rendererList.push({
        type: "atixpaymentgateway",
        component:
            // "Atix_PaymentGateway/js/view/payment/method-renderer/atixpaymentgateway-method",
            "Atix_PaymentGateway/js/view/payment/method-renderer/custompayment-method",
    });

    /** Add veiw logic here if needed */
    return Component.extend({});
});
