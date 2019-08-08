<?php if (!defined('ABSPATH')) {
    exit;
}

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
        if (!is_admin()) {
            return $this->modTitle(__('Sofort', 'wc-gateway-paylane'), $this->get_paylane_option('sofort_name'));
        }
        return __('Sofort', 'wc-gateway-paylane');
    }

    /**
     * @return mixed
     */
    protected function getGatewayTitle()
    {
        return __('Sofort', 'wc-gateway-paylane');
    }

    public function get_icon()
    {
        $iconHtml = '';
        if ($this->get_paylane_option('display_payment_methods_logo','yes') == 'yes') {
            $iconHtml = '<img src="' . plugins_url('../assets/images/payment_methods/sofort_h50_w80.png', __FILE__) . '" class="paylane-payment-method-label-logo" alt="sofort">';
        }

        return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
    }
}
