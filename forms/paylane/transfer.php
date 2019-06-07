<?php if ( !defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/../../includes/paylane-banks.php';
$paymentTypes = getBankTransferPaymentTypes();

?>
<div class="paylane-payment-form paylane-payment-form--polish-bank-transfers">
  <div class="paylane-payment-form__text">
    <?php echo __( 'You will be redirected to your bank\'s website payment.', 'wc-gateway-paylane' ); ?>
  </div>


  <div class="paylane-payment-methods-list paylane-payment-methods-list--polish-bank-transfers" data-mc-field-radio="transfer_bank">
    <?php foreach ($paymentTypes as $code => $data) { ?>
      <div class="paylane-payment-methods-list__item">

        <div class="paylane-polish-bank-transfer paylane-polish-bank-transfer--<?php echo strtolower($code); ?>">
          <input class="paylane-polish-bank-transfer__input"
                 type="radio"
                 name="transfer_bank"
                 id="payment_type_<?php echo $code; ?>"
                 value="<?php echo $code; ?>"
          >

          <label class="paylane-polish-bank-transfer__label"
                 for="payment_type_<?php echo $code; ?>"
          >
            <div class="paylane-polish-bank-transfer__text-wrapper">
              <span class="paylane-polish-bank-transfer__text"
                   title="<?php echo $data['label']; ?>"
                   style="background-image: url(<?php echo plugin_dir_url(__DIR__ . '/../../../') . 'assets/images/banks/' . $code . '.png'; ?>);"
              ><?php echo $data['label']; ?></span>
            </div>
          </label>

        </div>
      </div>
    <?php } ?>
  </div>

  <div class="paylane-payment-form__error-message" data-paylane-error-message="transfer_bank"></div>
</div>
