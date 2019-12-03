<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<div class="paylane-payment-form paylane-payment-form--ideal">
    <div class="paylane-payment-form__text">
        <?php echo __( 'You will be redirected to your bank\'s website payment.', 'wc-gateway-paylane' ); ?>
    </div>
    <?php echo esc_html($banks); ?>
</div>