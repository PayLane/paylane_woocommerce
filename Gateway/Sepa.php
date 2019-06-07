<?php if ( !defined( 'ABSPATH' ) ) exit;

class Paylane_Gateway_Sepa extends Paylane_Gateway_Base
{
	/**
	 * @var string
	 */
	protected $form_name = 'sepa';

	/**
	 * @var string
	 */
	protected $gateway_id = 'paylane_sepa_direct_debit';

	/**
	 * @return mixed
	 */
	protected function getMethodTitle()
	{
		return $this->get_option('sepa_name', __( 'SEPA Direct Debit', 'wc-gateway-paylane' ) );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('SEPA Direct Debit', 'wc-gateway-paylane');
	}
}