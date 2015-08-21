<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for PayPal Gateway
 */
return array(
                'enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the PayLane payment method.', 'woocommerce'),
                    'default' => 'yes',
                ),
                'title' => array(
                    'title' => __('Name', 'woocommerce'),
                    'type' => 'text',
                    'default' => __('Paylane', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Description', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('Description.', 'woocommerce'),
                    'default' => __('Pay with PayLane.', 'woocommerce')
                ),          
               'secure_form' => array(
                    'title' => __('Secure Form', 'woocommerce'),
                    'type'   => 'title',
		          'description' => __("Customers will be redirected to the PayLane Secure Form for payment", "woocommerce"),
                ), 
                'secure_form_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the Secure Form payment method', 'woocommerce'),
                    'default' => 'yes',
                ),
                'merchant_id' => array(
                    'title' => __('Secure Merchant API key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('API key for connecting to PayLane\'s Secure system.', 'woocommerce'),
                    'default' => __("", 'woocommerce'),
                    'desc_tip' => true,
                ),
                'hash_salt_secure_form' => array(
                    'title' => __('Hash Salt', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Secret hash salt value', 'woocommerce'),
                    'desc_tip' => true,
                ),      
                'redirect_version_secure_form' => array(
		'title'       => __( 'Redirect method', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane redirect method', 'woocommerce' ),
		'default'     => 'get',
		'desc_tip'    => true,
		'options'     => array(
			'GET'          => __( 'GET', 'woocommerce' ),
			'POST' => __( 'POST', 'woocommerce' )
		)
                ),
                'status_secure_form' => array(
                    'title' => __('Successful order status', 'woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('Pending', 'woocommerce'),
                        '1' => __('Complete', 'woocommerce'),
                    ),
                ),                
                'credit_card' => array(
                    'title' => __('Credit Card', 'woocommerce'),
                    'type'   => 'title',
		    'description' => '',
                ), 
                'credit_card_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the Credit Card payment method', 'woocommerce'),
                    'default' => 'yes',
                ),
                'login_credit_card' => array(
                    'title' => __('Login', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Login.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'password_credit_card' => array(
                    'title' => __('Password', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Password.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'hash_salt_credit_card' => array(
                    'title' => __('Hash Salt', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Secret hash salt value', 'woocommerce'),
                    'desc_tip' => true,
                ),   
                'redirect_version_credit_card' => array(
		'title'       => __( 'Redirect method', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'get',
		'desc_tip'    => true,
		'options'     => array(
			'GET'          => __( 'GET', 'woocommerce' ),
			'POST' => __( 'POST', 'woocommerce' )
		)
                ),
                'status_credit_card' => array(
                    'title' => __('Successful order status', 'woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('Pending', 'woocommerce'),
                        '1' => __('Completed', 'woocommerce'),
                    ),
                ),
                'fraud_check' => array(
		'title'       => __( 'Fraud Check', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'false',
		'desc_tip'    => false,
		'options'     => array(
			'true'          => __( 'YES', 'woocommerce' ),
			'false' => __( 'NO', 'woocommerce' )
		),
                ),
                '3ds_check' => array(
		'title'       => __( '3ds Check', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'false',
		'desc_tip'    => false,
		'options'     => array(
			'true'          => __( 'YES', 'woocommerce' ),
			'false' => __( 'NO', 'woocommerce' )
                ),
		),
                'transfer' => array(
                    'title' => __('Bank Wire Transfer', 'woocommerce'),
                    'type'   => 'title',
		    'description' => '',
                ), 
                'transfer_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the Transfer payment method', 'woocommerce'),
                    'default' => 'yes',
                ),
                'login_transfer' => array(
                    'title' => __('Login', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Login.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'password_transfer' => array(
                    'title' => __('Password', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Password.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'hash_salt_transfer' => array(
                    'title' => __('Hash Salt', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Secret hash salt value', 'woocommerce'),
                    'desc_tip' => true,
                ),   
                'redirect_version_transfer' => array(
		'title'       => __( 'Redirect method', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'get',
		'desc_tip'    => true,
		'options'     => array(
			'GET'          => __( 'GET', 'woocommerce' ),
			'POST' => __( 'POST', 'woocommerce' )
		)
                ),
                'status_transfer' => array(
                    'title' => __('Successful order status', 'woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('Pending', 'woocommerce'),
                        '1' => __('Completed', 'woocommerce'),
                    ),
                ),
                'sepa' => array(
                    'title' => __('SEPA', 'woocommerce'),
                    'type'   => 'title',
		    'description' => '',
                ), 
                'sepa_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the SEPA payment method', 'woocommerce'),
                    'default' => 'yes',
                ),  
                'login_sepa' => array(
                    'title' => __('Login', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Login.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'password_sepa' => array(
                    'title' => __('Password', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Password.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'hash_salt_sepa' => array(
                    'title' => __('Hash Salt', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Secret hash salt value', 'woocommerce'),
                    'desc_tip' => true,
                ),   
                'redirect_version_sepa' => array(
		'title'       => __( 'Redirect method', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'get',
		'desc_tip'    => true,
		'options'     => array(
			'GET'          => __( 'GET', 'woocommerce' ),
			'POST' => __( 'POST', 'woocommerce' )
		)
                ),
                'status_sepa' => array(
                    'title' => __('Successful order status', 'woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('Pending', 'woocommerce'),
                        '1' => __('Completed', 'woocommerce'),
                    ),
                ),
                'sofort' => array(
                    'title' => __('SOFORT', 'woocommerce'),
                    'type'   => 'title',
		    'description' => '',
                ), 
                'sofort_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the SOFORT payment method', 'woocommerce'),
                    'default' => 'yes',
                ),
                'login_sofort' => array(
                    'title' => __('Login', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Login.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'password_sofort' => array(
                    'title' => __('Password', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Password.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'hash_salt_sofort' => array(
                    'title' => __('Hash Salt', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Secret hash salt value', 'woocommerce'),
                    'desc_tip' => true,
                ),   
                'redirect_version_sofort' => array(
		'title'       => __( 'Redirect method', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'get',
		'desc_tip'    => true,
		'options'     => array(
			'GET'          => __( 'GET', 'woocommerce' ),
			'POST' => __( 'POST', 'woocommerce' )
		)
                ),
                'status_sofort' => array(
                    'title' => __('Successful order status', 'woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('Pending', 'woocommerce'),
                        '1' => __('Completed', 'woocommerce'),
                    ),
                ),
                'paypal' => array(
                    'title' => __('PAYPAL', 'woocommerce'),
                    'type'   => 'title',
		    'description' => '',
                ), 
                'paypal_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the PAYPAL payment method', 'woocommerce'),
                    'default' => 'yes',
                ),
                'login_paypal' => array(
                    'title' => __('Login', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Login.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'password_paypal' => array(
                    'title' => __('Password', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Password.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'hash_salt_paypal' => array(
                    'title' => __('Hash Salt', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Secret hash salt value', 'woocommerce'),
                    'desc_tip' => true,
                ),   
                'redirect_version_paypal' => array(
		'title'       => __( 'Redirect method', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'get',
		'desc_tip'    => true,
		'options'     => array(
			'GET'          => __( 'GET', 'woocommerce' ),
			'POST' => __( 'POST', 'woocommerce' )
		)
                ),
                'status_paypal' => array(
                    'title' => __('Successful order status', 'woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('Pending', 'woocommerce'),
                        '1' => __('Completed', 'woocommerce'),
                    ),
                ),
                'ideal' => array(
                    'title' => __('iDEAL', 'woocommerce'),
                    'type'   => 'title',
		    'description' => '',
                ), 
                'ideal_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable the iDEAL payment method', 'woocommerce'),
                    'default' => 'yes',
                ),   
                'login_ideal' => array(
                    'title' => __('Login', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Login.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'password_ideal' => array(
                    'title' => __('Password', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Password.', 'woocommerce'),
                    'default' => __('', 'woocommerce'),
                    'desc_tip' => true,
                ),
                'hash_salt_ideal' => array(
                    'title' => __('Hash Salt', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Secret hash salt value', 'woocommerce'),
                    'desc_tip' => true,
                ),   
                'redirect_version_ideal' => array(
		'title'       => __( 'Redirect method', 'woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'PayLane response redirect method', 'woocommerce' ),
		'default'     => 'get',
		'desc_tip'    => true,
		'options'     => array(
			'GET'          => __( 'GET', 'woocommerce' ),
			'POST' => __( 'POST', 'woocommerce' )
		)
                ),
                'status_ideal' => array(
                    'title' => __('Successful order status', 'woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('Pending', 'woocommerce'),
                        '1' => __('Completed', 'woocommerce'),
                    ),
                ),
                'notifications' => array(
                    'title' => __('Transaction notifications', 'woocommerce'),
                    'type'   => 'title',
		    'description' => 'Automated transaction notification handling from PayLane\'s systems.',
                ),
                'notifications_enabled' => array(
                    'title' => __('Enable/disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable notifications', 'woocommerce'),
                    'default' => 'no',
                ),
                'notifications_token' => array(
                    'title' => __('Token', 'woocommerce'),
                    'type' => 'text',
                    'desc_tip' => true,
                ),
                'notifications_http_auth' => array(
                    'title' => __('Include HTTP authorization', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable.', 'woocommerce'),
                    'default' => 'no',
                ),
                'notifications_login' => array(
                    'title' => __('HTTP login', 'woocommerce'),
                    'type' => 'text',
                    'desc_tip' => true,
                ),
                'notifications_password' => array(
                    'title' => __('HTTP Password', 'woocommerce'),
                    'type' => 'text',
                    'desc_tip' => true,
                ),
);
