<?php if ( !defined( 'ABSPATH' ) ) exit;

function wcpl_get_errors_by_code($code, $message)
{
	$codes = array(
		
		302 => __("Direct debit not accessible for this country", 'wc-gateway-paylane'), //"Direct debit not accessible for this country %COUNTRY_CODE%"
		303 => __("Direct debit declined", 'wc-gateway-paylane'),

		312 => __("Account holder name is not valid", 'wc-gateway-paylane'),
		313 => __("Customer name is not valid", 'wc-gateway-paylane'),
		314 => __("Customer e-mail is not valid", 'wc-gateway-paylane'),
		315 => __("Customer address (street and house) is not valid", 'wc-gateway-paylane'),
		316 => __("Customer city is not valid", 'wc-gateway-paylane'),
		317 => __("Customer zip code is not valid", 'wc-gateway-paylane'),
		318 => __("Customer state is not valid", 'wc-gateway-paylane'),
		319 => __("Customer country is not valid", 'wc-gateway-paylane'),
		320 => __("Amount is not valid", 'wc-gateway-paylane'),
		321 => __("Amount is too low", 'wc-gateway-paylane'),
		322 => __("Currency code is not valid", 'wc-gateway-paylane'),
		323 => __("Customer IP address is not valid", 'wc-gateway-paylane'),
		324 => __("Description is not valid", 'wc-gateway-paylane'),
		325 => __("Account country is not valid", 'wc-gateway-paylane'),
		326 => __("Bank code (SWIFT/BIC/BLZ) is not valid", 'wc-gateway-paylane'),
		327 => __("Account number is not valid", 'wc-gateway-paylane'),
		328 => __("Processing date is not valid", 'wc-gateway-paylane'),
		329 => __("Processing date must be from the future", 'wc-gateway-paylane'),
		330 => __("Account data or SEPA data should be set", 'wc-gateway-paylane'),

		340 => __("Amount is too low", 'wc-gateway-paylane'),
		341 => __("Amount is too large", 'wc-gateway-paylane'), //"Amount is too large (%AMOUNT%)"
		342 => __("To many transactions for this card/account", 'wc-gateway-paylane'),
		343 => __("Risk department rejection (RF)", 'wc-gateway-paylane'),
		344 => __("Risk department rejection (CB)", 'wc-gateway-paylane'),
		345 => __("Risk department rejection (PE)", 'wc-gateway-paylane'),

		371 => __("Wrong date format", 'wc-gateway-paylane'),
		372 => __("This API method is not allowed for this account", 'wc-gateway-paylane'),
		373 => __("Data not found", 'wc-gateway-paylane'),

		401 => __("Multiple same transactions lock triggered. Wait and try again", 'wc-gateway-paylane'), //Multiple same transactions lock triggered. Wait %LOCK_TIME% s and try again.
		402 => __("Payment gateway problem. Please try again later", 'wc-gateway-paylane'),
		403 => __("Card declined", 'wc-gateway-paylane'),
		404 => __("Transaction in this currency is not allowed", 'wc-gateway-paylane'), //"Transaction in this currency %CURRENCY_CODE% is not allowed"
		405 => __("Unknown payment method or method not set", 'wc-gateway-paylane'),
		406 => __("More than one payment method provided. Only one payment method is allowed", 'wc-gateway-paylane'),
		407 => __("Capture later not possible with this payment method", 'wc-gateway-paylane'),
		408 => __("Feature not available for this payment method", 'wc-gateway-paylane'), //"Feature '%FEATURE%' not available for this payment method"
		409 => __("Overriding default feature not allowed for this merchant account", 'wc-gateway-paylane'), //"Overriding default %FEATURE% not allowed for this merchant account"
		410 => __("Unsupported payment method", 'wc-gateway-paylane'),

		411 => __("Card number format is not valid", 'wc-gateway-paylane'),
		412 => __("Card expiration year is not valid", 'wc-gateway-paylane'),
		413 => __("Card expiration month is not valid", 'wc-gateway-paylane'),
		414 => __("Card expiration date is not valid", 'wc-gateway-paylane'),
		415 => __("Card has expired", 'wc-gateway-paylane'),
		416 => __("Card code (CVV2/CVC2/CID) format is not valid", 'wc-gateway-paylane'),
		417 => __("Name on card is not valid", 'wc-gateway-paylane'),
		418 => __("Cardholder name is not valid", 'wc-gateway-paylane'),
		419 => __("Cardholder e-mail is not valid", 'wc-gateway-paylane'),
		420 => __("Cardholder address (street and house) is not valid", 'wc-gateway-paylane'),
		421 => __("Cardholder city is not valid", 'wc-gateway-paylane'),
		422 => __("Cardholder zip code is not valid", 'wc-gateway-paylane'),
		423 => __("Cardholder state is not valid", 'wc-gateway-paylane'),
		424 => __("Cardholder country is not valid", 'wc-gateway-paylane'),
		425 => __("Amount is not valid", 'wc-gateway-paylane'),
		426 => __("Amount is too low", 'wc-gateway-paylane'),
		427 => __("Currency code is not valid", 'wc-gateway-paylane'),
		428 => __("Client IP address is not valid", 'wc-gateway-paylane'),
		429 => __("Description is not valid", 'wc-gateway-paylane'),
		430 => __("Unknown card type or card number invalid", 'wc-gateway-paylane'),
		431 => __("Card issue number is not valid", 'wc-gateway-paylane'),
		432 => __("Fraud check on is not valid", 'wc-gateway-paylane'),
		433 => __("AVS level is not valid", 'wc-gateway-paylane'),
		434 => __("Transaction declined", 'wc-gateway-paylane'),

		441 => __("Sale Authorization ID is not valid", 'wc-gateway-paylane'),
		442 => __("Sale Authorization ID not found or the authorization has been closed", 'wc-gateway-paylane'),
		443 => __("Capture sale amount greater than the authorization amount", 'wc-gateway-paylane'),
		444 => __("Capture sale amount less than the authorization amount", 'wc-gateway-paylane'),
		445 => __("Close Authorization amount different than the authorization amount", 'wc-gateway-paylane'),

		470 => __("Resale without card code is not allowed for this merchant account", 'wc-gateway-paylane'),
		471 => __("Sale ID is not valid", 'wc-gateway-paylane'),
		472 => __("Resale amount is not valid", 'wc-gateway-paylane'),
		473 => __("Amount is too low", 'wc-gateway-paylane'),
		474 => __("Resale currency code is not valid", 'wc-gateway-paylane'),
		475 => __("Resale description is not valid", 'wc-gateway-paylane'),
		476 => __("Sale ID not found", 'wc-gateway-paylane'),
		477 => __("Cannot resale. Chargeback assigned to Sale ID", 'wc-gateway-paylane'),
		478 => __("Cannot resale this sale", 'wc-gateway-paylane'),
		479 => __("Card has expired", 'wc-gateway-paylane'),
		480 => __("Cannot resale. Reversal assigned to Sale ID", 'wc-gateway-paylane'),

		481 => __("Sale ID is not valid", 'wc-gateway-paylane'),
		482 => __("Refund amount is not valid", 'wc-gateway-paylane'),
		483 => __("Refund reason is not valid", 'wc-gateway-paylane'),
		484 => __("Sale ID not found", 'wc-gateway-paylane'),
		485 => __("Cannot refund. Chargeback assigned to Sale ID", 'wc-gateway-paylane'),
		486 => __("Cannot refund. Exceeded available refund amount", 'wc-gateway-paylane'),
		487 => __("Cannot refund. Sale is already completely refunded", 'wc-gateway-paylane'),
		488 => __("Cannot refund this sale", 'wc-gateway-paylane'),
		489 => __("Cannot refund. Reversal assigned to Sale ID", 'wc-gateway-paylane'),
		490 => __("Refund ID not found", 'wc-gateway-paylane'),

		491 => __("Sale ID list is not set or empty", 'wc-gateway-paylane'),
		492 => __("Sale ID list is too large", 'wc-gateway-paylane'), //"Sale ID list is too large (more than %LIST_SIZE%)"
		493 => __("Sale ID is not valid", 'wc-gateway-paylane'),//"Sale ID %SALE_ID% at position %POSITION% is not valid"
		494 => __("Sale ID / Sale error ID not found", 'wc-gateway-paylane'),
		495 => __("Too many sales / sale errors found", 'wc-gateway-paylane'),

		501 => __("Internal server error. Please try again later", 'wc-gateway-paylane'),
		502 => __("Payment gateway error. Please try again later", 'wc-gateway-paylane'),
		503 => __("Payment method not allowed for this account", 'wc-gateway-paylane'),//"Payment method %PAYMENT_METHOD% not allowed for this account"
		504 => __("Service not accessible for this account", 'wc-gateway-paylane'), //"Service %SERVICE_NAME% not accessible for this account"
		505 => __("This merchant account is inactive", 'wc-gateway-paylane'),
		506 => __("No currency rate in Currency Converter", 'wc-gateway-paylane'), //"No currency rate for %CURRENCY% for %DATE% in Currency Converter"
		507 => __("Sale error conversion appeard", 'wc-gateway-paylane'),
		508 => __("Request is missing parameter", 'wc-gateway-paylane'), //"Request is missing '%PARAM%' parameter"
		509 => __("Requests parameter is not valid", 'wc-gateway-paylane'), //"Requests parameter '%PARAM%' is not valid"

		601 => __("Fraud attempt detected", 'wc-gateway-paylane'), //"Fraud attempt detected. Score is: %FRAUD_SCORE% (range is 0-100). Max fraud score accepted for this account is: %FRAUD_MAX_SCORE%"

		611 => __("Blacklisted account number found", 'wc-gateway-paylane'),
		612 => __("Blacklisted card country found", 'wc-gateway-paylane'),
		613 => __("Blacklisted card number found", 'wc-gateway-paylane'),
		614 => __("Blacklisted customer country found", 'wc-gateway-paylane'),
		615 => __("Blacklisted customer email found", 'wc-gateway-paylane'),
		616 => __("Blacklisted customer IP address found", 'wc-gateway-paylane'),
		617 => __("Unknown blacklisting operation", 'wc-gateway-paylane'),

		701 => __("3-D Secure authentication server error. Please try again later or use card not enrolled in 3-D Secure", 'wc-gateway-paylane'),
		702 => __("3-D Secure authentication server problem. Please try again later or use card not enrolled in 3-D Secure", 'wc-gateway-paylane'),
		703 => __("3-D Secure authentication failed. Credit card cannot be accepted for payment", 'wc-gateway-paylane'),
		704 => __("3-D Secure authentication failed. Card declined", 'wc-gateway-paylane'),

		711 => __("Card number format is not valid", 'wc-gateway-paylane'),
		712 => __("Card expiration year is not valid", 'wc-gateway-paylane'),
		713 => __("Card expiration month is not valid", 'wc-gateway-paylane'),
		714 => __("Card has expired", 'wc-gateway-paylane'),
		715 => __("Amount is not valid", 'wc-gateway-paylane'),
		716 => __("Currency code is not valid", 'wc-gateway-paylane'),
		717 => __("Back URL is not valid", 'wc-gateway-paylane'),
		718 => __("Unknown card type or card number invalid", 'wc-gateway-paylane'),
		719 => __("Card issue number is not valid", 'wc-gateway-paylane'),
		720 => __("Unable to verify enrollment for 3-D Secure. You can perform a normal payment without 3-D Secure or decline the transaction", 'wc-gateway-paylane'),

		721 => __("Secure3d ID is not valid", 'wc-gateway-paylane'),
		722 => __("Authentication response message is not valid", 'wc-gateway-paylane'),
		723 => __("Secure3d ID not found", 'wc-gateway-paylane'),
		724 => __("3-D Secure authentication was completed before", 'wc-gateway-paylane'), //"3-D Secure authentication was completed at %DATE_TIME% UTC"
		725 => __("Authentication is not available for this credit card. You can perform a normal payment without 3-D Secure or decline the transaction", 'wc-gateway-paylane'),

		731 => __("Completed authentication with this Secure3d ID not found", 'wc-gateway-paylane'),
		732 => __("Sale and 3-D Secure card number are different", 'wc-gateway-paylane'),
		733 => __("Sale and 3-D Secure card expiration year are different", 'wc-gateway-paylane'),
		734 => __("Sale and 3-D Secure card expiration month are different", 'wc-gateway-paylane'),
		735 => __("Sale and 3-D Secure amount are different", 'wc-gateway-paylane'),
		736 => __("Sale and 3-D Secure currency code are different", 'wc-gateway-paylane'),
		737 => __("Sale was performed for this Secure3d ID", 'wc-gateway-paylane'),//"Sale with ID %SALE_ID% was performed for this Secure3d ID"

		760 => __("Unrecognized or malformed token", 'wc-gateway-paylane'),
		761 => __("The provided token does not exist", 'wc-gateway-paylane'),

		780 => __("Service fee amount not set", 'wc-gateway-paylane'),
		781 => __("Id sub-merchant not set", 'wc-gateway-paylane'),
		782 => __("Account cannot process sub-merchant transactions", 'wc-gateway-paylane'),
		783 => __("Invalid sub-merchant id", 'wc-gateway-paylane'),
		784 => __("Sub-merchant is disabled", 'wc-gateway-paylane'),
		785 => __("Service fee amount is too low", 'wc-gateway-paylane'),
		786 => __("Invalid service fee amount", 'wc-gateway-paylane'),
		787 => __("Sub-merchant ID mismatch", 'wc-gateway-paylane'),

		801 => __("Wrong input data", 'wc-gateway-paylane'),
		802 => __("Paypal server error", 'wc-gateway-paylane'),
		803 => __("Polskie ePłatności token not found in database", 'wc-gateway-paylane'),
		804 => __("Transaction not found", 'wc-gateway-paylane'),
		805 => __("Paypal checkout id not found in database", 'wc-gateway-paylane'),
		806 => __("Wrong PayPal rebilling period", 'wc-gateway-paylane'),
		807 => __("Wrong PayPal rebilling start date", 'wc-gateway-paylane'),
		808 => __("PayPal recurring profile not created", 'wc-gateway-paylane'),
		809 => __("PayPal recurring already disabled", 'wc-gateway-paylane'),
		810 => __("PayPal sale authorization not found", 'wc-gateway-paylane'),
		811 => __("PayPal recurring not found", 'wc-gateway-paylane'),
		812 => __("Transaction cancelled", 'wc-gateway-paylane'),
		813 => __("Locale code is not valid", 'wc-gateway-paylane'),
		814 => __("PayPal permissions not granted", 'wc-gateway-paylane'),
		815 => __("PayPal transaciton not finished", 'wc-gateway-paylane'),

		830 => __("Transaction cannot be settled", 'wc-gateway-paylane'),
		831 => __("Transaction has expired", 'wc-gateway-paylane'),
		832 => __("Transaction aborted by customer", 'wc-gateway-paylane'),

		833 => __("Unknown notification format", 'wc-gateway-paylane'),
		834 => __("Unknown transaction identifier", 'wc-gateway-paylane'),
		835 => __("Transaction notification error", 'wc-gateway-paylane'),
		836 => __("Unknown notification confirmation status", 'wc-gateway-paylane'),
		837 => __("Transaction amount and notification amount are different", 'wc-gateway-paylane'),
		838 => __("Transaction unsuccessfull", 'wc-gateway-paylane'),
		839 => __("Transaction currency And notification currency are different", 'wc-gateway-paylane'),
		840 => __("Transaction checksum incorrect", 'wc-gateway-paylane'),
		841 => __("Party ID mismatch", 'wc-gateway-paylane'),

		850 => __("Unknown Project type", 'wc-gateway-paylane'),
		851 => __("Notification checksum incorrect", 'wc-gateway-paylane'),

		870 => __("Promo code does not exist", 'wc-gateway-paylane'),
		871 => __("Promo code has been disabled", 'wc-gateway-paylane'),
		872 => __("Promo code has expired", 'wc-gateway-paylane'),

		880 => __("Invalid user name", 'wc-gateway-paylane'),
		881 => __("Invalid password", 'wc-gateway-paylane'),
		882 => __("Invalid email address", 'wc-gateway-paylane'),
		883 => __("User name already exists", 'wc-gateway-paylane'),
		884 => __("Wrong company name", 'wc-gateway-paylane'),
		885 => __("Wrong user name", 'wc-gateway-paylane'),
		886 => __("Email already exists", 'wc-gateway-paylane'),

		887 => __("This Device ID is disabled", 'wc-gateway-paylane'),
		888 => __("Device PIN is not valid", 'wc-gateway-paylane'),
		889 => __("Device ID length is not valid", 'wc-gateway-paylane'),

		900 => __('Unknown payment method, could not load bank module', 'wc-gateway-paylane'),
		901 => __('Could not load bank parser module', 'wc-gateway-paylane'),
		902 => __('Sale and bank transfer amount don\'t match', 'wc-gateway-paylane'), //'Sale and bank transfer amount don\'t match (%TRANSACTION_AMOUNT% - %TRANSFER_AMOUNT)'
		903 => __('Transaction with transfer title not found in database', 'wc-gateway-paylane'),//'Transaction with transfer title "%TRANSFER_TITLE%" not found in database,'
		904 => __('Transaction unsuccessful or aborted by payer', 'wc-gateway-paylane'),

		930 => __("Delivery zip code is not valid", 'wc-gateway-paylane'),
		931 => __("Delivery address (street and house) is not valid", 'wc-gateway-paylane'),
		932 => __("Delivery country is not valid", 'wc-gateway-paylane'),
		933 => __("Delivery city is not valid", 'wc-gateway-paylane'),
		934 => __("Delivery risk is not valid", 'wc-gateway-paylane'),
		935 => __("Delivery risk is not set", 'wc-gateway-paylane'),
		936 => __("Delivery country is not set", 'wc-gateway-paylane'),

		960 => __('Source amount and summed items amount not equal', 'wc-gateway-paylane'),
		961 => __('Invalid invoice prefix value', 'wc-gateway-paylane'),
		962 => __('Invalid invoice vat id', 'wc-gateway-paylane'),
		963 => __('Invalid invoice vat rate', 'wc-gateway-paylane'),
		964 => __('Sale id is not valid', 'wc-gateway-paylane'),
		965 => __('Refund id is not valid', 'wc-gateway-paylane'),
		966 => __('Incorrect transactions number', 'wc-gateway-paylane'),
		967 => __('Invalid language', 'wc-gateway-paylane'),
		968 => __('Original invoice have different param value', 'wc-gateway-paylane'),//'Original invoice have different param value (%PARAM%)'
		969 => __('You have to perform sale invoice first', 'wc-gateway-paylane'),
		970 => __('Invoice not found', 'wc-gateway-paylane'),
		971 => __('Invoice already created', 'wc-gateway-paylane'),//'Invoice already created, id_invoice=%ID_INVOICE%'
		972 => __('Source transaction not found', 'wc-gateway-paylane'),
		973 => __('One of previous refunds have no invoice', 'wc-gateway-paylane'),

		990 => __('No invoice data found', 'wc-gateway-paylane'),
		991 => __('Receiver already exported', 'wc-gateway-paylane'),
		992 => __('Missing Symfonia credit (Ma) account configuration for MID', 'wc-gateway-paylane'),//'Missing Symfonia credit (Ma) account configuration for MID = %MID%'
		993 => __('Missing Symfonia debit (Wn) account configuration for MID', 'wc-gateway-paylane'),//'Missing Symfonia debit (Wn) account configuration for MID = %MID%, currency = %CURRENCY%'

		1000 =>__('No transaction export data found for the specified search criteria', 'wc-gateway-paylane'),

		1010 =>__('Too many search results, please narrow down the search criteria', 'wc-gateway-paylane'),
		1011 =>__('The parameter is invalid', 'wc-gateway-paylane'),//'The %PARAM% parameter is invalid'

		1020 =>__('Invalid captcha', 'wc-gateway-paylane'),
		1021 =>__('No such company record exists', 'wc-gateway-paylane'),

		1030 =>__('The IP address is not valid', 'wc-gateway-paylane'),//'The IP address %IP% is not valid'
		1031 =>__('API passwords must be at least 10 characters in length', 'wc-gateway-paylane'),
		1032 =>__('The uploaded Secure Form logo appears to be malformed or corrupt', 'wc-gateway-paylane'),
		1033 =>__('The Secure Form description must be a text (no HTML) value up to 500 characters in length', 'wc-gateway-paylane'),
		1034 =>__('Secure group name cannot be blank', 'wc-gateway-paylane'),
	);


	if(!isset($codes[$code])){
		return null;
	}

	$variable_codes = array(
		302, 341, 401, 404, 408, 409, 492, 493, 503, 504, 506, 508, 509, 601, 724, 737, 902, 903, 968, 971, 992, 993, 1011, 1030 
	);

	if(!in_array($code, $variable_codes)){
		return $codes[$code];
	}


	return $codes[$code].' ('.$message.')';
}
