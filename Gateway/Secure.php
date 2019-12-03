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
		if(!is_admin()){
			return $this->modTitle(__( 'Fast transfer with Polskie ePłatności', 'wc-gateway-paylane' ), $this->get_paylane_option( 'secure_form_name'), true);
		}
		return __( 'Secure Form', 'wc-gateway-paylane' );
	}

	/**
	 * @return mixed
	 */
	protected function getGatewayTitle()
	{
		return __('Fast transfer with Polskie ePłatności', 'wc-gateway-paylane');
	}

	public function get_icon()
    {
        $iconUrl = plugins_url('../assets/pep.svg', __FILE__);
        $iconHtml = '';
        if ($this->get_paylane_option('display_payment_methods_logo','yes') == 'yes') {
            $iconHtml .= '<img src="' . $iconUrl . '" class="paylane-payment-method-label-logo" alt="' . esc_attr__(
                'Polskie ePłatności image', 'woocommerce'
            ) . '">';
        }

        return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
    }


}