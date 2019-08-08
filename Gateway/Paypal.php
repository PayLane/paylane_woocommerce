<?php if (!defined('ABSPATH')) {
    exit;
}

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
        if (!is_admin()) {
            return $this->modTitle(__('PayPal', 'wc-gateway-paylane'), $this->get_paylane_option('paypal_name'));
        }
        return __('PayPal', 'wc-gateway-paylane');

    }

    /**
     * @return mixed
     */
    protected function getGatewayTitle()
    {
        return __('PayPal', 'wc-gateway-paylane');
    }

    public function get_icon()
    {
        $iconHtml = '';
        if ($this->get_paylane_option('display_payment_methods_logo','yes') == 'yes') {
            $iconHtml = '<img src="' . plugins_url('../assets/images/payment_methods/paypal_h50_w80.png', __FILE__) . '" class="paylane-payment-method-label-logo" alt="paypal">';
        }

        return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
    }
}
