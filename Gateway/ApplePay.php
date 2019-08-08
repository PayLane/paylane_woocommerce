<?php if (!defined('ABSPATH')) {
    exit;
}

class Paylane_Gateway_ApplePay extends Paylane_Gateway_Base
{
    /**
     * @var string
     */
    protected $form_name = 'apple_pay';

    /**
     * @var string
     */
	protected $gateway_id = 'paylane_apple_pay';
	
	public function __construct(){
		parent::__construct();

		add_action('woocommerce_review_order_before_submit',array($this,'add_apple_pay_button'));

	}

    /**
     * @return mixed 
     */
    protected function getMethodTitle()
    {
        if(!is_admin()){
            return $this->modTitle(__( 'Apple Pay', 'wc-gateway-paylane' ), $this->get_paylane_option( 'apple_pay_name'));
		}
		return __( 'Apple Pay', 'wc-gateway-paylane' );
     
    }

    /**
     * @return mixed
     */
    protected function getGatewayTitle()
    {
        return __('Apple Pay', 'wc-gateway-paylane');
	}
	
	private function getButtonLanguage(){
		$lang = $this->get_paylane_option('apple_pay_language');
		if($lang == 'auto'){
			return substr(get_locale(), 0,2);
		}

		return $lang;
    }
    
    protected function isCorrectpayload($payload){
        if(is_null($payload)){
            return false;
        }
        if(!isset($payload['customer'])){
            return false;
        }
        if(!isset($payload['card'])){
            return false;
        }
        if(!isset($payload['card']['token'])){
            return false;
        }
        return true;
    }

    public function getPreparedForm()
    {
        global $woocommerce;

        wp_enqueue_script('woocommerce_paylane_api_script', 'https://js.paylane.com/v1/', array());
        $form = $this->get_form('apple_pay', array(
            'api_key' => $this->get_paylane_option('api_key_val'),
            'button_style' => $this->get_paylane_option('apple_pay_style'),
            'button_language' => $this->getButtonLanguage(),
            'currencyCode' => get_woocommerce_currency(),
            'label' => get_bloginfo('name'), 
            'amount' => $woocommerce->cart->get_total(''),
		));

		wp_register_script( 'woocommerce_paylane_apple_pay_script', plugins_url('../assets/js/paylane-apple-pay.js', __FILE__) );
		wp_enqueue_script('woocommerce_paylane_apple_pay_script');
		
		return $form;
    }
    

	function add_apple_pay_button(){
		echo '<span lang="'.$this->getButtonLanguage().'" class="apple-pay-button" id="applepay_button_sale" onclick="applePayButtonClicked()"></span>';
	}

}
