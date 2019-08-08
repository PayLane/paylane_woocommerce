<?php if (!defined('ABSPATH')) {
    exit;
}

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
        if (!is_admin()) {
            return $this->modTitle(__('iDEAL', 'wc-gateway-paylane'), $this->get_paylane_option('ideal_name'));
        }
        return __('iDEAL', 'wc-gateway-paylane');
    }

    /**
     * @return mixed
     */
    protected function getGatewayTitle()
    {
        return __('iDEAL', 'wc-gateway-paylane');
    }

    public function get_icon()
    {
        $iconHtml = '';
        if ($this->get_paylane_option('display_payment_methods_logo','yes') == 'yes') {
            $iconHtml = '<img src="' . plugins_url('../assets/images/payment_methods/ideal_h50_w80.png', __FILE__) . '" class="paylane-payment-method-label-logo" alt="ideal">';
        }

        return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
    }
}
