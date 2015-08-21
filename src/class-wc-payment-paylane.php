<?php
/**
 *
 * Plugin Name: Paylane payment module for Woocommerce
 * Plugin URI: http://www.paylane.com
 * Description: Paylane payment module for WooCommerce.
 * Author: Paylane
 * Author URI: http://www.paylane.com
 * Version: 1.0.0
 */
add_action( 'wp_enqueue_scripts', 'paylane_payment_style' );
function paylane_payment_style()
{
    wp_register_style( 'payment-field', plugins_url( 'assets/css/payment-field.css', __FILE__ ), array(), '20120208', 'all' );
    wp_enqueue_style( 'payment-field' );
}

add_action('plugins_loaded', 'init_paylane');
function init_paylane() {
        
    if (!class_exists('WC_Payment_Gateway')){
        add_action( 'admin_init', 'child_plugin_has_parent_plugin' );
        function child_plugin_has_parent_plugin() {
            if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                add_action( 'admin_notices', 'child_plugin_notice' );

                deactivate_plugins( plugin_basename( __FILE__ ) ); 

                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
            }
        }

        function child_plugin_notice(){
            echo '<div class="error"><p>', __('The PayLane payment module requires WooCommerce to run, you can download it', 'woocommerce'), ' <a target="blank" href="https://wordpress.org/plugins/woocommerce/">', __('here', 'woocommerce'), '</a></p></div>';
        }
        return;
    }
        
    
    

    class WC_Gateway_Paylane extends WC_Payment_Gateway {

        /**
         * Constructor for the gateway.
         *
         * @access public
         *
         * 
         * @global type $woocommerce
         */
        private static $paylane_methods = array(
        "secure_form" => "Secure Form",
        "credit_card" => "Credit Card",
        "transfer" => "Bank Transfer",
        "sepa" => "SEPA",
        "sofort" => "Sofort",
        "paypal" => "PayPal",
        "ideal" => "iDEAL",
        );
       
        
        
        public function __construct() {
          
 
            global $woocommerce;
            
            
            $this->id = __('paylane', 'woocommerce');
            $this->icon = apply_filters('woocommerce_paylane_icon', plugins_url('assets/paylane.png', __FILE__));
            $this->has_fields = true;
            $this->method_title = __('Paylane', 'woocommerce');
            $this->notify_link = str_replace('https:', 'http:', add_query_arg('wc-api', 'WC_Gateway_Paylane', home_url('/')));
            $this->supports           = array(
			'products',
			'refunds'
            );
            
            add_filter('woocommerce_payment_gateways', array($this, 'add_paylane_gateway'));

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->payment_method = $this->get_option('payment_method');
            $this->secure_form = $this->get_option('secure_form');
            $this->merchant_id = $this->get_option('merchant_id');
            $this->fraud_check = $this->get_option('fraud_check');
            $this->ds_check = $this->get_option('3ds_check');
            $this->enable_notification = $this->get_option('notifications_enabled');
            
            add_action('woocommerce_checkout_update_order_meta','set_order_paylane_type');
            add_action('woocommerce_checkout_update_order_meta','set_order_paylane_id');
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_checkout_process', 'check_paylane_fields');
            add_action('woocommerce_api_wc_gateway_paylane', array($this, 'data_handler'));
            add_action('woocommerce_review_order_after_submit', array($this, 'repopulate_buttons'));
            add_filter('payment_fields', array($this, 'payment_fields'));
            add_action( 'woocommerce_order_actions', array( $this, 'add_order_meta_box_actions' ) );
            add_action( 'woocommerce_order_action_directdebit_check', array( $this, 'check_direct_debit' ) );
         
        }
        //Add status checking possibility for SEPA DIRECT DEBIT at order page at admin panel
        function add_order_meta_box_actions( $actions ) {
        $actions['directdebit_check'] = __( 'Sprawdz status transakcji SEPA Direct Debit', $this->text_domain );
        return $actions;
        }
        // Execute action hooked to 'directdebit_check'
        function check_direct_debit( $order ) {
            $type = get_post_meta($order->id, 'paylane-type', true);
            $id = get_post_meta($order->id, 'paylane-id-sale', true);
            if($type == 'sepa'){
                include_once ('includes/paylane-rest.php');
                $client = new PayLaneRestClient($this->get_option('login_'.$type), $this->get_option('password_'.$type));
                $info = $client->getSaleInfo(array('id_sale' => $id));
                $order->add_order_note(__('PayLane transaction status: ', 'woocommerce') . $info['status']);  
            }
         
        }
        /**
         * Adds Paylane payment gateway to the list of installed gateways
         * @param $methods
         * @return array
         */
        function add_paylane_gateway($methods) {
            $methods[] = 'WC_Gateway_Paylane';
            return $methods;
        }

        /**
         * Generates admin options
         */
        public function admin_options() {
            ?>
            <h2><?php _e('Paylane', 'woocommerce'); ?></h2>
            <table class="form-table">
            <?php $this->generate_settings_html(); ?>
            </table> 


            <?php
        }
        
        /**
         * Initialise Gateway Settings Form Fields
         *
         * @access public
         * @return void
         */
        function init_form_fields() {
            $this->form_fields = include( 'includes/paylane-settings.php' );
            
        }
        //Repopulate form and selected PayLane method
        function repopulate_buttons() {
            ?><script type="text/javascript">
                                jQuery(document).ready(function ($) {
                                    jQuery(document.body).on('change', 'input[name="type"]', function () {
                                        jQuery('body').trigger('update_checkout');
                                        jQuery.ajax($fragment_refresh);
                                    });
                                });


                  function getStorage(key_prefix) {
                  if (window.localStorage) {
                      return {
                          set: function(data) {
                              localStorage.setItem(key_prefix, data);
                          },
                          get: function() {
                              return localStorage.getItem(key_prefix);
                          }
                      };
                  }
              }

              jQuery(function($) {
                  var storedData = getStorage("paylane_method"); 
                  var storedCode = getStorage("paylane_method_code"); 
                  var storedCodeLast = getStorage("paylane_method_codelast");
                  $("#payment_form_"+storedCodeLast.get("paylane_method_codelast")).hide();
                  $("#payment_form_"+storedCode.get("paylane_method_code")).show();
                  $("div.check input:radio").bind("change",function(){
                      $("#"+this.id+"txt").toggle($(this.id).is(":checked"));
                      if($("#"+this.id).is(":checked")) { storedData.set(this.id); storedCodeLast.set(storedCode.get("paylane_method_code")); storedCode.set(this.value);}

                  }).each(function() {
                      var val = storedData.get("paylane_method");
                      if (val == this.id) $("#"+this.id).attr("checked", "checked");
                  });
                  
              });

            </script><?php
        }
        //Get active payment methods selected in WooCommerce setting
        function getActiveMethods($methods){
            $active = array();
            foreach($methods as $method => $title){
                if($this->get_option($method."_enabled")=="yes")
                        array_push($active, array($method => $title));
            }
            return $active;
        }
        //Prepare forms for active methods
        function prepareFields($active){
            $i=1;
            $buttons = null;
            $forms = null;
            foreach($active as $one){
                $buttons .= '<input type="radio" id="radio'.$i.'" name="type" class="'.key($one).'" value="'.key($one).'">
                    <label for="radio'.$i.'">'.$one[key($one)].'</label>';
                $i+=1;
                $forms.= "<p>".$this->prepare_paylane_form(key($one))."</p>";
            }
            return array("buttons" => $buttons, 'forms' => $forms);
        }
        //Show PayLane methods fields at checkout
        function payment_fields() {
            echo "<p>{$this->description}</p>";
            echo "<p>Wybierz metodę płatności:</p> ";
            $methods = self::$paylane_methods;
            $active = $this->getActiveMethods($methods);           
            $fields = $this->prepareFields($active);
            echo '              
            <div class="paylane-methods check">'.$fields["buttons"].'</div>
            <div id="paylane-form" class="paylane-form">'.$fields["forms"].'</div> ';  
     
        }
        //Fields validation at checkout 
        function validate_fields(){
            $errors = array();
            if($_POST['type'] == 'credit_card'){

                $number = $_POST['card_number'];
                $number=preg_replace('/\D/', '', $number);


                $number_length=strlen($number);
                $parity=$number_length % 2;


                $total=0;
                for ($i=0; $i<$number_length; $i++) {
                  $digit=$number[$i];

                  if ($i % 2 == $parity) {
                    $digit*=2;

                    if ($digit > 9) {
                      $digit-=9;
                    }
                  }

                  $total+=$digit;
                }

                // If the total mod 10 equals 0, the number is valid
                ($total % 10 == 0) ? TRUE : FALSE;
                if(!$_POST['card_number']){
                    $errors[]=__('Card number is empty', 'woocommerce');
                }
                else if(($total % 10)!=0){
                    $errors[]=__('Card number is invalid', 'woocommerce');
                }
                if(!$_POST['security_code']){
                    $errors[]=__('Security code is empty', 'woocommerce');
                }
                if(!$_POST['name_on_card']){
                    $errors[]=__('Card holder name is empty', 'woocommerce');
                }
                  
            }
            if($_POST['type'] == 'sepa'){

                        if(!$_POST["sepa_account_holder"]){
                            $errors[]=__('Account holder name is empty', 'woocommerce');
                        }
                     
                        if(!$_POST["sepa_account_country"]){
                             $errors[]=__('Account country is empty', 'woocommerce');
                        }
                        if(!$_POST["sepa_iban"]){
                             $errors[]=__('IBAN is empty', 'woocommerce');
                        }
                    
                        if(!$_POST["sepa_bic"]){
                             $errors[]=__('BIC is empty', 'woocommerce');
                        }
                        if(!$_POST["sepa_mandate_id"]){
                             $errors[]=__('SEPA mandate is empty', 'woocommerce');
                        }
            }
            if(empty($errors))
                return true;
            else{
                foreach($errors as $error){
                    wc_add_notice($error, 'error');
                }
                return false;
            }
                
        }
        //Function which prepare data and parameters for gateway API and process it to communication function
        function process_payment($order_id) {
            if($this->validate_fields()){
            global $woocommerce;
            $order = new WC_Order($order_id);
            $order->update_status('on-hold', __('Awaiting payment confirmation', 'woocommerce'));
            update_post_meta($order_id, '_payment_method_title', "Paylane - ".self::$paylane_methods[$_POST['type']]);
            $order->reduce_order_stock();
            $woocommerce->cart->empty_cart();
                                    
            if($_POST['type']!="secure_form"){
                if($_POST['type']=='paypal'){
                    $data = array(
                    'sale'     => array(
                        'amount'      => $order->get_total(),
                        'currency'    => get_woocommerce_currency(),
                        'description' => $order_id
                    ),
                    'back_url'  => $this->notify_link,
                );}
                else{
                $customer_name = $order->billing_first_name . ' '. $order->billing_last_name;
                $customer_address = $order->billing_address_1 . ' ' . $order->billing_address_2;
                
                $data = array(
                    'sale'     => array(
                        'amount'      => $order->get_total(),
                        'currency'    => get_woocommerce_currency(),
                        'description' => $order_id
                    ),
                    'customer' => array(
                        'name'    => $customer_name,
                        'email'   => $order->billing_email,
                        'ip'      => WC_Geolocation::get_ip_address(),
                        'address' => array (
                            'street_house' => $customer_address,
                            'city'         => $order->billing_city,
                            'zip'          => $order->billing_postcode,
                            'country_code' => $order->billing_country,
                        ),
                    )
                );
                
            switch($_POST['type']){
                case "credit_card":
                    $data['card'] = array(
                    "card_number"       =>   $_POST['card_number'],
                    "expiration_month"  =>   $_POST['expiration_month'],
                    "expiration_year"   =>   $_POST['expiration_year'],
                    "name_on_card"      =>   $_POST['name_on_card'],
                    "card_code"         =>   $_POST['security_code'],
                    );
                    break;
                case "sepa":
                    $data['account'] = array(
                    'account_holder'  => $_POST['sepa_account_holder'],
                    'account_country' => $_POST['sepa_account_country'],
                    'iban'            => $_POST['sepa_iban'],
                    'bic'             => $_POST['sepa_bic'],
                    'mandate_id'      => $_POST['sepa_mandate_id'],
                    );
                    break;
                case "ideal":
                    $data['back_url'] =  $this->notify_link;
                    $data['bank_code'] = $_POST['bank-code'];
                    break;
                case "transfer":
                    $data['payment_type'] = $_POST['transfer_bank'];
                    $data['back_url'] = $this->notify_link;
                    break;
                case "sofort":
                    $data['back_url'] =  $this->notify_link;
                    break;
            }}
            session_start();
            $_SESSION['paylane-data'] = $data;
            $_SESSION['paylane-type'] = $_POST['type'];
            }
            $this->set_order_paylane_type($order_id, $_POST['type']);
            return array(
                'result' => 'success',
                'redirect' => add_query_arg(array('order_id' => $order_id, 'type' => $_POST['type']), $this->notify_link)
            );
            }
        }
        function handle_notification($data, $token, $communication_id){
            // check communication
            if($this->get_option('notifications_http_auth') == 'yes'){
                $user = $this->get_option('notifications_login');
                $password = $this->get_option('notifications_password');
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])
                || $user != $_SERVER['PHP_AUTH_USER'] || $password != $_SERVER['PHP_AUTH_PW']) {

                // authentication failed
                header("WWW-Authenticate: Basic realm=\"Secure Area\"");
                header("HTTP/1.0 401 Unauthorized");
                exit();
            }
            }
            if (empty($_POST['communication_id'])) {
                die('Empty communication id');
            }

            // check if token correct
            if ((!empty($this->get_option('notifications_token')))&&($this->get_option('notifications_token') !== $_POST['token'])) 
                die('Wrong token');

            foreach ($data as $notification) {
                $id_sale = $notification['id_sale'];
                $order_id = $notification['text'];
                $order = new WC_Order($order_id);
                if ($notification['type'] === 'S') { 
                    $order->add_order_note(__('Transaction complete', 'woocommerce'));
                }
                if ($notification['type'] === 'R') { 
                    $order->add_order_note(__('Refund complete', 'woocommerce'));
                }
                if ($notification['type'] === 'RV') { 
                    $order->update_status('on-hold', __('Reversal received', 'woocommerce'));
                }
                if ($notification['type'] === 'RRO') { 
                    $order->update_status('on-hold',__('Retrieval request / chargeback opened', 'woocommerce'));
                }
                if ($notification['type'] === 'CAD') { 
                    $order->update_status('on-hold',__('Retrieval request / chargeback opened', 'woocommerce'));
                }
            }
            die($_POST['communication_id']);
        }
        //Main function which sends data to PayLane service and get response
        function data_handler() {
      
            if(isset($_POST['content'])&&($this->enable_notification == 'yes')){
                if (empty($_POST['communication_id'])) {
                die('Empty communication id');
                }
                if (!empty(($this->get_option('notifications_token')))&&($this->get_option('notifications_token') !== $_POST['token'])) {
                die('Wrong token');
                }     
                $this->handle_notification($_POST['content'], $_POST['token'], $_POST['communication_id']);
                unset($_POST['content']);
            }
                                              
            $type = null;
            if(isset($_GET['type']))
            $type = $_GET['type'];
            
            if(!$type){
                $this->response_check();
            }
            else{
            if($type == "secure_form"){
                    $this->send_payment_data($_GET['order_id']);
                    unset($_GET['order_id']);
            }
            else{
                include_once ('includes/paylane-rest.php');
                $client = new PayLaneRestClient($this->get_option('login_'.$type), $this->get_option('password_'.$type));
                session_start();
                $params = $_SESSION['paylane-data'];
                try {
                    switch($type){
                        case "credit_card":
                            $status = $client->cardSale($params);
                            break;
                        case "sepa":
                         $status = $client->directDebitSale($params);
                            break;
                        case "ideal":
                            $status = $client->idealSale($params);
                            break;
                        case "transfer":
                            $status = $client->bankTransferSale($params);
                            break;
                        case "sofort":
                            $status = $client->sofortSale($params);
                            break;
                        case "paypal":
                        $status = $client->paypalSale($params);
                            break;
                    }
                } catch (Exception $e) {

                }  
                // checking transaction status example (optional):
                if ($client->isSuccess()) {
                    switch($type){
                        case "credit_card":
                            echo "Success, id_sale: {$status['id_sale']} \n";
                            $this->set_order_paylane_id($_GET['order_id'], $status['id_sale']);
                            $this->finish_order($_GET['order_id'], 'success');                            
                            break;
                        case "sepa":
                            echo "Success, id_sale: {$status['id_sale']} \n";
                            $this->set_order_paylane_id($_GET['order_id'], $status['id_sale']);
                            $this->finish_order($_GET['order_id'], 'success'); 
                            break;
                        case "ideal":
                            $status = $client->idealSale($params);
                            header('Location: ' . $status['redirect_url']);
                            break;
                        case "transfer":
                            $status = $client->bankTransferSale($params);
                            header('Location: ' . $status['redirect_url']);
                            break;
                        case "sofort":
                            $status = $client->sofortSale($params);                            
                            header('Location: ' . $status['redirect_url']);
                            break;
                        case "paypal":
                        $status = $client->paypalSale($params);         
                        header('Location: ' . $status['redirect_url']);
                            break;
                    }
                } else {
                    $error_message = null;
                    if(isset($status['error']['id_error']))$error_message .= "Error ID: {$status['error']['id_error']}, \n";
                    if(isset($status['error']['error_number']))$error_message .= "Error number: {$status['error']['error_number']}, \n";
                    if(isset($status['error']['error_description']))$error_message .= "Error description: {$status['error']['error_description']}";
                    $this->finish_order($_GET['order_id'], 2, $error_message);
                    
                     echo '<div style="margin: 0 auto 0 auto; width: 50%; heigth: 30%; margin-top: 10%;"><img src="'.$this->icon.'"></img>'
                     .'<div style="font-family: sans-serif; padding: 20px; background-color: #D6D6C2; border: display; border-style: solid; border-radius: 10px;">'
                    . __('There was an error when processing your payment.', 'woocommerce') . '<br><br>'
                    . __('Your order could not be completed, please try again or contact the store.', 'woocommerce')
                    . '</div>'
                    . '<div><a href="'.home_url('/').'"><button style="background-color: #6E6E6E; color: #FFF;">' . __('Return to the store', 'woocommerce') . '</button></a></div>';
                }

            }
            }
            exit;
        }
                //Calculating hash
        protected function hash($hash_data, $data_type, $type){
            if($data_type == "request")
            $hash = SHA1($this->get_option('hash_salt_'.$type)."|".$hash_data["order_id"]."|".$hash_data["total"]."|".get_woocommerce_currency()."|".'S');
            if($data_type == "response")
            $hash = SHA1($this->get_option('hash_salt_'.$type)."|".$hash_data["status"]."|".$hash_data["description"]."|".$hash_data["amount"]."|".get_woocommerce_currency()."|".$hash_data["id"]);
            return $hash;
        }
        //Functions which prepare and send data for Secure Form method
        function send_payment_data($order_id) {
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
            $customer_name = $order->billing_first_name . ' '. $order->billing_last_name;
            $customer_address = $order->billing_address_1 . ' ' . $order->billing_address_2;
            $hash_data = array("order_id" => $order_id, "total" => $order->get_total());
            $form = '
            <form action="'.$url.'" method="post" id="paylane_form" name="paylane_form">
                <input type="hidden" name="customer_name" value="'.$customer_name.'">
                <input type="hidden" name="customer_email" value="'.$order->billing_email.'">
                <input type="hidden" name="customer_address" value="'.$customer_address.'">
                <input type="hidden" name="customer_zip" value="'.$order->billing_postcode.'">	
                <input type="hidden" name="customer_city" value="'.$order->billing_city.'">
                <input type="hidden" name="amount" value="'.$order->get_total().'">
                <input type="hidden" name="currency" value="'.get_woocommerce_currency().'">
                <input type="hidden" name="merchant_id" value="'.$this->merchant_id.'">
                <input type="hidden" name="description" value="'.$order_id.'">
                <input type="hidden" name="transaction_description" value="Zamówienie nr: '.$order_id.'">
                <input type="hidden" name="transaction_type" value="S">
                <input type="hidden" name="back_url" value="'.$this->notify_link.'">
                <input type="hidden" name="language" value="'.$language.'">
                <input type="hidden" name="hash" value="'.$this->hash($hash_data, "request", $type).'">
            </form>
            <script type="text/javascript">
                document.getElementById("paylane_form").submit();
            </script>';
            echo $form;
            die();
        }
        //Function which check response from PayLane service and proceed it to finish orderd
        function response_check(){
            if(isset($_POST['description']))
                $order_id = $_POST['description'];
            else
                $order_id = $_GET['description'];
            $type = get_post_meta($order_id, 'paylane-type', true);
            $redirect_version = $this->get_option('redirect_version_'.$type);
            if($redirect_version == "POST"){
                $response['status'] = $_POST['status'];
                $response['description'] = $_POST['description'];
                $response['amount'] = $_POST['amount'];
                $response['currency'] = $_POST['currency'];
                $response['hash'] = $_POST['hash'];
                if(isset($_POST['id_error'])){
                    $response['id_error'] = $_POST['id_error'];
                    $error_message = "Error: ".$_POST['id_error'];
                    if(isset($_POST['error_code'])){
                    $error_message .= " - ".$_POST['error_code'];
                    }
                    if(isset($_POST['error_text'])){
                    $error_message .= " - ".$_POST['error_text'];
                    }
                }
                else{
                    $response['id_sale'] = $_POST['id_sale'];
                    $this->set_order_paylane_id($response['description'], $response['id_sale']);
                }
            }
            else{
                $response['status'] = $_GET['status'];
                $response['description'] = $_GET['description'];
                $response['amount'] = $_GET['amount'];
                $response['currency'] = $_GET['currency'];
                $response['hash'] = $_GET['hash'];
                if(isset($_GET['id_error'])){
                    $response['id_error'] = $_GET['id_error'];
                    $error_message = "Error: ".$_GET['id_error'];
                    if(isset($_GET['error_code'])){
                    $error_message .= " - ".$_GET['error_code'];
                    }
                    if(isset($_GET['error_text'])){
                    $error_message .= " - ".$_GET['error_text'];
                    }
                }
                else{
                    $response['id_sale'] = $_GET['id_sale'];
                    $this->set_order_paylane_id($response['description'], $response['id_sale']);
                }
            }

            if(!isset($error_message)){
                
                $hash_data = array("status" => $response['status'], "description" => $response['description'], "amount" => $response['amount'], "id" => $response['id_sale']);
                $hash = $this->hash($hash_data, "response", $type);

                if ($hash == $response['hash']) {
                    if ($response['status'] != 'ERROR') {
                        if($response['status'] == 'PENDING')
                        $this->finish_order($response['description'], 0, __("Payment awaiting confirmation", "woocommerce"));
                        else{
                        session_start();
                        $this->finish_order($response['description'], $this->get_option('status_'.$_SESSION['paylane-type']));                        
                        }
                    }
                }
                else{
                     echo '<div style="margin: 0 auto 0 auto; width: 50%; heigth: 30%; margin-top: 10%;"><img src="'.$this->icon.'"></img>'
                     .'<div style="font-family: sans-serif; padding: 20px; background-color: #D6D6C2; border: display; border-style: solid; border-radius: 10px;">'
                    . __('There was an error when processing your payment.', 'woocommerce') . '<br><br>'
                    . __('Your order could not be completed, please try again or contact the store.', 'woocommerce')
                    . '</div>'
                    . '<div><a href="'.home_url('/').'"><button style="background-color: #6E6E6E; color: #FFF;">' . __('Return to the store', 'woocommerce') . '</button></a></div>';
                }
            }
            else {
                $this->finish_order($response['description'], 2, $error_message);
                 echo '<div style="margin: 0 auto 0 auto; width: 50%; heigth: 30%; margin-top: 10%;"><img src="'.$this->icon.'"></img>'
                 .'<div style="font-family: sans-serif; padding: 20px; background-color: #D6D6C2; border: display; border-style: solid; border-radius: 10px;">'
                . __('There was an error when processing your payment.', 'woocommerce') . '<br><br>'
                . __('Your order could not be completed, please try again or contact the store.', 'woocommerce')
                . '</div>'
                . '<div><a href="'.home_url('/').'"><button style="background-color: #6E6E6E; color: #FFF;">' . __('Return to the store', 'woocommerce') . '</button></a></div>';
            }
        }
        //Last function which finish orders and set proper status to them
        function finish_order($order_id, $state, $message = null) {
            $order = new WC_Order($order_id);
            $paylane_code = get_post_meta($order_id, 'paylane-type', true);
            $paylane_methods = self::$paylane_methods;
            $payment_label = $paylane_methods[$paylane_code];
            if ($state == 0) {
                    $order_status='processing';
                    $order->update_status($order_status, __('Transaction confirmed, payment method: '.$payment_label.". ".$message));
                    $return_url = $order->get_checkout_order_received_url();
                    wp_redirect($return_url);
                }
                else if($state == 1){
                    $order_status='completed';
                    $order->update_status($order_status, __('Transaction confirmed, payment method: '.$payment_label.". ".$message));
                    $return_url = $order->get_checkout_order_received_url();
                    wp_redirect($return_url);
                }                
                else{
                    $order->update_status('failed', __('Transaction failed with reason: '.$message));
                }
            
        }
        //Function to handle manual refund through PayLane in Woocomerce
        public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );

                
                $refund_params = array(
                    'id_sale' => get_post_meta($order_id, 'paylane-id-sale', true),
                    'amount'  => $amount,
                    'reason'  => $reason,
                );
                include_once ('includes/paylane-rest.php');
                $type = get_post_meta($order_id, 'paylane-type', true);
                $client = new PayLaneRestClient(get_option('login_'.$type), get_option('password_'.$type));  
                try {
                    $status = $client->refund($refund_params);
                } catch (Exception $e) {

                }

                if ($client->isSuccess()) {
                    $order->add_order_note('Refund completed. ID: '.$status['id_refund']);
                    return true;
                } else {
                    $error_message = null;
                    if(isset($status['error']['id_error']))$error_message .= "Error ID: {$status['error']['id_error']}, \n";
                    if(isset($status['error']['error_number']))$error_message .= "Error number: {$status['error']['error_number']}, \n";
                    if(isset($status['error']['error_description']))$error_message .= "Error description: {$status['error']['error_description']}";
                    $order->add_order_note('Refund Failed: ' . $error_message );
                    return false; 
                }
				
	}
        //Prepare form to show in checkout depending on selected methods
        public function prepare_paylane_form($method){
            switch($method){
                case "secure_form":
                    $form = '<div id="payment_form_secure_form" style="display:none;">' . __('You will be redirected to the PayLane Secure Form website for payment.', 'woocommerce') . '</div>';
                        break;
                case "credit_card":
                    $date = date("Y");
                    $option_years = null;
                    for($i = 0; $i<10; $i++)
                    {
                    $option_years .= '<option value="'.$date.'">'.$date.'</option>';
                    $date++;
                    }
                    $form = '
                        <ul class="form-list paylane-list" id="payment_form_credit_card" style="display:none; list-style-type: none;">
                        <li>
                            <label for="cc_card_number" class="required"><em>*</em>' . __('Credit card number', 'woocommerce') . '</label>
                            <span class="input-box">
                                <input type="text" class="input-text required-entry" id="cc_card_number" name="card_number"/>
                            </span>
                        </li>
                        <li>
                            <label for="cc_security_code" class="required"><em>*</em>' . __('Security code (CVV/CVC)', 'woocommerce') . '</label>
                            <span class="input-box">
                                <input type="text" class="input-text required-entry" id="cc_security_code" name="security_code"/>
                            </span>
                        </li>

                        <li>
                            <label for="cc_credit_card_expiration" class="required"><em>*</em>' . __('Card expiration date', 'woocommerce') . '</label>
                                    <div class="input-box">
                                    <div class="v-fix">
                                    <label for="expiration_month" class="required"><em>*</em>' . __('Month', 'woocommerce') . '</label>
                                    <select id="expiration_month" name="expiration_month" class="month required-entry">
                                            <option value=""></option>
                                            <option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
                                </div>
                                <div class="v-fix">
                                    <label for="expiration_year" class="required"><em>*</em>' . __('Year', 'woocommerce') . '</label>
                                    <select id="expiration_year" name="expiration_year" class="year required-entry">
                                            <option value=""></option>
                                            '.$option_years.'
                                    </select>
                                </div>
                            </div>
                        </li>

                        <li>
                            <label for="cc_name_on_card" class="required"><em>*</em>' . __('Card holder name', 'woocommerce') . '</label>
                            <span class="input-box">
                                <input type="text" lass="input-text required-entry" id="cc_name_on_card" name="name_on_card"/>
                            </span>
                        </li>
                    </ul>';
                    break;  
                case "transfer":
                    $form =  '<div id="payment_form_transfer" style="display:none;">
                                ' . __('You will be redirected to your bank\'s website payment.', 'woocommerce') . '
                                <select name="transfer_bank">                                      
                                    <option value="AB">Alior Bank</option>
                                    <option value="AS">T-Mobile Usługi Bankowe</option>
                                    <option value="MU">Multibank</option>
                                    <option value="MT">mTransfer</option>
                                    <option value="IN">Inteligo</option>
                                    <option value="IP">iPKO</option>
                                    <option value="DB">Deutsche Bank</option>
                                    <option value="MI">Millennium</option>
                                    <option value="CA">Credit Agricole</option>
                                    <option value="PP">Poczta Polska (postal money order)</option>
                                    <option value="BP">Bank BPH</option>
                                    <option value="IB">Idea Bank</option>
                                    <option value="PO">Pekao S.A.</option>
                                    <option value="GB">Getin Bank</option>
                                    <option value="IG">ING Bank Śląski</option>
                                    <option value="WB">Bank Zachodni WBK</option>
                                    <option value="OH">Other</option>
                                </select>
                            </div>';
                    break; 
                case "sepa":
                    $form = '<div id="payment_form_sepa" style="display:none;">
                        <label for="sepa_account_holder" class="required"><em>*</em>SEPA Account Holder</label>
                        <input type="text" title="Name on card:" class="input-text required-entry" name="sepa_account_holder"/>
                        <label for="sepa_account_country" class="required"><em>*</em>SEPA Account Country</label>
                        <input type="text" title="Name on card:" class="input-text required-entry" name="sepa_account_country"/>
                        <label for="sepa_iban" class="required"><em>*</em>SEPA IBAN</label>
                        <input type="text" title="Name on card:" class="input-text required-entry" name="sepa_iban"/>
                        <label for="sepa_bic" class="required"><em>*</em>SEPA BIC</label>
                        <input type="text" title="Name on card:" class="input-text required-entry" name="sepa_bic"/>
                        <label for="sepa_mandate_id" class="required"><em>*</em>SEPA Mandate ID</label>
                        <input type="text" title="Name on card:" class="input-text required-entry" name="sepa_mandate_id"/>
                        </div>';
                    break; 
                case "sofort":
                    $form =  '<div id="payment_form_sofort" style="display:none;">' . __('You will be redirected to the Sofort website for payment.', 'woocommerce') . '
                            </div>';
                    break; 
                case "paypal":
                    $form =  '<div id="payment_form_paypal" style="display:none;">' . __('You will be redirected to the PayPal website for payment.', 'woocommerce') . '</div>';
                    break; 
                case "ideal":
                    $form =  '<div id="payment_form_ideal" style="display:none;"><p>' . __('You will be redirected to the iDeal website for payment.', 'woocommerce') . '.</p>'.$this->getIdealBanks().'</div>';
                    break; 
            }
            return $form;
        }
        //Get iDEAL bank codes
        public function getIdealBanks(){
            include_once ('includes/paylane-rest.php');
            $client = new PayLaneRestClient($this->get_option('login_ideal'), $this->get_option('password_ideal'));  
            $codes = null;
            try {
            $status = $client->idealBankCodes();
            }
            catch (Exception $e) {
              // handle exception
            }

            if (!$client->isSuccess()) {
              $error_number = $status['error']['error_number'];
              $error_description = $status['error']['error_description'];
              // handle error
            }
            else{
                $codes .= '<select name="bank-code">';
                  foreach ($status['data'] as $bank) {
                    $codes .= '<option value="'.$bank['bank_code'].'">'.$bank['bank_name'].'</option>';
                  }                
                $codes .= '</select>';
                return $codes;
            }
        }
        //set meta data required to process orders in gateway
        function set_order_paylane_type($order_id, $type) {

             update_post_meta($order_id, 'paylane-type', $type);
             
         }
         function set_order_paylane_id($order_id, $id) {

             update_post_meta($order_id, 'paylane-id-sale', $id);
             
         }    

    }

    new WC_Gateway_Paylane();
}
