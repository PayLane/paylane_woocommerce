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
		if(!is_admin()){
			return $this->modTitle(__( 'Bank transfer', 'wc-gateway-paylane' ), $this->get_paylane_option( 'transfer_name'));
		}
		return __( 'Bank transfer', 'wc-gateway-paylane' );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('Bank transfer', 'wc-gateway-paylane');
	}

	public function get_icon()
    {
		$iconHtml = '';
		
        return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
    }
}