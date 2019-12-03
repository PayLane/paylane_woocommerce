<?php 
/**
 * Plugin Name: Polskie ePłatności Online Payment Gateway for WooCommerce 
 * Description: Polskie ePłatności Online payment module for WooCommerce.
 * Version: 2.2.3
 * Author: Polskie ePłatności Online
 * Author URI: https://paylane.pl
 * Plugin URI: https://github.com/PayLane/paylane_woocommerce
 * Text Domain: wc-gateway-paylane
 * Requires at least: 4.4
 * Tested up to: 5.2.3
 * Requires PHP: 5.6
 * WC requires at least: 2.6
 * WC tested up to: 3.7
 **/

if (!defined('ABSPATH')) {
    exit;
}


add_filter('woocommerce_notice_types', 'wcpl_add_paylane_notice_type');
add_action('before_woocommerce_pay', 'wcpl_paylane_js_validation', 10, 0);
add_action('woocommerce_checkout_before_order_review', 'wcpl_paylane_js_validation', 10, 0);

function wcpl_add_paylane_notice_type($notice_types)
{
    $notice_types[] = "paylane_error";
    return $notice_types;
}

function wcpl_gp_param_isset($method, $key, $defaultValue = null)
{
    if (isset($method[$key])) {
        return $method[$key];
    }

    return $defaultValue;
}

function wcpl_string($value)
{
    return filter_var($value, FILTER_SANITIZE_STRING);
}

function wcpl_float($value)
{
    return filter_var($value, FILTER_SANITIZE_STRING);
}

function wcpl_paylane_js_validation()
{
    $json = json_encode(require_once __DIR__ . '/includes/paylane-js-validation-messages.php');

    echo <<<EOF
<script>
	const PAYLANE_VALIDATION_MESSAGES = $json
</script>
EOF;

}

function wcpl_init_paylane()
{
    // Localisation
    load_plugin_textdomain('wc-gateway-paylane', false, dirname(plugin_basename(__FILE__)) . '/languages');
    add_filter('plugin_row_meta', 'wcpl_paylane_plugin_row_meta', 20, 4);
    require_once __DIR__ . '/includes/paylane-errors.php';
    require_once __DIR__ . '/includes/logger.php';

    if (!class_exists('WC_Payment_Gateway')) {
        add_action('admin_init', 'wcpl_child_plugin_has_parent_plugin');

        function wcpl_child_plugin_has_parent_plugin()
        {
            if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('woocommerce/woocommerce.php')) {
                add_action('admin_notices', 'wcpl_child_plugin_notice');
                deactivate_plugins(plugin_basename(__FILE__));

                if (isset($_GET['activate'])) {
                    unset($_GET['activate']);
                }
            }
        }

        function wcpl_child_plugin_notice()
        {
            require_once __DIR__ . '/views/admin/notices/woocommerce-is-missing.php';
        }

        return;
    }

    /**
     * @param $plugin_meta
     * @param $plugin_file
     * @param $plugin_data
     * @param $status
     * @return mixed
     */
    function wcpl_paylane_plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status)
    {
        if (basename($plugin_file) === basename(__FILE__)) {
            $url = 'https://paylane.pl/wyprobuj/?utm_source=woocommerce-plugin';
            $label = __('Create account', 'wc-gateway-paylane');
            $icon = 'dashicons-id-alt';

            $plugin_meta[] = sprintf('<a href="%s" target="_blank"><span class="dashicons %s"></span>%s</a>', $url, $icon, $label);
        }

        return $plugin_meta;
    }

    class Paylane_Woocommerce_Tools
    {
        public static function getIdealBanks($login, $password)
        {
            if (!class_exists('PayLaneRestClient')) {
                require_once __DIR__ . '/includes/paylane-rest.php';
            }

            $client = new PayLaneRestClient($login, $password);
            $codes = null;

            try
            {
                $status = $client->idealBankCodes();
            } catch (Exception $e) {
                WCPL_Logger::log("[getIdealBanks]\nException in idealBankCodes\n" . $e->getMessage(), 'error');
            }

            if (!$client->isSuccess()) {
                // WCPL_Logger::log("[getIdealBanks]\nThis API method is not allowed for this merchant account",'notice');

                $codes = __('This API method is not allowed for this merchant account.', 'wc-gateway-paylane');
            } else {
                $codes .= '<select name="bank-code">';
                foreach ($status['data'] as $bank) {
                    $codes .= '<option value="' . $bank['bank_code'] . '">' . $bank['bank_name'] . '</option>';
                }
                $codes .= '</select>';
            }

            return $codes;
        }
    }

    class WC_Gateway_Paylane extends WC_Payment_Gateway
    {
        public static $is_loaded = false;

        /**
         * @var string
         */
        const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';

        /**
         * @var string
         */
        const PAYMENT_METHOD_SECURE_FORM = 'secure_form';

        /**
         * @var string
         */
        const PAYMENT_METHOD_BANK_TRANSFER = 'transfer';

        /**
         * @var string
         */
        const PAYMENT_METHOD_SEPA = 'sepa';

        /**
         * @var string
         */
        const PAYMENT_METHOD_SOFORT = 'sofort';

        /**
         * @var string
         */
        const PAYMENT_METHOD_PAYPAL = 'paypal';

        /**
         * @var string
         */
        const PAYMENT_METHOD_IDEAL = 'ideal';

        /**
         * @var string
         */
        const PAYMENT_METHOD_APPLEPAY = 'apple_pay';

        /**
         * @var string
         */
        const ORDER_STATUS_PENDING = 'pending';

        /**
         * @var string
         */
        const ORDER_STATUS_PROCESSING = 'processing';

        /**
         * @var string
         */
        const ORDER_STATUS_ON_HOLD = 'on-hold';

        /**
         * @var string
         */
        const ORDER_STATUS_COMPLETED = 'completed';

        /**
         * @var string
         */
        const ORDER_STATUS_CANCELLED = 'cancelled';

        /**
         * @var string
         */
        const ORDER_STATUS_REFUNDED = 'refunded';

        /**
         * @var string
         */
        const ORDER_STATUS_FAILED = 'failed';

        /**
         * Constructor for the gateway.
         *
         * @access public
         *
         *
         * @global type $woocommerce
         */
        private static $paylane_methods = array(
            WC_Gateway_Paylane::PAYMENT_METHOD_SECURE_FORM => 'Secure Form',
            WC_Gateway_Paylane::PAYMENT_METHOD_CREDIT_CARD => 'Credit Card',
            WC_Gateway_Paylane::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
            WC_Gateway_Paylane::PAYMENT_METHOD_SEPA => 'SEPA',
            WC_Gateway_Paylane::PAYMENT_METHOD_SOFORT => 'Sofort',
            WC_Gateway_Paylane::PAYMENT_METHOD_PAYPAL => 'PayPal',
            WC_Gateway_Paylane::PAYMENT_METHOD_IDEAL => 'iDEAL',
            WC_Gateway_Paylane::PAYMENT_METHOD_APPLEPAY => 'Apple Pay',
        );

        private $order_status_to_id = array(
            WC_Gateway_Paylane::ORDER_STATUS_PENDING => 0,
            WC_Gateway_Paylane::ORDER_STATUS_PROCESSING => 1,
            WC_Gateway_Paylane::ORDER_STATUS_ON_HOLD => 2,
            WC_Gateway_Paylane::ORDER_STATUS_COMPLETED => 3,
            WC_Gateway_Paylane::ORDER_STATUS_CANCELLED => 4,
            WC_Gateway_Paylane::ORDER_STATUS_REFUNDED => 5,
            WC_Gateway_Paylane::ORDER_STATUS_FAILED => 6,
        );

        private static $instance = null;

        public static function instance()
        {
            if (null === self::$instance) {
                self::$instance = new WC_Gateway_Paylane;
            }

            return self::$instance;
        }

        public function __construct()
        {
            global $woocommerce;

            $this->id = 'paylane'; //__('paylane', 'wc-gateway-paylane');
            $this->method_title = __('Polskie ePłatności Online', 'wc-gateway-paylane');
            $this->has_fields = true;
            $this->notify_link = add_query_arg('wc-api', 'WC_Gateway_Paylane', home_url('/'));
            $this->notify_link_3ds = add_query_arg('wc-api', 'WC_Gateway_Paylane_3ds', home_url('/'));
            $this->supports = array(
                'products',
                'refunds',
                'subscriptions',
                'subscription_cancellation',
                'subscription_suspension',
                'subscription_reactivation',
                'subscription_amount_changes',
                'subscription_date_changes',
                'subscription_payment_method_change',
            );

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->payment_method = $this->get_option('payment_method');
            $this->secure_form = $this->get_option('secure_form');
            $this->merchant_id = $this->get_option('merchant_id');
            $this->fraud_check = $this->get_option('fraud_check');
            $this->ds_check = 'true';//$this->get_option('3ds_check');
            $this->first_name = '';
            $this->last_name = '';
            $this->enable_notification = 'yes';

            $this->init_form_fields();
            $this->init_settings();
            $this->add_actions();
            $this->add_filters();

        }

        /**
         * Init
         */
        public function init()
        {
            $this->load_depedencies();

            if ('SecureForm' === $this->get_option('connection_mode')) {
                $this->init_secure_form();
            } else {
                $this->init_api();
            }
        }

        /**
         * Register Custom css & js
         */
        public function paylane_payment_style()
        {
            if (is_checkout()) {
                wp_register_style(
                    'paylane-woocommerce', plugins_url(
                        'assets/css/paylane-woocommerce-' . $this->get_option('design') . '.css', __FILE__
                    ), [], '223_' . $this->get_option('design'), 'all'
                );
                wp_enqueue_style('paylane-woocommerce');
                wp_enqueue_script('jquery-mask-form', plugin_dir_url(__FILE__) . 'assets/js/jquery.mask.min.js', array('jquery'));
                wp_register_script(
                    'paylane-woocommerce-script', plugin_dir_url(__FILE__) . 'assets/js/paylane-woocommerce.js', array('jquery', 'jquery-payment', 'jquery-mask-form'),
                    '223', true
                );
                wp_enqueue_script(
                    'paylane-woocommerce-script'
                );

            }

        }

        //Main function which sends data to Polskie ePłatności service and get response
        function data_handler()
        {
            $communication_id = wcpl_string(wcpl_gp_param_isset($_POST, 'communication_id'));
            $token = wcpl_string(wcpl_gp_param_isset($_POST, 'token'));

            if (isset($_POST['content']) && ($this->enable_notification === 'yes')) {

                if (!isset($_POST['communication_id']) || empty($_POST['communication_id'])) {
                    WCPL_Logger::log("[data_handler]\nEmpty communication id", 'error');

                    die('Empty communication id');
                }

                if(is_null($token)){
                    die('Empty token');
                }

                $content = $_POST['content'];

                if (!empty(($this->get_option('notification_token_PayLane'))) && ($this->get_option('notification_token_PayLane') !== $token)) {
                    WCPL_Logger::log("[data_handler]\nWrong token\n" . "communication_id: " . $communication_id, 'error');
                    die('Wrong token');
                }

                try
                {
                    $this->handle_notification($content, $token, $communication_id);
                } catch (Exception $e) {
                    WCPL_Logger::log("[data_handler] Exception in handle_notification\n" . $e->getMessage() . "\ncommunication_id: " . $communication_id, 'error');
                    die($e->getMessage());
                }
                unset($_POST['content']);
            }

            $type = wcpl_string(wcpl_gp_param_isset($_GET, 'type'));

            if (!$type) {
                $this->response_check();
            } else {
                $order_id = wcpl_string(wcpl_gp_param_isset($_GET, 'order_id'));

                if ($type == "secure_form") {
                    $this->send_payment_data($order_id);
                    unset($_GET['order_id']);
                } else {
                    require_once __DIR__ . '/includes/paylane-rest.php';
                    $client = new PayLaneRestClient($this->get_option('login_PayLane'), $this->get_option('password_PayLane'));
                    if (!session_id()) {
                        session_start();
                    }

                    $params = $_SESSION['paylane-data'];
                    try
                    {
                        switch ($type) {
                            case WC_Gateway_Paylane::PAYMENT_METHOD_CREDIT_CARD:
                                // if ($this->get_option('3ds_check') == 'true') {
                                    try
                                    {
                                        $result = $client->checkCard3DSecureByToken($params);
                                    } catch (Exception $e) {
                                        WCPL_Logger::log("[data_handler]\nException in checkCard3DSecureByToken\n" . $e->getMessage() . "\ncommunication_id: " . $communication_id, 'error');
                                        $this->print_error_page($e->getMessage());
                                        exit;
                                    }

                                    if ($client->isSuccess()) {
                                        if (true == $result['is_card_enrolled']) {
                                            wp_redirect($result['redirect_url']);
                                            exit;
                                        }
                                        WCPL_Logger::log("[data_handler]\ncheckCard3DSecure CARD NOT ENROLLER", 'warning');
                                        $this->finish_order($order_id, $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED), __('Card not enrolled in 3-D Secure', 'wc-gateway-paylane'));
                                        $this->print_error_page(__('Card not enrolled in 3-D Secure', 'wc-gateway-paylane'));
                                        exit;
                                    } else {
                                        WCPL_Logger::log("[data_handler]\ncheckCard3DSecureByToken is NOT SUCCESS\norder_id: " . $order_id, 'warning');
                                        $this->finish_order($order_id, $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED), __('3-D Secure verification failed', 'wc-gateway-paylane'));
                                        $this->print_error_page(__('3-D Secure verification failed', 'wc-gateway-paylane'));
                                        exit;
                                    }
                                // } else {
                                //     $status = $client->cardSaleByToken($params);
                                // }
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_SEPA:
                                $status = $client->directDebitSale($params);
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_IDEAL:
                                $status = $client->idealSale($params);
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_BANK_TRANSFER:
                                $status = $client->bankTransferSale($params);
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_SOFORT:
                                $status = $client->sofortSale($params);
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_PAYPAL:
                                $status = $client->paypalSale($params);
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_APPLEPAY:
                                $status = $client->applePaySale($params);
                                break;
                        }
                    } catch (Exception $e) {
                        WCPL_Logger::log("[data_handler]\nException in the method " . $type . "\n" . $e->getMessage() . "\ncommunication_id: " . $communication_id, 'error');
                        $this->print_error_page($e->getMessage());
                        exit;
                    }

                    if ($client->isSuccess()) {
                        WCPL_Logger::log("[data_handler]\nPayment SUCCESS\nmethod: " . $type . "\nsale_id: " . $status['id_sale'] . "\norder_id: " . $order_id);

                        switch ($type) {
                            case WC_Gateway_Paylane::PAYMENT_METHOD_CREDIT_CARD:
                                echo __("Success, id_sale:", 'wc-gateway-paylane') . " {$status['id_sale']} \n";
                                $this->set_order_paylane_id($order_id, $status['id_sale']);
                                $this->finish_order($order_id, $this->get_option('status_successful_order'));
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_SEPA:
                                echo __("Success, id_sale:", 'wc-gateway-paylane') . " {$status['id_sale']} \n";
                                $this->set_order_paylane_id($order_id, $status['id_sale']);
                                $this->finish_order($order_id, $this->get_option('status_successful_order'));
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_IDEAL:
                                wp_redirect($status['redirect_url']);
                                exit;
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_BANK_TRANSFER:
                                wp_redirect($status['redirect_url']);
                                exit;
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_SOFORT:
                                wp_redirect($status['redirect_url']);
                                exit;
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_PAYPAL:
                                wp_redirect($status['redirect_url']);
                                exit;
                                break;

                            case WC_Gateway_Paylane::PAYMENT_METHOD_APPLEPAY:
                                echo __("Success, id_sale:", 'wc-gateway-paylane') . " {$status['id_sale']} \n";
                                $this->set_order_paylane_id($order_id, $status['id_sale']);
                                $this->finish_order($order_id, $this->get_option('status_successful_order'));
                                break;
                        }
                    } else {
                        $error_message = '';

                        if (isset($status['error']['id_error'])) {
                            $error_message .= __('Error ID:', 'wc-gateway-paylane') . " {$status['error']['id_error']} <br>";
                        }

                        $errorNumber = null;
                        if (isset($status['error']['error_number'])) {
                            $errorNumber = $status['error']['error_number'];
                            $error_message .= __('Error number:', 'wc-gateway-paylane') . " {$status['error']['error_number']} <br>";
                        }

                        if (isset($status['error']['error_description'])) {
                            $error_message .= __('Error description:', 'wc-gateway-paylane') . " {$this->translate_error_description($status['error']['error_description'], $errorNumber)}";
                        }

                        WCPL_Logger::log("[data_handler]\n" . $type . " is NOT success \ncommunication_id: " . $communication_id . "\nsale_id: " . $status['id_sale'] . "\norder_id: " . $order_id . "\n" . $error_message, 'error');

                        $this->finish_order($order_id, $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED), $error_message);
                        $this->print_error_page($error_message);

                        exit;
                    }
                }
            }
            exit;
        }

        /**
         * Check response from Polskie ePłatności service and proceed it to finish orderd
         */
        function response_check()
        {
            if (isset($_POST['description'])) {
                $order_id = wcpl_string(wcpl_gp_param_isset($_POST, 'description'));
            } else {
                $order_id = wcpl_string(wcpl_gp_param_isset($_GET, 'description'));
            }

            WCPL_Logger::log("[response_check]\nResponse\nGET: " . WCPL_Logger::jsonifySecure($_GET) . "\nPOST: " . WCPL_Logger::jsonifySecure($_POST), 'info');

            $type = get_post_meta($order_id, 'paylane-type', true);
            $redirect_version = $this->get_option('paylane_redirect_version');

            $_data_method = $_GET;
            if ($redirect_version == 'POST') {
                $_data_method = $_POST;
            }

            if(empty($_POST) && isset($_GET['correct']) && isset($_GET['id_sale'])){
                $_data_method = $_GET;
            }

            $response['status'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'status'));
            $response['description'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'description'));
            $response['amount'] = wcpl_float(wcpl_gp_param_isset($_data_method, 'amount'));
            $response['currency'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'currency'));
            $response['hash'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'hash'));

            $id_sale = wcpl_string(wcpl_gp_param_isset($_data_method, 'id_sale'));

            $id_error = wcpl_string(wcpl_gp_param_isset($_data_method, 'id_error'));
            $error_code = wcpl_string(wcpl_gp_param_isset($_data_method, 'error_code'));
            $error_text = wcpl_string(wcpl_gp_param_isset($_data_method, 'error_text'));


            $error_message = null;
            if (isset($_data_method['id_error']) || isset($_data_method['error_code'])) {
                $response['id_error'] = $id_error;
                $error_message .= __("Error ID:", 'wc-gateway-paylane') . esc_attr($id_error) ." <br>";

                $errorNumber = null;
                if (isset($_data_method['error_code'])) {
                    $errorNumber = $error_code;
                    $error_message .= __("Error number:", 'wc-gateway-paylane') . esc_attr($_data_method['error_code']). " <br>";
                }

                if (isset($_data_method['error_text'])) {
                    $error_message .= __("Error description:", 'wc-gateway-paylane') . esc_attr($this->translate_error_description($error_text, $errorNumber));
                }
            } else {
                if (!isset($_data_method['id_sale'])) {
                    WCPL_Logger::log("[response_check]\nPayment canceled \norder_id: " . $order_id, 'warning');
                    $order = new WC_Order($order_id);
                    $this->finish_order($order_id, $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED), __('Payment canceled', 'wc-gateway-paylane'));
                    $this->print_error_page(__('Payment canceled', 'wc-gateway-paylane'));
                    exit;
                } else {
                    $response['id_sale'] = $id_sale;
                    $this->set_order_paylane_id($response['description'], $response['id_sale']);
                    WCPL_Logger::log("[response_check]\n" . $response['description'] . "\norder_id: " . $order_id . "\nsale_id: " . $response['id_sale']);
                }
            }

            if (!isset($error_message)) {
                $hash_data = array(
                    'status' => $response['status'],
                    'description' => $response['description'],
                    'amount' => $response['amount'],
                    'id' => $response['id_sale'],
                );

                $hcurrency = null;
                if(!isset($response['status']) && isset($response['correct']) && $response['correct'] == '1'){
                    if(!isset($response['currency']) || empty($hcurrency)){
                        $hcurrency = $response['currency_code'];
                    }else{
                        $hcurrency = $response['currency'];
                    } 
                }

                $hash = $this->hash($hash_data, 'response', $type, $hcurrency);

                if(!isset($response['status']) && isset($response['correct']) && $response['correct'] == '1'){
                    $response['status'] = 'PERFORMED';
                }

                if ($hash == $response['hash']) {
                    if ($response['status'] != 'ERROR') {
                        if ($response['status'] == 'PENDING') {
                            $this->finish_order(
                                $response['description'],
                                $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_PENDING),
                                __('Payment awaiting confirmation', 'wc-gateway-paylane')
                            );
                            WCPL_Logger::log("[response_check]\nPayment awaiting confirmation\norder_id: " . $order_id . "\nsale_id: " . $response['id_sale']);
                        } else {
                            @session_start();
                            $this->finish_order($response['description'], $this->get_option('status_successful_order'));
                            WCPL_Logger::log("[response_check]\nFinish order: " . $response['description'] . "\norder_id: " . $order_id . "\nsale_id: " . $response['id_sale']);
                        }
                    }
                } else {
                    WCPL_Logger::log("[response_check]\nWrong hash \norder_id: " . $order_id . "\nsale_id: " . $response['id_sale'] . "\ndescription: " . $response['description']."\nh0: ".$hash."\nh1:".$response['hash']."\nhd:".json_encode($hash_data)." | ".$type." | ".$response['currency'], 'error');
                    $this->finish_order($response['description'], $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED), __('Wrong hash', 'wc-gateway-paylane'));
                    $this->print_error_page(__('Wrong hash', 'wc-gateway-paylane'));
                }
            } else {
                WCPL_Logger::log("[response_check]\n" . $error_message . "\norder_id: " . $order_id . "\nsale_id: " . $response['id_sale'] . "\ndescription: " . $response['description'], 'error');
                $this->finish_order($response['description'], $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED), $error_message);
                $this->print_error_page($error_message);
            }
        }

        function response_check_3ds()
        {
            WCPL_Logger::log("[response_check_3ds]\nResponse\nGET: " . WCPL_Logger::jsonifySecure($_GET) . "\nPOST: " . WCPL_Logger::jsonifySecure($_POST), 'info');

            $order_id = (isset($_POST['description'])) ? intval($_POST['description']) : intval($_GET['description']);
            $type = get_post_meta($order_id, 'paylane-type', true);
            $redirect_version = $this->get_option('paylane_redirect_version');

            $response = array();
            $response['id'] = '';
            $error_message = '';

            $_data_method = $_GET;
            $response['hash'] = (wcpl_gp_param_isset($_data_method, 'hash'));
            if(is_null($response['hash'])){
                $_data_method = $_POST;
                $response['hash'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'hash'));
            }else{
                $response['hash'] = wcpl_string($response['hash']);
            }
            
            $response['status'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'status'));
            $response['description'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'description'));
            $response['amount'] = wcpl_float(wcpl_gp_param_isset($_data_method, 'amount'));
            $response['currency'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'currency'));
            
            if ($response['status'] !== 'ERROR') {
                $response['id'] = wcpl_string(wcpl_gp_param_isset($_data_method, 'id_3dsecure_auth'));
            } else {
                $error_message .= " Error: " . esc_attr($response['status']);
            }


            $hash_data = array(
                'status' => $response['status'],
                'description' => $response['description'],
                'amount' => $response['amount'],
                'id' => $response['id'],
            );
            $calc_hash = $this->hash($hash_data, 'response', $type, $response['currency']);

            if ($calc_hash !== $response['hash']) {
                $error_message .= __('Error: wrong hash', 'wc-gateway-paylane');
            }

            if ($response['status'] === 'ERROR' || $error_message != "") {
                $error_message .= ' ' . __('Error, 3-D auth transaction declined', 'wc-gateway-paylane');
            } else {
                require_once __DIR__ . '/includes/paylane-rest.php';
                $client = new PayLaneRestClient($this->get_option('login_PayLane'), $this->get_option('password_PayLane'));
                @session_start();

                try
                {
                    $status = $client->saleBy3DSecureAuthorization(array('id_3dsecure_auth' => $response['id']));
                } catch (Exception $e) {
                    WCPL_Logger::log("[response_check_3ds]\nException in saleBy3DSecureAuthorization\n" . $e->getMessage() . "\nresponse_id: " . $response['id'], 'error');
                    $this->finish_order(
                        $response['description'], $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED),
                        $e->getMessage()
                    );
                    $this->print_error_page($e->getMessage());
                    exit;
                }

                if ($client->isSuccess()) {
                    $this->set_order_paylane_id($response['description'], $status['id_sale']);
                    WCPL_Logger::log("[response_check_3ds]\nsaleBy3DSecureAuthorization SUCCESS\nresponse_id: " . $response['id']);
                } else {
                    $error_message .= __('Error 3-D Secure payment', 'wc-gateway-paylane');
                }
            }

            if ($error_message == "") {
                $hash_data = array("status" => $response['status'], "description" => $response['description'], "amount" => $response['amount'], "id" => $response['id']);
                $hash = $this->hash($hash_data, "response", $type, $response['currency']);

                if ($hash == $response['hash']) {
                    if ($response['status'] != 'ERROR') {
                        if ($response['status'] == 'PENDING') {
                            $this->finish_order(
                                $response['description'],
                                $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_PENDING),
                                __("Payment awaiting confirmation", 'wc-gateway-paylane')
                            );
                            WCPL_Logger::log("[response_check_3ds]\nPayment awaiting confirmation\nresponse_id: " . $response['id'] . "\ndescription: " . $response['description'], 'error');
                        } else {
                            @session_start();
                            $this->finish_order($response['description'], $this->get_option('status_successful_order'));
                            WCPL_Logger::log("[response_check_3ds]\nFinish order\nresponse_id: " . $response['id'] . "\ndescription: " . $response['description']);
                        }
                    }
                } else {
                    $this->finish_order(
                        $response['description'],
                        $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED),
                        $error_message
                    );
                    WCPL_Logger::log("[response_check_3ds]\nFinish order: " . $error_message . "\nresponse_id: " . $response['id'] . "\ndescription: " . $response['description'], 'error');
                    $this->print_error_page($error_message);
                }
            } else {
                $this->finish_order(
                    $response['description'], $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED),
                    $error_message
                );
                WCPL_Logger::log("[response_check_3ds]\nFinish order: " . $error_message, 'error');
                $this->print_error_page($error_message);
                exit;
            }
            exit;
        }

        /**
         * Calculating hash
         *
         * @param      $hash_data
         * @param      $data_type
         * @param      $type
         * @param null $currency
         * @return string
         */
        protected function hash($hash_data, $data_type, $type, $currency = null)
        {
            if (is_null($currency)) {
                $currency = get_woocommerce_currency();
            }

            $array = array();

            if ($data_type == 'request') {
                $array = array(
                    $this->get_option('hash_salt'),
                    $hash_data['order_id'],
                    $hash_data['total'],
                    $currency,
                    'S',
                );
            }

            if ($data_type == 'response') {
                $array = array(
                    $this->get_option('hash_salt'),
                    isset($hash_data['status']) ? $hash_data['status'] : '',
                    $hash_data['description'],
                    $hash_data['amount'],
                    $currency,
                    $hash_data['id'],
                );
            }

            WCPL_Logger::log("[calc hash]\n".json_encode($array).'\n'.$data_type);

            $hash = sha1(implode('|', $array));

            return $hash;
        }

        /**
         * Prepare and send data for Secure Form method
         *
         * @param $order_id
         */
        function send_payment_data($order_id)
        {
            global $wp;

            $order = new WC_Order($order_id);
            $url = "https://secure.paylane.com/order/cart.html";
            $type = get_post_meta($order_id, 'paylane-type', true);

            switch (get_locale()) {
                case "pl_PL":
                    $language = "pl";
                    break;
                case "de_DE":
                    $language = "de";
                    break;
                case "nl_NL":
                    $language = "nl";
                    break;
                case "es_ES":
                    $language = "es";
                    break;
                case "fr_FR":
                    $language = "fr";
                    break;
                default:
                    $language = "en";
            }

            $address = $order->get_address('billing');
            $customer_name = $address['first_name'] . ' ' . $address['last_name'];
            $customer_address = $address['address_1'] . ' ' . $address['address_2'];
            $hash_data = array('order_id' => $order_id, "total" => $order->get_total());

            $form = '
            <form action="' . $url . '" method="' . $this->get_option('paylane_redirect_version') . '" id="paylane_form" name="paylane_form">
                <input type="hidden" name="customer_name" value="' . $customer_name . '">
                <input type="hidden" name="customer_email" value="' . $address['email'] . '">
                <input type="hidden" name="customer_address" value="' . $customer_address . '">
                <input type="hidden" name="customer_zip" value="' . $address['postcode'] . '">
                <input type="hidden" name="customer_city" value="' . $address['city'] . '">
                <input type="hidden" name="amount" value="' . $order->get_total() . '">
                <input type="hidden" name="currency" value="' . get_woocommerce_currency() . '">
                <input type="hidden" name="merchant_id" value="' . $this->merchant_id . '">
                <input type="hidden" name="description" value="' . $order_id . '">
                <input type="hidden" name="transaction_description" value=' . __('Order no.: ', 'wc-gateway-paylane') . $order_id . '">
                <input type="hidden" name="transaction_type" value="S">
                <input type="hidden" name="back_url" value="' . $this->notify_link . '">
                <input type="hidden" name="language" value="' . $language . '">
                <input type="hidden" name="hash" value="' . $this->hash($hash_data, "request", $type) . '">
            </form>
            <script type="text/javascript">
                document.getElementById("paylane_form").submit();
            </script>';
            echo ($form); 
            die();
        }

        /**
         * Execute action hooked to 'directdebit_check'
         *
         * @param WC_Order $order
         */
        function check_direct_debit($order)
        {
            $type = get_post_meta($order->id, 'paylane-type', true);
            $id = get_post_meta($order->id, 'paylane-id-sale', true);

            if ($type == 'sepa') {
                require_once __DIR__ . '/includes/paylane-rest.php';

                $client = new PayLaneRestClient(
                    $this->get_option('login_PayLane'),
                    $this->get_option('password_PayLane')
                );

                $info = $client->getSaleInfo(array('id_sale' => $id));

                $order->add_order_note(__('Polskie ePłatności transaction status: ', 'wc-gateway-paylane') . $info['status']);
                WCPL_Logger::log("[check_direct_debit]\Polskie ePłatności transaction status: " . $info['status'] . "\nid_sale: " . $id);
            }
        }

        private function translate_error_description($message, $code = null)
        {
            if (is_null($code)) {
                return $message;
            }

            $msg = wcpl_get_errors_by_code($code, $message);

            if (is_null($msg)) {
                return $message;
            }

            return $msg;
        }

        private function getCorrectOrderStatus($state)
        {
            $order_status = 'pending';

            if (substr($state, 0, 3) == 'wc-') {
                $order_status = substr($state, 3);
            } else {
                switch ($state) {
                    case 0:
                        $order_status = 'pending';
                        break;
                    case 1:
                        $order_status = 'processing';
                        break;
                    case 2:
                        $order_status = 'on-hold';
                        break;

                    case 3:
                        $order_status = 'completed';
                        break;

                    case 4:
                        $order_status = 'cancelled';
                        break;

                    case 5:
                        $order_status = 'refunded';
                        break;
                }
            }

            return $order_status;
        }

        /**
         * Last function which finish orders and set proper status to them
         *
         * @param      $order_id
         * @param      $state
         * @param null $message
         */
        public function finish_order($order_id, $state, $message = null)
        {
            $order = new WC_Order($order_id);
            $paylane_code = get_post_meta($order_id, 'paylane-type', true);
            $paylane_methods = self::$paylane_methods;
            $payment_label = $paylane_methods[$paylane_code];

            if (empty($state)) {
                $state = $this->get_option('status_' . $paylane_code);
            }

            if ($state >= 6) {
                $order_status_message = sprintf(
                    __('Transaction failed with reason: %s.', 'wc-gateway-paylane'),
                    $message
                );

                $current_order_status = $order->get_status();
                if($current_order_status == 'wc-completed'){
                    WCPL_Logger::log("[finish_order]\nTransaction action AFTER COMPLETED, payment method: " . $payment_label . "\norder_id: " . $order_id . "\state: " . $state, 'error');
                    return false;
                }
                
                $order->update_status('failed', $order_status_message);
                WCPL_Logger::log("[finish_order]\nTransaction failed with reason: " . $message . "\norder_id: " . $order_id . "\nstate: " . $state, 'error');
                return false;
            }

            $current_order_status = $order->get_status();
            if($current_order_status == 'wc-completed'){
                WCPL_Logger::log("[finish_order]\nTransaction action AFTER COMPLETED, payment method: " . $payment_label . "\norder_id: " . $order_id . "\state: " . $state, 'error');
                $return_url = $order->get_checkout_order_received_url();
                wp_redirect($return_url);
                exit;
            }

            $order_status_message = sprintf(
                __('Transaction confirmed, payment method: %s. %s', 'wc-gateway-paylane'),
                $payment_label,
                $message
            );
            
            $order_status = $this->getCorrectOrderStatus($state);

            $order->update_status($order_status, $order_status_message);
            WCPL_Logger::log("[finish_order]\nTransaction confirmed, payment method: " . $payment_label . "\norder_id: " . $order_id . "\state: " . $state);
            $return_url = $order->get_checkout_order_received_url();
            wp_redirect($return_url);
            exit;
        }

        /**
         * Handle manual refund through Polskie ePłatności in Woocomerce
         *
         * @param        $order_id
         * @param null   $amount
         * @param string $reason
         * @return bool
         */
        public function process_refund($order_id, $amount = null, $reason = '')
        {
            $order = wc_get_order($order_id);

            $refund_params = array(
                'id_sale' => get_post_meta($order_id, 'paylane-id-sale', true),
                'amount' => $amount,
                'reason' => $reason,
            );

            include_once 'includes/paylane-rest.php';
            $type = get_post_meta($order_id, 'paylane-type', true);
            $client = new PayLaneRestClient(get_option('login_PayLane'), get_option('password_PayLane'));
            try {
                $status = $client->refund($refund_params);
            } catch (Exception $e) {
                WCPL_Logger::log("[process_refund]\nException in refund\n" . $e->getMessage() . "\norder_id: " . $order_id, 'error');
            }

            if ($client->isSuccess()) {
                $order->add_order_note('Refund completed. ID: ' . $status['id_refund']);
                WCPL_Logger::log("[process_refund]\nRefund completed. ID: " . $status['id_refund'] . "\norder_id: " . $order_id);
                return true;
            } else {
                $error_message = null;
                if (isset($status['error']['id_error'])) {
                    $error_message .= __("Error ID:", 'wc-gateway-paylane') . " {$status['error']['id_error']} <br>";
                }

                $errorNumber = null;
                if (isset($status['error']['error_number'])) {
                    $errorNumber = $status['error']['error_number'];
                    $error_message .= __("Error number:", 'wc-gateway-paylane') . " {$status['error']['error_number']} <br>";
                }

                if (isset($status['error']['error_description'])) {
                    $error_message .= __("Error description:", 'wc-gateway-paylane') . " {$this->translate_error_description($status['error']['error_description'], $errorNumber)}";
                }

                $order->add_order_note(__('Refund Failed:', 'wc-gateway-paylane') . ' ' . $error_message);
                WCPL_Logger::log("[process_refund]\nRefund Failed: " . $error_message . "\norder_id: " . $order_id, 'error');
                return false;
            }
        }

        function set_order_paylane_id($order_id, $id)
        {
            update_post_meta($order_id, 'paylane-id-sale', $id);
        }

        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields()
        {
            $this->form_fields = include __DIR__ . '/includes/paylane-settings.php';
            if(is_admin()){
                foreach($this->form_fields as $k=>$v){
                    if(in_array($k, array('title','description','secure_form_name','credit_card_name','transfer_name','sepa_name','sofort_name','paypal_name','ideal_name'))){
                        $val = $this->get_option($k);
                        $repl = preg_replace('/paylane/i', 'Polskie ePłatności', $val);
                        if($val != $repl){
                            $this->update_option($k, $repl);
                        }
                    }
                }
            }
        }

        /**
         * Adds Paylane payment gateway to the list of installed gateways
         *
         * @param $methods
         * @return array
         */
        public function add_paylane_gateway($methods)
        {
            $methods[] = 'WC_Gateway_Paylane';

            return $methods;
        }

        /**
         * @param $methods
         * @return array
         */
        public function enable_secure_form_integration($methods)
        {
            $methods[] = 'Paylane_Gateway_Secure';
            return $methods;
        }

        /**
         * @param $methods
         * @return array
         */
        public function enable_api_integration($methods)
        {
            $methods[] = 'Paylane_Gateway_CreditCard';
            $methods[] = 'Paylane_Gateway_BankTransfer';
            $methods[] = 'Paylane_Gateway_Sepa';
            $methods[] = 'Paylane_Gateway_Sofort';
            $methods[] = 'Paylane_Gateway_Paypal';
            $methods[] = 'Paylane_Gateway_Ideal';
            $methods[] = 'Paylane_Gateway_ApplePay';

            return $methods;
        }

        /**
         * @param $available_gateways
         * @return mixed
         */
        public function disable_paylane_main_gateway_and_sub($available_gateways)
        {
            global $woocommerce;

            if (isset($available_gateways['paylane'])) {
                unset($available_gateways['paylane']);
            }

            if ($woocommerce->cart === null) {
                return $available_gateways;
            }

            if (!class_exists('WC_Subscriptions_Product')) {
                return $available_gateways;
            }

            $unset_gateways = [
                'paylane_polish_bank_transfer',
                'paylane_ideal',
                'paylane_secure_form',
                'paylane_sofort',
            ];
            $cart_items = $woocommerce->cart->cart_contents;
            $isSub = false;

            foreach ($cart_items as $key => $values) {
                $isSub = WC_Subscriptions_Product::is_subscription($values['product_id']);
                if ($isSub) {
                    break;
                }
            }

            if ($isSub) {
                foreach ($unset_gateways as $k) {
                    unset($available_gateways[$k]);
                }
            }

            return $available_gateways;
        }

        /**
         * Add actions
         *
         * @return void
         */
        private function add_actions()
        {

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_api_wc_gateway_paylane', array($this, 'data_handler'));
            add_action('woocommerce_api_wc_gateway_paylane_3ds', array($this, 'response_check_3ds'));
            add_action('woocommerce_order_action_directdebit_check', array($this, 'check_direct_debit'));
            add_action('wp_enqueue_scripts', array($this, 'paylane_payment_style'), 20);
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        }

        /**
         * Add filters
         *
         * @return void
         */
        private function add_filters()
        {
            add_filter('woocommerce_payment_gateways', array($this, 'add_paylane_gateway'));
            add_filter('woocommerce_available_payment_gateways', array($this, 'disable_paylane_main_gateway_and_sub'));

        }

        function process_admin_options()
        {
            parent::process_admin_options();
            $this->init_apple_pay_admin_settings();

        }

        /**
         * Init Apple Pay Validation
         *
         * @return void
         */
        private function init_apple_pay_admin_settings()
        {

            if (empty($this->settings['apple_pay_cert'])) {
                return;
            }

            try {
                $path = untrailingslashit($_SERVER['DOCUMENT_ROOT']);
                $dir = '.well-known';
                $file = 'apple-developer-merchantid-domain-association.txt';
                $fullpath = $path . '/' . $dir . '/' . $file;

                if (!file_exists($fullpath)) {
                    if (!file_exists($path . '/' . $dir)) {
                        if (!@mkdir($path . '/' . $dir, 0755)) {
                            throw new Exception(__('Unable to create certificate folder. Please create "./well-known/apple-developer-merchantid-domain-association.txt" file into your main domain directory with certificate text.', 'wc-gateway-paylane'));
                        }
                    }

                    $this->store_apple_pay_cert($fullpath, $this->settings['apple_pay_cert']);
                } else {
                    $myfile = @fopen($fullpath, "r");
                    $content = @fread($myfile, filesize($fullpath));
                    @fclose($myfile);

                    if ($this->settings['apple_pay_cert'] != $content) {
                        $this->store_apple_pay_cert($fullpath, $this->settings['apple_pay_cert']);
                    }
                }

            } catch (Exception $e) {
                $this->update_option('apple_pay_cert', '');
                $this->settings['apple_pay_cert'] = '';
                $this->displayError($e);
                WCPL_Logger::log("[init_apple_pay_admin_settings]\nException:\n" . $e->getMessage(), 'error');
            }
        }

        private function store_apple_pay_cert($fullpath, $content)
        {
            if (file_exists($fullpath) && !is_writable($fullpath)) {
                throw new Exception(__('Unable to write certificate file. Please create "./well-known/apple-developer-merchantid-domain-association.txt" file into your main domain directory with certificate text.', 'wc-gateway-paylane'));
            } else {
                $myfile = @fopen($fullpath, "w");
                @fwrite($myfile, trim($content));
                @fclose($myfile);
            }
        }

        private function displayError($err)
        {
            add_action('admin_notices', function () use ($err) {
                ?>
				<div class="error notice">
					<p><?php echo $err->getMessage(); ?></p>
				</div>
				<?php
            });
        }

        /**
         * Load depedencies
         *
         * @return void
         */
        private function load_depedencies()
        {
            require_once __DIR__ . '/Gateway/Base.php';
        }

        /**
         * Init secure form
         *
         * @return void
         */
        private function init_secure_form()
        {
            require_once __DIR__ . '/Gateway/Secure.php';
            add_filter('woocommerce_payment_gateways', array($this, 'enable_secure_form_integration'));
        }

        /**
         * Init API
         *
         * @return void
         */
        private function init_api()
        {
            require_once __DIR__ . '/Gateway/CreditCard.php';
            require_once __DIR__ . '/Gateway/BankTransfer.php';
            require_once __DIR__ . '/Gateway/Ideal.php';
            require_once __DIR__ . '/Gateway/Paypal.php';
            require_once __DIR__ . '/Gateway/Sepa.php';
            require_once __DIR__ . '/Gateway/Sofort.php';
            require_once __DIR__ . '/Gateway/ApplePay.php';

            add_filter('woocommerce_payment_gateways', array($this, 'enable_api_integration'));
        }

        /**
         * @param $order_status_string
         * @return mixed|null
         */
        private function get_order_status_id($order_status_string)
        {
            return (isset($this->order_status_to_id[$order_status_string]))
            ? $this->order_status_to_id[$order_status_string]
            : null;
        }

        /**
         * @param $error_message
         */
        private function print_error_page($error_message)
        {
            global $woocommerce;

            wc_add_notice(__('Payment error', 'wc-gateway-paylane') . '<br>' . $error_message, 'error');
            wp_redirect(wc_get_checkout_url());
            exit;

        }

        /**
         * @param $data
         * @param $token
         * @param $communication_id
         */
        function handle_notification($data, $token, $communication_id)
        {
            WCPL_Logger::log("[handle_notification]\nResponse\nData: " . json_encode(WCPL_Logger::secure($data)), 'info');
            // check communication
            if (!empty($this->get_option('notification_login_PayLane')) && !empty($this->get_option('notification_password_PayLane'))) {
                $this->checkBasicAuth();
            }
            if (empty($communication_id)) {
                WCPL_Logger::log("[handle_notification]\nEmpty communication id", 'error');
                die('Empty communication id');
            }

            foreach ($data as $notification) {
                $_txt = json_decode(stripslashes($notification['text']),true);
                if(is_array($_txt)){
                    $order_id = $_txt['description'];
                }else{
                    $order_id = $notification['text'];
                }

                $order = new WC_Order($order_id);

                if(empty($notification['type']) || empty($notification['id_sale']) || empty($notification['date']) || empty($notification['amount']) || empty($notification['currency_code']) || empty($notification['text'])){
                    continue;
                }

                $this->parseNotification($notification, $order);
            }

            die($communication_id);
        }

        private function canUpdateStatus($currentNotifType, $newNotifType)
        {
            if ($currentNotifType == 'S' && $newNotifType == 'R') {
                return false;
            } elseif ($currentNotifType == 'R' && $newNotifType == 'S') {
                return true;
            } elseif (in_array($currentNotifType, ['S', 'R'])) {
                return false;
            }

            return true;
        }

        private function parseNotification($notification, $order)
        {
            $id_sale = $notification['id_sale'];

            $notificationType = get_post_meta($order->get_id(), 'paylane-notification-type', true);

            if ($notificationType === false || ($notificationType !== false && $this->canUpdateStatus($notificationType, $notification['type']))) {
                //first time or not final type

                if ($notification['type'] === 'S') {
                    $order->update_status($this->getCorrectOrderStatus($this->get_option('status_successful_order')), 'Polskie ePłatności: ' . __('Transaction complete', 'wc-gateway-paylane'));
                    WCPL_Logger::log("[handle_notification]\nTransaction complete (S)\nsale_id: " . $id_sale);

                }

                if ($notification['type'] === 'R') {
                    $order->update_status(WC_Gateway_Paylane::ORDER_STATUS_REFUNDED, 'Polskie ePłatności: ' . __('Refund complete', 'wc-gateway-paylane'));
                    WCPL_Logger::log("[handle_notification]\nRefund complete (R)\nsale_id: " . $id_sale);
                }

                if ($notification['type'] === 'RV') {
                    $order->update_status('on-hold', __('Reversal received', 'wc-gateway-paylane'));
                    WCPL_Logger::log("[handle_notification]\nReversal received (RV)\nsale_id: " . $id_sale);
                }

                if ($notification['type'] === 'RRO') {
                    $order->update_status('on-hold', __('Retrieval request / chargeback opened', 'wc-gateway-paylane'));
                    WCPL_Logger::log("[handle_notification]\nRetrieval request / chargeback opened (RRO)\nsale_id: " . $id_sale);
                }

                if ($notification['type'] === 'CAD') {
                    $order->update_status('on-hold', __('Retrieval request / chargeback opened', 'wc-gateway-paylane'));
                    WCPL_Logger::log("[handle_notification]\nRetrieval request / chargeback opened (CAD)\nsale_id: " . $id_sale);
                }

                update_post_meta($order->get_id(), 'paylane-notification-timestamp', time());
                update_post_meta($order->get_id(), 'paylane-notification-type', $notification['type']);
            }

        }

        /**
         * @return void
         */
        protected function checkBasicAuth()
        {
            $user = $this->get_option('notification_login_PayLane');
            $password = $this->get_option('notification_password_PayLane');

            if (
                !isset($_SERVER['PHP_AUTH_USER']) ||
                !isset($_SERVER['PHP_AUTH_PW']) ||
                $user != $_SERVER['PHP_AUTH_USER'] ||
                $password != $_SERVER['PHP_AUTH_PW']
            ) {
                // authentication failed
                header("WWW-Authenticate: Basic realm=\"Secure Area\"");
                header("HTTP/1.0 401 Unauthorized");
                exit();
            }
        }

        function enqueue_admin_scripts()
        {
            if ('woocommerce_page_wc-settings' != get_current_screen()->id) {
                return;
            }

            wp_enqueue_script('woocommerce_paylane_admin', plugins_url('assets/js/paylane-admin-script.js', __FILE__), array());
        }

    }

    WC_Gateway_Paylane::instance()->init();

}

add_action('plugins_loaded', 'wcpl_init_paylane');
