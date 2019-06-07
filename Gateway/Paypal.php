<?php if ( !defined( 'ABSPATH' ) ) exit;

class Paylane_Gateway_Paypal extends Paylane_Gateway_Base
{
	/**
	 * @var string
	 */
	protected $form_name = 'paypal';

	/**
	 * @var string
	 */
	protected $gateway_id = 'paylane_paypal';

	/**
	 * @return mixed
	 */
	protected function getMethodTitle()
	{
		return $this->get_option('paypal_name', __( 'PayPal', 'wc-gateway-paylane' ) );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('PayPal', 'wc-gateway-paylane');
	}
}