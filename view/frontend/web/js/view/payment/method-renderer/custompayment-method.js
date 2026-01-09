define([
    "jquery",
    "Magento_Payment/js/view/payment/cc-form",
    "Magento_Checkout/js/action/place-order",
    "Magento_Checkout/js/action/select-payment-method",
    "Magento_Checkout/js/model/payment/additional-validators",
    "Magento_Ui/js/model/messages",
    "Magento_Checkout/js/model/quote",
], function (
    $,
    Component,
    placeOrderAction,
    selectPaymentMethodAction,
    additionalValidators,
    Messages,
    quote
) {
    "use strict";

    return Component.extend({
        defaults: {
            template: "Atix_PaymentGateway/payment/form-custom",
        },
        getCode: function () {
            return "atixpaymentgateway"; // Replace with your payment method code
        },
        getData: function () {
            return {
                method: this.item.method,
                additional_data: {
                    // 'custom_field': this.customField
                },
            };
        },
        getLogoUrl: function () {
            return require.toUrl("Atix_PaymentGateway/images/logos-credit-debit-cards.png");
        },
        placeOrder: function (data, event) {
            $('body').loader('show');
            var self = this;
            // Obtenemos la url base de la tienda
            const baseUrl = window.checkoutConfig.payment.atixpaymentgateway.store_url;
            // Creamos la url completa para crear el pedido
            const urlPaymentInternal = `${baseUrl}/atixpaymentgateway/payment/submit`;

            // Evitamos que la página se recargue al hacer la acción
            if (event) {
                event.preventDefault();
            }

            // Obtenemos los datos de necesarios para generar el link de pago
            const currency = quote.totals().base_currency_code;
            const totalAmount = quote.totals().base_grand_total;
            const customer = checkoutConfig.customerData;

            // Creamos el query para crear el pedido
            const queryParams = new URLSearchParams({
                quoteId: quote.getItems().map((item) => item.quote_id)[0],
            }).toString();

            if (this.validate() && additionalValidators.validate()) {
                // Enviamos los datos al endpoint de creación del pedido
                fetch(`${urlPaymentInternal}?${queryParams}`)
                    .then((response) => response.json())
                    .then((response) => {
                        $('body').loader('hide');
                        console.log(response.success);
                        // Si es creo el pedido correctamente
                        if (response.success) {
                            // Obtenemos el api_key
                            const api_key = response.api_key;
                            // Obtenemos la url para generar el pago
                            const url = response.url;
                            // Obtenemos el orderId
                            const reference = response.order_id;
                            // Importamos el url y enviamos los datos para genera el link de pago y lo redirigimos
                            
                            // fetch service atix
                            const data = {
                                totalamount: totalAmount,
                                currency: currency,
                                reference: reference,
                                email: customer.email,
                                phone: "",
                                country: "",
                                urlorigi: location.origin,
                                mobile: false,
                                typeconection: {},
                                protocol: location.protocol,
                                navigator: navigator.userAgent,
                            };
            
                            const formData = {
                              User: "wzzzGE38zPk5pUKWd7jhN",
                              Password: "YkSzED4ty92BjMa2SXYsF",
                              Apikey: api_key,
                              Version: "V1.1",
                              Data: JSON.stringify(data),
                            };
                            
                            console.log(formData);
            
                            fetch(url, {
                              method: "POST",
                              body: JSON.stringify(formData),
                            })
                            .then((res) => res.json())
                            .then((res) => {
                              console.log(res);
                              if(res.length > 0) {
                                 location.href = res[0].Url;
                              }
                            })
                            .catch((error) => {
                              console.error(error);
                            });
                        }
                    })
                    .catch((error) => {
                        $('body').loader('hide');
                        console.error(error);
                    });
            }

            return false;
        },
    });
});
