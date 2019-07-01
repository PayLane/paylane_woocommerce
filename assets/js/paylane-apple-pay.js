"use strict";

let applePayAvailable = false;

const onAuthorized = function (paymentResult, completion) {
    jQuery('#paylane_apple_pay_payload').val(btoa(unescape(encodeURIComponent(JSON.stringify(paymentResult)))));
    jQuery('input[name="payment_method"]').val('paylane_apple_pay');
    jQuery('#place_order').trigger('click');
    completion(ApplePaySession.STATUS_SUCCESS);
}

function showApplePayButton() {
    jQuery('.apple-pay-button').addClass('visible')
    if(jQuery('input[name="payment_method"]').val() == 'paylane_apple_pay'){
        jQuery('input[name="payment_method"][value!="paylane_apple_pay"]:first').trigger('click').trigger('click')
    }
}



function applePayButtonClicked() {
    const applePaySession = PayLane.applePay.createSession(
        applePayPaymentRequest,
        onAuthorized,
        function (result) {
            console.error(result);
        }
    );
}



window.addEventListener("load", function () {
   
    PayLane.setPublicApiKey(applePayPublicApiKey);

    PayLane.applePay.checkAvailability((available) => {
        if (!available) {
            return console.warn('Apple Pay not available');
        }
        applePayAvailable = true;
        showApplePayButton();
    });
});
