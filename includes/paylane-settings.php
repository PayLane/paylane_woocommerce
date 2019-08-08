<?php
/**
 * Settings for PayPal Gateway
 *
 * @author Endora <biuro@endora.pl> https://endora.pl
 */
if (!defined('ABSPATH')) {
    exit;
}

global $woocommerce;

$order_status = array();
$statuses = wc_get_order_statuses();

$i = 0;

foreach ($statuses as $status => $status_name) {
    $order_status[$status] = __(esc_html($status_name), 'wc-gateway-paylane');
    $i++;
}

$options = array(
    'version' => array(
        'type' => 'title',
        'description' => __('Version', 'wc-gateway-paylane') . ' 2.1.3',
    ),
    'title' => array(
        'title' => __('Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'default' => __('Paylane', 'wc-gateway-paylane'),
        'desc_tip' => true,
    ),
    'description' => array(
        'title' => __('Description', 'wc-gateway-paylane'),
        'type' => 'textarea',
        'description' => __('Description.', 'wc-gateway-paylane'),
        'default' => __('Pay with PayLane.', 'wc-gateway-paylane'),
    ),
    'connection_mode' => array(
        'title' => __('PayLane connection method', 'wc-gateway-paylane'),
        'type' => 'select',
        'description' => __('Set the PayLane connection method SecureForm/API.', 'wc-gateway-paylane'),
        'default' => 'API',
        'class' => 'wc-enhanced-select',
        'options' => array(
            'API' => __('API', 'wc-gateway-paylane'),
            'SecureForm' => __('SecureForm', 'wc-gateway-paylane'),
        ),
    ),

    'login_PayLane' => array(
        'title' => __('API Login', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => __('The API Login can be found in the merchant panel [<a href="https://merchant.paylane.com" rel="noopener" target="_blank">https://merchant.paylane.com</a>] in Administration -> Integration. <strong>API Login is not a login for the merchant panel!</strong>', 'wc-gateway-paylane'),
        'default' => '',
        'desc_tip' => true,
    ),
    'password_PayLane' => array(
        'title' => __('API Password', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => __('The API Password can be found in the merchant panel [<a href="https://merchant.paylane.com" rel="noopener" target="_blank">https://merchant.paylane.com</a>] in Administration -> Integration. <strong>API Password is not a password for the merchant panel!</strong>', 'wc-gateway-paylane'),
        'default' => '',
        'desc_tip' => true,
    ),
    'api_key_val' => array(
        'title' => __('Public API key', 'wc-gateway-paylane'),
        'type' => 'text',
        'class' => 'api-field',
    ),
    'hash_salt' => array(
        'title' => __('Hash Salt', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => __('Secret hash salt value', 'wc-gateway-paylane'),
        'desc_tip' => true,
    ),
    'status_successful_order' => array(
        'title' => __('Successful order status', 'wc-gateway-paylane'),
        'type' => 'select',
        'default' => 'wc-pending',
        'options' => $statuses,
    ),
    'paylane_redirect_version' => array(
        'title' => __('Redirect method', 'wc-gateway-paylane'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('PayLane response redirect method', 'wc-gateway-paylane'),
        'default' => 'POST',
        'desc_tip' => true,
        'options' => array(
            'GET' => __('GET', 'wc-gateway-paylane'),
            'POST' => __('POST', 'wc-gateway-paylane'),
        ),
    ),

    'design' => array(
        'class' => 'admin-appearance-paylane',
        'title' => __('Appearance', 'wc-gateway-paylane'),
        'description' => __(
            'Select <strong>basic</strong> if you want to give your own css styles for the form.',
            'wc-gateway-paylane'
        ),
        'type' => 'select',
        'default' => 'paylane',
        'options' => array(
            'basic' => __('Basic', 'wc-gateway-paylane'),
            'paylane' => __('PayLane', 'wc-gateway-paylane'),
        ),
    ),
    'display_payment_methods_logo' => array(
        'title' => __('Display payment methods logo', 'wc-gateway-paylane'),
        'type' => 'checkbox',
        'default' => 'yes',
    ),
    'notificaion' => array(
        'title' => __('Notification adress', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => sprintf(
            __(
                'Notifications is a service that simplifies automatic communication between your shop and PayLane. A notification has information about payment status.<br><br>
							<big><strong>It is Highly recommended to enable notifications</strong></big>.<br>
							<br>
<strong>To enable notifications</strong>:<br>
- Chose individual login and password and enter them below (it should be safe login and password, <strong>not the same as API login/password or merchant panel login/password</strong>)<br>
<br>
- Send to us on e-mail (support@paylane.com) you notification login, password and notification address, <br>
(Your notification address: <code>%s</code>)<br>
<br>
We will send you Notification token to fill inside this field', 'wc-gateway-paylane'
            ), add_query_arg('wc-api', 'WC_Gateway_Paylane', home_url('/'))
        ),
    ),
    'notification_login_PayLane' => array(
        'title' => __('Notification Login', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => __('Notification Login', 'wc-gateway-paylane'),
        'default' => '',
        'desc_tip' => true,
    ),
    'notification_password_PayLane' => array(
        'title' => __('Notification Password', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => __('Notification Password', 'wc-gateway-paylane'),
        'default' => '',
        'desc_tip' => true,
    ),
    'notification_token_PayLane' => array(
        'title' => __('Notification Token', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => __('Notification Token', 'wc-gateway-paylane'),
        'default' => '',
        'desc_tip' => true,
    ),
    'secure_form' => array(
        'title' => __('Secure Form', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => __("Customers will be redirected to the PayLane Secure Form for payment", "wc-gateway-paylane"),
    ),
    'secure_form_name' => array(
        'title' => __('Custom Payment Method Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => '',
        'default' => '',
    ),
    'merchant_id' => array(
        'title' => __('Merchant ID', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => __('You can find your Merchant ID in the Merchant Panel (Settings => Secure Form => Integration)', 'wc-gateway-paylane'),
        'default' => '',
        'desc_tip' => true,
    ),
    'credit_card' => array(
        'title' => __('Credit Card', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => '',
    ),
    'credit_card_name' => array(
        'title' => __('Custom Payment Method Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => '',
        'default' => '',
    ),
    '3ds_check' => array(
        'title' => __('3ds Check', 'wc-gateway-paylane'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'default' => 'false',
        'desc_tip' => false,
        'options' => array(
            'true' => __('YES', 'wc-gateway-paylane'),
            'false' => __('NO', 'wc-gateway-paylane'),
        ),
    ),
    'transfer' => array(
        'title' => __('Bank Wire Transfer', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => '',
    ),
    'transfer_name' => array(
        'title' => __('Custom Payment Method Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => '',
        'default' => '',
    ),
    'sepa' => array(
        'title' => __('Direct Debit (SEPA)', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => '',
    ),
    'sepa_name' => array(
        'title' => __('Custom Payment Method Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => '',
        'default' => '',
    ),
    'sofort' => array(
        'title' => __('SOFORT', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => '',
    ),
    'sofort_name' => array(
        'title' => __('Custom Payment Method Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => '',
        'default' => '',
    ),
    'paypal' => array(
        'title' => __('PAYPAL', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => '',
    ),
    'paypal_name' => array(
        'title' => __('Custom Payment Method Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => '',
        'default' => '',
    ),
    'ideal' => array(
        'title' => __('iDEAL', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => '',
    ),
    'ideal_name' => array(
        'title' => __('Custom Payment Method Name', 'wc-gateway-paylane'),
        'type' => 'text',
        'description' => '',
        'default' => '',
    ),
    'applepay' => array(
        'title' => __('Apple Pay', 'wc-gateway-paylane'),
        'type' => 'title',
        'description' => __(
            '<strong>What to do to enable Apple Pay:</strong><br>- Send an email to support@paylane.com asking you to enable Apple Pay on your account<br>- If you use the payment method through the API, please request a <b>certificate</b><br>- Paste the <b>certificate</b> into the certificate field<br>- Tell us when you will finish above activities',
            'wc-gateway-paylane'),
    ),
    'apple_pay_style' => array(
        'title' => __('Button Style', 'wc-gateway-paylane'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'default' => 'black',
        'desc_tip' => false,
        'options' => array(
            'black' => __('Black', 'wc-gateway-paylane'),
            'white' => __('White', 'wc-gateway-paylane'),
            'white-outline' => __('White with outline border', 'wc-gateway-paylane'),
        ),
    ),
    'apple_pay_language' => array(
        'title' => __('Language', 'wc-gateway-paylane'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'default' => 'auto',
        'desc_tip' => false,
        'options' => array(
            'auto' => __('Auto', 'wc-gateway-paylane'),
            'pl' => __('PL', 'wc-gateway-paylane'),
            'en' => __('EN', 'wc-gateway-paylane'),
            'de' => __('DE', 'wc-gateway-paylane'),
            'fr' => __('FR', 'wc-gateway-paylane'),
        ),
    ),
    'apple_pay_cert' => array(
        'title' => __('Certificate', 'wc-gateway-paylane'),
        'type' => 'textarea',
        'description' => '',
    ),

);

if (version_compare($woocommerce->version, '3.2.0','<')) {
    //legacy
    $options['legacy'] = array(
        'title' => __('Enable/Disable payment methods', 'wc-gateway-paylane'),
        'type' => 'title',
    );
    $options['secure_form_legacy_enabled'] = array(
        'title' => 'SecureForm',
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), 'SecureForm'),
        'default' => 'no',
    );
    $options['apple_pay_legacy_enabled'] = array(
        'title' => 'ApplePay',
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), 'ApplePay'),
        'default' => 'no',
    );
    $options['transfer_legacy_enabled'] = array(
        'title' => __('Bank Wire Transfer', 'wc-gateway-paylane'),
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), __('Bank Wire Transfer', 'wc-gateway-paylane')),
        'default' => 'no',
    );
    $options['credit_card_legacy_enabled'] = array(
        'title' => __('Credit Card', 'wc-gateway-paylane'),
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), __('Credit Card', 'wc-gateway-paylane')),
        'default' => 'no',
    );
    $options['ideal_legacy_enabled'] = array(
        'title' => __( 'iDEAL', 'wc-gateway-paylane' ),
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), __( 'iDEAL', 'wc-gateway-paylane' )),
        'default' => 'no',
    );
    $options['paypal_legacy_enabled'] = array(
        'title' => __('PayPal', 'wc-gateway-paylane'),
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), __('PayPal', 'wc-gateway-paylane')),
        'default' => 'no',
    );
    $options['sepa_legacy_enabled'] = array(
        'title' => __('SEPA Direct Debit', 'wc-gateway-paylane'),
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), __('SEPA Direct Debit', 'wc-gateway-paylane')),
        'default' => 'no',
    );
    $options['sofort_legacy_enabled'] = array(
        'title' => __('Sofort', 'wc-gateway-paylane'),
        'type' => 'checkbox',
        'label' => sprintf(__('Enable %s standard', 'wc-gateway-paylane'), __('Sofort', 'wc-gateway-paylane')),
        'default' => 'no',
    );
}

return $options;
