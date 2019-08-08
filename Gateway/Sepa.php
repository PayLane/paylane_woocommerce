<?php if (!defined('ABSPATH')) {
    exit;
}

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
        if (!is_admin()) {
            return $this->modTitle(__('SEPA Direct Debit', 'wc-gateway-paylane'), $this->get_paylane_option('sepa_name'));
        }
        return __('SEPA Direct Debit', 'wc-gateway-paylane');
    }

    /**
     * @return mixed
     */
    protected function getGatewayTitle()
    {
        return __('SEPA Direct Debit', 'wc-gateway-paylane');
    }

    public function get_icon()
    {
        $iconHtml = '';
        if ($this->get_paylane_option('display_payment_methods_logo','yes') == 'yes') {
            $iconHtml = '<img src="' . plugins_url('../assets/images/payment_methods/sepa_h50_w80.png', __FILE__) . '" class="paylane-payment-method-label-logo" alt="sepa">';
        }

        return apply_filters('woocommerce_gateway_icon', $iconHtml, $this->id);
    }
}
