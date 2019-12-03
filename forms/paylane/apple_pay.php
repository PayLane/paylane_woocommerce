<?php if (!defined('ABSPATH')) {
    exit;
}
?>
 
<style>
    .apple-pay-button {
        -webkit-appearance: -apple-pay-button;
        -apple-pay-button-type: buy;
        -apple-pay-button-style: <?php echo $button_style; ?>;
        visibility: hidden;
        display: block;
        width: auto;
        height:50px;
        border: 1px solid black;
        background-image: -webkit-named-image(apple-pay-logo-black);
        background-size: 100% calc(60% + 2px);
        background-repeat: no-repeat;
        background-color: white;
        background-position: 50% 50%;
        border-radius: 5px;
        margin-left:auto;
        margin-right:auto;
        transition: background-color .15s;
        float:right;
    }
    .apple-pay-button.visible {
        visibility: visible;
        padding:10px;
        margin:5px;
    }

    .apple-pay-button:hover{
        cursor:pointer;
    }

    li.wc_payment_method.payment_method_paylane_apple_pay{
        visibility: hidden;
        height:2px;
    }

    @media only screen and (max-width: 768px) {
        .apple-pay-button{
            width:100%;
            margin-bottom:1rem;
        }
    }


</style>


<script type="text/javascript">
"use strict";

let applePayPaymentRequest = {
    countryCode: jQuery('#billing_country option:selected').val(),
    currencyCode: '<?php echo esc_attr($currencyCode); ?>',
    total: {
        label: '<?php echo esc_attr($label); ?>',
        amount: '<?php echo esc_attr($amount); ?>'
    }
};

const applePayPublicApiKey = '<?php echo esc_attr($api_key); ?>';

try{
    if(applePayAvailable){
        showApplePayButton()
    }else{
    }
}catch(err){

}

</script>

<input type="hidden" name="paylane_apple_pay_payload" id="paylane_apple_pay_payload" value=""/>