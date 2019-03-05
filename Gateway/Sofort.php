<?php if ( !defined( 'ABSPATH' ) ) exit;

class Paylane_Gateway_Sofort extends Paylane_Gateway_Base
{
	/**
	 * @var string
	 */
	protected $form_name = 'sofort';

	/**
	 * @var string
	 */
	protected $gateway_id = 'paylane_sofort';

	/**
	 * @return mixed
	 */
	protected function getMethodTitle()
	{
		return $this->get_option('sofort_name', __( 'Sofort', 'wc-gateway-paylane' ) );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('Sofort', 'wc-gateway-paylane');
	}
}