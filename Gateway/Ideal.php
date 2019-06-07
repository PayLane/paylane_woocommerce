<?php if ( !defined( 'ABSPATH' ) ) exit;

class Paylane_Gateway_Ideal extends Paylane_Gateway_Base
{
	/**
	 * @var string
	 */
	protected $form_name = 'ideal';

	/**
	 * @var string
	 */
	protected $gateway_id = 'paylane_ideal';
	
	/**
	 * @return mixed
	 */
	protected function getMethodTitle()
	{
		return $this->get_option( 'ideal_name', __('iDEAL', 'wc-gateway-paylane' ) );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __( 'iDEAL', 'wc-gateway-paylane' );
	}
}