<?php if ( !defined( 'ABSPATH' ) ) exit;

//$date = date("Y");
//$option_years = null;
//
//for ($i = 0; $i < 10; $i++)
//{
//	$option_years .= '<option value="' . $date . '">' . $date . '</option>';
//	$date++;
//}

$fields = array(
    'cc_card_number' => array(
        'label' => __( 'Card number', 'wc-gateway-paylane' ),
        'required' => true,
        'type' => 'text',
        'class' => array('form-row-wide'),
        'validate' => array('card-number'),
        'autocomplete' => 'no',
        'priority' => 100,
        'placeholder' => '1234 1234 1234 1234',
    ),
    'cc_expiration_date' => array(
        'label' => __( 'Expiry Date', 'wc-gateway-paylane' ),
        'required' => true,
        'type' => 'text',
        'class' => array('form-row-wide'),
        'validate' => array('card-expiry-date'),
        'autocomplete' => 'no',
        'priority' => 110,
        'placeholder' => __('MM / YY', 'wc-gateway-paylane'),
        'pattern'=> "(1[0-2]|0[1-9])\/(1[5-9]|2\d)"
    ),
    'cc_security_code' => array(
        'label' => __( 'CVV/CVC', 'wc-gateway-paylane' ),
        'required' => true,
        'type' => 'text',
        'class' => array('form-row-wide'),
        'validate' => array('card-security-code'),
        'autocomplete' => 'no',
        'priority' => 120,
        'placeholder' => __('CVC', 'wc-gateway-paylane'),
    ),
    'cc_name_on_card' => array(
        'label' => __( 'Card holder name', 'wc-gateway-paylane' ),
        'required' => true,
        'type' => 'text',
        'class' => array('form-row-wide'),
        'validate' => array('card-holder-name'),
        'autocomplete' => 'name',
        'priority' => 130,
    ),
);

?>

<div class="paylane-payment-form paylane-payment-form--credit-card">
    <?php

    foreach ( $fields as $key => $field ) {
      ?>

      <div class="paylane-payment-form__field">

      <?php
        woocommerce_form_field( $key, $field );
      ?>
        <div class="paylane-payment-form__error-message" data-paylane-error-message="<?php echo esc_attr($key) ?>"></div>
      </div>

      <?php
    }
    ?>

  <div class="paylane-payment-form__error-message" data-paylane-error-message="credit_card"></div>
</div>

<input type="hidden" id="payment_params_token" name="payment_params_token" value="">

<script type="text/javascript">
  //<![CDATA[
  window.addEventListener("load", function () {
    PayLane.setPublicApiKey("<?php echo esc_attr($api_key); ?>");
  });
  //]]>
</script>
