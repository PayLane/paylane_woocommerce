<?php if ( !defined( 'ABSPATH' ) ) exit;

class Paylane_Gateway_BankTransfer extends Paylane_Gateway_Base
{
	/**
	 * @var string
	 */
	protected $form_name = 'transfer';

	/**
	 * @var string
	 */
	protected $gateway_id = 'paylane_polish_bank_transfer';

	/**
	 * @return mixed
	 */
	protected function getMethodTitle()
	{
		return $this->get_option( 'transfer_name', __( 'Bank transfer', 'wc-gateway-paylane' ) );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('Bank transfer', 'wc-gateway-paylane');
	}
}