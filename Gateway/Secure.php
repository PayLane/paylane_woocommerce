<?php if ( !defined( 'ABSPATH' ) ) exit;

class Paylane_Gateway_Secure extends Paylane_Gateway_Base
{
	/**
	 * @var string
	 */
	protected $form_name = 'secure_form';

	/**
	 * @var string
	 */
	protected $gateway_id = 'paylane_secure_form';

	/**
	 * @return mixed
	 */
	protected function getMethodTitle()
	{
		return $this->get_option('secure_form_name', __( 'Secure', 'wc-gateway-paylane' ) );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('Secure', 'wc-gateway-paylane');
	}
}