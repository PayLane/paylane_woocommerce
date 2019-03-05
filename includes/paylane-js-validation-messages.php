<?php if ( !defined( 'ABSPATH' ) ) exit;

return array(
	'defaults'=> array(
		'required'           => __( 'This field is required', 'wc-gateway-paylane' ),
		'pattern'            => __( 'Incorrect field format', 'wc-gateway-paylane' ),
		'cardNumber'         => __( 'Invalid card number', 'wc-gateway-paylane' ),
		'cardExpirationDate' => __( 'Invalid card expiration date', 'wc-gateway-paylane' ),
		'cardSecurityCode'   => __( 'Invalid card security code', 'wc-gateway-paylane' ),
	),
	'nameOnCard' => array(
		'minLength' => sprintf( __( 'Minimum %d characters required', 'wc-gateway-paylane' ), 2 ),
		'maxLength' => sprintf( __( 'Max. %d characters required', 'wc-gateway-paylane' ), 50 ),
	),
);