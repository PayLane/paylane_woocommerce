<?php

class Paylane_Gateway_CreditCard extends Paylane_Gateway_Base
{
	/**
	 * @var string
	 */
	protected $form_name = 'credit_card';

	/**
	 * @var string
	 */
	protected $gateway_id = 'paylane_credit_card';

	/**
	 * @return mixed
	 */
	protected function getMethodTitle()
	{
		if(!is_admin()){
			return $this->modTitle(__( 'Credit Card', 'wc-gateway-paylane' ), $this->get_paylane_option( 'credit_card_name'));
		}
		return __( 'Credit Card', 'wc-gateway-paylane' );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('Credit Card', 'wc-gateway-paylane'); 
	}

	public function get_icon()
    {
		$iconHtml = '';
		if ($this->get_paylane_option('display_payment_methods_logo','yes') == 'yes') {
			$iconHtml = '<img src="' . plugins_url('../assets/images/payment_methods/mastercard_h50_w80.png', __FILE__) . '" class="paylane-payment-method-label-logo" alt="mastercard"><img src="' . plugins_url('../assets/images/payment_methods/visa_h50_w80.png', __FILE__) . '" class="paylane-payment-method-label-logo" alt="visa">';
		}
        return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
    }
}