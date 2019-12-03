<?php if ( !defined( 'ABSPATH' ) ) exit;

$fields = array(
  'sepa_account_country' => array(
    'label' => __( 'Account Country', 'wc-gateway-paylane' ),
    'required' => true,
    'type' => 'country',
    'class' => array('form-row-wide'),
    'validate' => array(),
    'autocomplete' => 'country',
    'priority' => 110,
  ),
  'sepa_iban' => array(
    'label' => __( 'IBAN', 'wc-gateway-paylane' ),
    'required' => true,
    'type' => 'text',
    'class' => array('form-row-wide'),
    'validate' => array('iban'),
    'autocomplete' => 'iban',
    'priority' => 120,
  ),
  'sepa_bic' => array(
    'label' => __( 'BIC', 'wc-gateway-paylane' ),
    'required' => true,
    'type' => 'text',
    'class' => array('form-row-wide'),
    'validate' => array('bic'),
    'autocomplete' => 'bic',
    'priority' => 130,
  ),
  'sepa_account_holder' => array(
    'label' => __( 'Account Holder', 'wc-gateway-paylane' ),
    'required' => true,
    'type' => 'text',
    'class' => array('form-row-wide'),
    'validate' => array('text'),
    'autocomplete' => 'no',
    'priority' => 100,
  ),
);

?>

<div class="paylane-payment-form paylane-payment-form--sepa-direct-debit">
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
</div>
