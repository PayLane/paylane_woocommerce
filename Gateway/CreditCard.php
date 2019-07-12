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
		return $this->get_paylane_option( 'credit_card_name',  __( 'Credit Card', 'wc-gateway-paylane' ) );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('Credit Card', 'wc-gateway-paylane'); 
	}
}