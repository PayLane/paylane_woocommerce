<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Plugin Name: WooCommerce PayLane Gateway
 * Description: PayLane (Polskie ePłatności Online) payment module for WooCommerce.
 * Version: 2.0.4
 * Author: Paylane (Polskie ePłatności Online)
 * Author URI: https://paylane.pl
 * Plugin URI: https://github.com/PayLane/paylane_woocommerce
 * Text Domain: wc-gateway-paylane
 * Requires at least: 4.4
 * Tested up to: 5.1
 * WC requires at least: 2.6
 * WC tested up to: 3.5
 **/

add_filter( 'the_posts', 'generate_error_page', -10 );
add_filter( 'woocommerce_notice_types', 'add_paylane_notice_type' );
add_action( 'before_woocommerce_pay', 'paylane_js_validation', 10, 0 );
add_action( 'woocommerce_checkout_before_order_review', 'paylane_js_validation', 10, 0 );

function add_paylane_notice_type( $notice_types )
{
	$notice_types[] = "paylane_error";
	return $notice_types;
}

function paylane_js_validation()
{
	$json = json_encode( require_once __DIR__ . '/includes/paylane-js-validation-messages.php' );

	echo <<<EOF
<script>
	const PAYLANE_VALIDATION_MESSAGES = $json
</script>
EOF;

}


function generate_error_page( $posts )
{
	global $wp, $wp_query;

	if ( ! defined( 'PAYLANE_ERROR_PAGE' ) && isset($_GET['paylane-api-error']) && wc_notice_count('paylane_error') > 0 )
	{
		define( 'PAYLANE_ERROR_PAGE', true );

		// create a fake virtual page
		$post = new stdClass;
		$post->post_author    = 1;
		$post->post_name      = 'Error';
		$post->guid           = home_url() . '/' . 'paylane/error';
		$post->post_title     = __('Payment Error Occurred', 'wc-gateway-paylane');
		$post->post_content   = require_once __DIR__ . '/views/error.php';
		$post->ID             = -1;
		$post->post_type      = 'page';
		$post->post_status    = 'static';
		$post->comment_status = 'closed';
		$post->ping_status    = 'open';
		$post->comment_count  = 0;
		$post->post_date      = current_time( 'mysql' );
		$post->post_date_gmt  = current_time( 'mysql', 1 );
		$posts                = NULL;
		$posts[]              = $post;

		// make wpQuery believe this is a real page too
		$wp_query->is_page             = true;
		$wp_query->is_singular         = true;
		$wp_query->is_home             = false;
		$wp_query->is_archive          = false;
		$wp_query->is_category         = false;
		unset( $wp_query->query[ 'error' ] );
		$wp_query->query_vars[ 'error' ] = '';
		$wp_query->is_404 = false;
	}

	return $posts;
}

function init_paylane()
{
	// Localisation
	load_plugin_textdomain('wc-gateway-paylane', false, dirname(plugin_basename(__FILE__)) . '/languages');
	add_filter( 'plugin_row_meta', 'paylane_plugin_row_meta', 20, 4 );

	if ( ! class_exists('WC_Payment_Gateway') )
	{
		add_action('admin_init', 'child_plugin_has_parent_plugin');

		function child_plugin_has_parent_plugin()
		{
			if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('woocommerce/woocommerce.php'))
			{
				add_action('admin_notices', 'child_plugin_notice');
				deactivate_plugins(plugin_basename(__FILE__));

				if (isset($_GET['activate']))
				{
					unset($_GET['activate']);
				}
			}
		}

		function child_plugin_notice()
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
	function paylane_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status )
	{
		if (basename($plugin_file) === basename(__FILE__))
		{
			$url   = 'https://paylane.pl/wyprobuj/?utm_source=woocommerce-plugin';
			$label = __('Create account', 'wc-gateway-paylane');
			$icon  = 'dashicons-id-alt';

			$plugin_meta[] = sprintf( '<a href="%s" target="_blank"><span class="dashicons %s"></span>%s</a>', $url, $icon, $label );
		}

		return $plugin_meta;
	}

	class Paylane_Woocommerce_Tools
	{
		public static function getIdealBanks($login, $password)
		{
			if (!class_exists('PayLaneRestClient'))
			{
				require_once __DIR__ . '/includes/paylane-rest.php';
			}

			$client = new PayLaneRestClient($login, $password);
			$codes  = null;

			try
			{
				$status = $client->idealBankCodes();
			}
			catch (Exception $e)
			{
			}

			if (!$client->isSuccess())
			{
				$codes = __('This API method is not allowed for this merchant account.', 'wc-gateway-paylane');
			}
			else
			{
				$codes .= '<select name="bank-code">';
				foreach ($status['data'] as $bank)
				{
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
			WC_Gateway_Paylane::PAYMENT_METHOD_SECURE_FORM   => 'Secure Form',
			WC_Gateway_Paylane::PAYMENT_METHOD_CREDIT_CARD   => 'Credit Card',
			WC_Gateway_Paylane::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
			WC_Gateway_Paylane::PAYMENT_METHOD_SEPA          => 'SEPA',
			WC_Gateway_Paylane::PAYMENT_METHOD_SOFORT        => 'Sofort',
			WC_Gateway_Paylane::PAYMENT_METHOD_PAYPAL        => 'PayPal',
			WC_Gateway_Paylane::PAYMENT_METHOD_IDEAL         => 'iDEAL',
		);

		private $order_status_to_id = array(
			WC_Gateway_Paylane::ORDER_STATUS_PENDING    => 0,
			WC_Gateway_Paylane::ORDER_STATUS_PROCESSING => 1,
			WC_Gateway_Paylane::ORDER_STATUS_ON_HOLD    => 2,
			WC_Gateway_Paylane::ORDER_STATUS_COMPLETED  => 3,
			WC_Gateway_Paylane::ORDER_STATUS_CANCELLED  => 4,
			WC_Gateway_Paylane::ORDER_STATUS_REFUNDED   => 5,
			WC_Gateway_Paylane::ORDER_STATUS_FAILED     => 6,
		);

		private static $instance = null;

		public function instance()
		{
			if ( null === self::$instance )
			{
				self::$instance = new WC_Gateway_Paylane;
			}

			return self::$instance;
		}

		public function __construct()
		{
			global $woocommerce;

			$this->id              = __('paylane', 'wc-gateway-paylane');
			$this->method_title    = __('Paylane', 'wc-gateway-paylane');
			$this->has_fields      = true;
			$this->notify_link     = add_query_arg('wc-api', 'WC_Gateway_Paylane', home_url('/'));
			$this->notify_link_3ds = add_query_arg('wc-api', 'WC_Gateway_Paylane_3ds', home_url('/'));
			$this->supports        = array(
				'products',
				'refunds',
				'subscriptions',
				'subscription_cancellation',
				'subscription_suspension',
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes',
				'subscription_payment_method_change'
			);

			$this->title               = $this->get_option('title');
			$this->description         = $this->get_option('description');
			$this->payment_method      = $this->get_option('payment_method');
			$this->secure_form         = $this->get_option('secure_form');
			$this->merchant_id         = $this->get_option('merchant_id');
			$this->fraud_check         = $this->get_option('fraud_check');
			$this->ds_check            = $this->get_option('3ds_check');
			$this->enable_notification = $this->get_option('notifications_enabled');
			$this->first_name          = '';
			$this->last_name           = '';

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

			if ('SecureForm' === $this->get_option('connection_mode'))
			{
				$this->init_secure_form();
			}
			else
			{
				$this->init_api();
			}
		}

		/**
		 * Register Custom css & js
		 */
		public function paylane_payment_style()
		{
			if (is_checkout())
			{
				wp_register_style(
					'paylane-woocommerce', plugins_url(
						'assets/css/paylane-woocommerce-' . $this->get_option('design') . '.css', __FILE__
					), [], '204_' . $this->get_option('design'), 'all'
				);
				wp_enqueue_style( 'paylane-woocommerce' );
				wp_enqueue_script(
					'paylane-woocommerce-script', plugin_dir_url(__FILE__) . 'assets/js/paylane-woocommerce.js', [],
					'204', true
				);
			}
		}

		//Main function which sends data to PayLane service and get response
		function data_handler()
		{
			if (isset($_POST['content']) && ($this->enable_notification === 'yes'))
			{
				if ( ! isset($_POST['communication_id']) || empty($_POST['communication_id']))
				{
					die('Empty communication id');
				}

				if ( !empty(($this->get_option('notifications_token'))) && ($this->get_option('notifications_token') !== $_POST['token']))
				{
					die('Wrong token');
				}

				$this->handle_notification($_POST['content'], $_POST['token'], $_POST['communication_id']);
				unset($_POST['content']);
			}

			$type = null;

			if (isset($_GET['type']))
			{
				$type = $_GET['type'];
			}

			if ( !$type )
			{
				$this->response_check();
			}
			else
			{
				if($type == "secure_form")
				{
					$this->send_payment_data($_GET['order_id']);
					unset($_GET['order_id']);
				}
				else
				{
					require_once __DIR__ . '/includes/paylane-rest.php';
					$client = new PayLaneRestClient($this->get_option('login_PayLane'), $this->get_option('password_PayLane'));
					@session_start();

					$params = $_SESSION['paylane-data'];

					try
					{
						switch($type)
						{
							case WC_Gateway_Paylane::PAYMENT_METHOD_CREDIT_CARD:

								if ( $this->get_option('3ds_check') == 'true' )
								{
									try
									{
										$result = $client->checkCard3DSecureByToken($params);
									}
									catch (Exception $e)
									{
										$this->print_error_page($e->getMessage());
										return;
									}

									if ( $client->isSuccess() )
									{
										if (true == $result['is_card_enrolled'])
										{
											wp_redirect($result['redirect_url']);
											exit;
										}
									}
									else
									{
										$status = $client->cardSaleByToken($params);
									}
								}
								else
								{
									$status = $client->cardSaleByToken($params);
								}
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
						}
					}
					catch (Exception $e)
					{
						$this->print_error_page($e->getMessage());
						return;
					}
					
					if ( $client->isSuccess() )
					{
						switch($type)
						{
							case WC_Gateway_Paylane::PAYMENT_METHOD_CREDIT_CARD:
								echo __("Success, id_sale:",'wc-gateway-paylane')." {$status['id_sale']} \n";
								$this->set_order_paylane_id( $_GET['order_id'], $status['id_sale'] );
								$this->finish_order( $_GET['order_id'], $this->get_option('status_successful_order') );
								break;

							case WC_Gateway_Paylane::PAYMENT_METHOD_SEPA:
								echo __("Success, id_sale:",'wc-gateway-paylane')." {$status['id_sale']} \n";
								$this->set_order_paylane_id( $_GET['order_id'], $status['id_sale'] );
								$this->finish_order($_GET['order_id'], $this->get_option('status_successful_order'));
								break;

							case WC_Gateway_Paylane::PAYMENT_METHOD_IDEAL:
								wp_redirect( $status['redirect_url'] );
								exit;
								break;

							case WC_Gateway_Paylane::PAYMENT_METHOD_BANK_TRANSFER:
								wp_redirect( $status['redirect_url'] );
								exit;
								break;

							case WC_Gateway_Paylane::PAYMENT_METHOD_SOFORT:
								wp_redirect( $status['redirect_url'] );
								exit;
								break;

							case WC_Gateway_Paylane::PAYMENT_METHOD_PAYPAL:
								wp_redirect( $status['redirect_url'] );
								exit;
								break;
						}
					}
					else
					{
						$error_message = '';

						if ( isset($status['error']['id_error']) )
						{
							$error_message .= __( 'Error ID:', 'wc-gateway-paylane' ) . " {$status['error']['id_error']} <br>";
						}

						if ( isset($status['error']['error_number']) )
						{
							$error_message .= __( 'Error number:', 'wc-gateway-paylane' ) . " {$status['error']['error_number']} <br>";
						}

						if ( isset($status['error']['error_description']) )
						{
							$error_message .= __( 'Error description:', 'wc-gateway-paylane' ) . " {$status['error']['error_description']}";
						}

						$this->finish_order( $_GET['order_id'], $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED), $error_message);
						$this->print_error_page( $error_message );

						exit;
					}
				}
			}
			exit;
		}

		/**
		 * Check response from PayLane service and proceed it to finish orderd
		 */
		function response_check()
		{
			if(isset($_POST['description']))
			{
				$order_id = $_POST['description'];
			}
			else
			{
				$order_id = $_GET['description'];
			}

			$type             = get_post_meta( $order_id, 'paylane-type', true );
			$redirect_version = $this->get_option( 'redirect_version_' . $type );

			if ( $redirect_version == 'POST' )
			{
				$response['status']      = $_POST['status'];
				$response['description'] = $_POST['description'];
				$response['amount']      = $_POST['amount'];
				$response['currency']    = $_POST['currency'];
				$response['hash']        = $_POST['hash'];

				if ( isset( $_POST['id_error'] ) || isset( $_POST['error_code'] ) )
				{
					$response['id_error'] = $_POST['id_error'];
					$error_message        = "Error: " . $_POST['id_error'];

					if ( isset( $_POST['error_code'] ) )
					{
						$error_message .= " - ".$_POST['error_code'];
					}

					if ( isset( $_POST['error_text'] ) )
					{
						$error_message .= " - ".$_POST['error_text'];
					}
				}
				else
				{
					$response['id_sale'] = $_POST['id_sale'];
					$this->set_order_paylane_id($response['description'], $response['id_sale']);
				}
			}
			else
			{
				$response['status']      = $_GET['status'];
				$response['description'] = $_GET['description'];
				$response['amount']      = $_GET['amount'];
				$response['currency']    = $_GET['currency'];
				$response['hash']        = $_GET['hash'];

				if ( isset( $_GET['id_error'] ) || isset( $_GET['error_code'] ) )
				{
					$response['id_error'] = $_GET['id_error'];
					$error_message        = "Error: " . $_GET['id_error'];

					if( isset( $_GET['error_code'] ) )
					{
						$error_message .= " - ".$_GET['error_code'];
					}

					if( isset( $_GET['error_text'] ) )
					{
						$error_message .= " - ".$_GET['error_text'];
					}
				}
				else
				{
					$response['id_sale'] = $_GET['id_sale'];
					$this->set_order_paylane_id($response['description'], $response['id_sale']);
				}
			}

			if ( ! isset($error_message) )
			{
				$hash_data = array(
					'status'      => $response['status'],
					'description' => $response['description'],
					'amount'      => $response['amount'],
					'id'          => $response['id_sale']
				);

				$hash = $this->hash( $hash_data, 'response', $type, $response['currency'] );

				if ( $hash == $response['hash'] )
				{
					if ($response['status'] != 'ERROR')
					{
						if($response['status'] == 'PENDING')
						{
							$this->finish_order(
								$response['description'],
								$this->get_order_status_id( WC_Gateway_Paylane::ORDER_STATUS_PENDING ),
								__( 'Payment awaiting confirmation', 'wc-gateway-paylane' )
							);
						}
						else
						{
							@session_start();
							$this->finish_order( $response['description'], $this->get_option( 'status_successful_order' ) );
						}
					}
				}
				else
				{
					$this->finish_order( $response['description'], $this->get_order_status_id( WC_Gateway_Paylane::ORDER_STATUS_FAILED ), __('Wrong hash', 'wc-gateway-paylane') );
					$this->print_error_page( __('Wrong hash', 'wc-gateway-paylane') );
				}
			}
			else
			{
				$this->finish_order( $response['description'], $this->get_order_status_id( WC_Gateway_Paylane::ORDER_STATUS_FAILED ), $error_message );
				$this->print_error_page( $error_message );
			}
		}


		function response_check_3ds()
		{

			$order_id = ( isset( $_POST['description'] ) ) ? intval( $_POST['description'] ) : intval( $_GET['description'] );
			$type = get_post_meta( $order_id, 'paylane-type', true );
			$redirect_version = $this->get_option( 'redirect_version_' . $type );

			if ( $redirect_version == 'POST' )
			{
				$response['status']      = $_POST['status'];
				$response['description'] = $_POST['description'];
				$response['amount']      = $_POST['amount'];
				$response['currency']    = $_POST['currency'];
				$response['hash']        = $_POST['hash'];
				$response['id']          = '';
				$error_message           = '';

				if( $response['status'] !== 'ERROR' )
				{
					$response['id'] = $_POST['id_3dsecure_auth'];
				}
				else
				{
					$error_message .= "Error: ".$response['status'];
				}
			}
			else
			{
				$response['status']      = $_GET['status'];
				$response['description'] = $_GET['description'];
				$response['amount']      = $_GET['amount'];
				$response['currency']    = $_GET['currency'];
				$response['hash']        = $_GET['hash'];
				$response['id']          = '';
				$error_message           = "";

				if ( $response['status'] !== 'ERROR' )
				{
					$response['id'] = $_GET['id_3dsecure_auth'];
				}
				else
				{
					$error_message .= "Error: ".$response['status'];
				}
			}

			$hash_data = array(
				'status'      => $response['status'],
				'description' => $response['description'],
				'amount'      => $response['amount'],
				'id'          => $response['id']
			);
			$calc_hash = $this->hash($hash_data, 'response', $type, $response['currency']);

			if ( $calc_hash !== $response['hash'] )
			{
				$error_message .= __( 'Error: wrong hash', 'wc-gateway-paylane' );
			}

			if ( $response['status'] === 'ERROR' || $error_message != "" )
			{
				$error_message .= __( 'Error, 3-D auth transaction declined', 'wc-gateway-paylane' );
			}
			else
			{
				require_once __DIR__ . '/includes/paylane-rest.php';
				$client = new PayLaneRestClient( $this->get_option('login_PayLane'), $this->get_option('password_PayLane') );
				@session_start();

				try
				{
					$status = $client->saleBy3DSecureAuthorization( array( 'id_3dsecure_auth' => $response['id'] ) );
				}
				catch(Exception $e)
				{
					var_dump($e->getMessage()); die;
				}

				if ( $client->isSuccess() )
				{
					$this->set_order_paylane_id($response['description'], $status['id_sale']);
				}
				else
				{
					$error_message .= __( 'Error 3-D Secure payment', 'wc-gateway-paylane' );
				}
			}

			if ( $error_message == "" )
			{
				$hash_data = array("status" => $response['status'], "description" => $response['description'], "amount" => $response['amount'], "id" => $response['id']);
				$hash = $this->hash($hash_data, "response", $type, $response['currency']);

				if ( $hash == $response['hash'] )
				{
					if ( $response['status'] != 'ERROR' )
					{
						if( $response['status'] == 'PENDING' )
						{
							$this->finish_order(
								$response['description'],
								$this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_PENDING),
								__("Payment awaiting confirmation", 'wc-gateway-paylane')
							);
						}
						else
						{
							@session_start();
							$this->finish_order( $response['description'], $this->get_option('status_successful_order') );
						}
					}
				}
				else
				{
					$this->finish_order(
						$response['description'],
						$this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED),
						$error_message
					);
					$this->print_error_page( $error_message );
				}
			}
			else
			{
				$this->finish_order(
					$response['description'], $this->get_order_status_id(WC_Gateway_Paylane::ORDER_STATUS_FAILED),
					$error_message
				);
				$this->print_error_page( $error_message );
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
			if (is_null($currency))
			{
				$currency = get_woocommerce_currency();
			}

			$array = array();

			if( $data_type == 'request' )
			{
				$array = array(
					$this->get_option( 'hash_salt' ),
					$hash_data['order_id'],
					$hash_data['total'],
					$currency,
					'S'
				);
			}

			if ( $data_type == 'response' )
			{
				$array = array(
					$this->get_option( 'hash_salt' ),
					$hash_data['status'],
					$hash_data['description'],
					$hash_data['amount'],
					$currency,
					$hash_data['id']
				);
			}

			$hash = sha1( implode( '|', $array ) );
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
			$url   = "https://secure.paylane.com/order/cart.html";
			$type  = get_post_meta($order_id, 'paylane-type', true);

			switch (get_locale())
			{
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

			$address          = $order->get_address('billing');
			$customer_name    = $address['first_name'] . ' ' . $address['last_name'];
			$customer_address = $address['address_1'] . ' ' . $address['address_2'];
			$hash_data        = array('order_id' => $order_id, "total" => $order->get_total());

			$form = '
            <form action="'.$url.'" method="'.$this->get_option('paylane_redirect_version').'" id="paylane_form" name="paylane_form">
                <input type="hidden" name="customer_name" value="'.$customer_name.'">
                <input type="hidden" name="customer_email" value="'.$address['email'].'">
                <input type="hidden" name="customer_address" value="'.$customer_address.'">
                <input type="hidden" name="customer_zip" value="'.$address['postcode'].'">	
                <input type="hidden" name="customer_city" value="'.$address['city'].'">
                <input type="hidden" name="amount" value="'.$order->get_total().'">
                <input type="hidden" name="currency" value="'.get_woocommerce_currency().'">
                <input type="hidden" name="merchant_id" value="'.$this->merchant_id.'">
                <input type="hidden" name="description" value="'.$order_id.'">
                <input type="hidden" name="transaction_description" value='.__('Order no.: ','wc-gateway-paylane').$order_id.'">
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

		/**
		 * Add status checking possibility for SEPA DIRECT DEBIT at order page at admin panel
		 *
		 * @param $actions
		 * @return mixed
		 */
		function add_order_meta_box_actions( $actions )
		{
			$actions['directdebit_check'] = __( 'Check Direct Debit transaction status (SEPA)', 'wc-gateway-paylane' );
			$actions['subscription_renew'] = __( 'Test subscription payment', 'wc-gateway-paylane' );
			return $actions;
		}

		/**
		 * Execute action hooked to 'directdebit_check'
		 *
		 * @param WC_Order $order
		 */
		function check_direct_debit($order)
		{
			$type = get_post_meta( $order->id, 'paylane-type', true );
			$id   = get_post_meta( $order->id, 'paylane-id-sale', true );

			if ( $type == 'sepa' )
			{
				require_once __DIR__. '/includes/paylane-rest.php';

				$client = new PayLaneRestClient(
					$this->get_option( 'login_PayLane' ),
					$this->get_option( 'password_PayLane' )
				);

				$info = $client->getSaleInfo( array( 'id_sale' => $id ) );

				$order->add_order_note( __( 'PayLane transaction status: ', 'wc-gateway-paylane' ) . $info['status'] );
			}
		}

		/**
		 * @param $amount_to_charge
		 * @param $order
		 */
		function scheduled_subscription_payment( $amount_to_charge, $order )
		{
			/**
			 * 1) Get PayLane sale ID from order
			 * 2) Make payment request using payment method and data from order
			 * 3) Handle according to results
			 */
			global $woocommerce, $post;

			$parent_order_id = WC_Subscriptions_Renewal_Order::get_parent_order_id( $order->id );
			$parent_order    = new WC_Order( $parent_order_id );

			$params = array(
				'id_sale'     => get_post_meta( $parent_order->id, 'paylane-id-sale', true ),
				'amount'      => $amount_to_charge,
				'currency'    => get_woocommerce_currency(),
				'description' => $order->id
			);

			$paymentType = get_post_meta( $parent_order->id, 'paylane-id-sale', true );

			if ($paymentType !== 'paypal')
			{
				require_once __DIR__ . '/includes/paylane-rest.php';

				$client = new PayLaneRestClient($this->get_option('login_PayLane'), $this->get_option('password_PayLane'));
				$result = $client->resaleBySale($params);

				if ( $client->isSuccess() )
				{
					set_order_paylane_id($order->id, $result['id_sale']);
					WC_Subscriptions_Manager::process_subscription_payments_on_order( $parent_order );
				}
				else
				{
					WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $parent_order );
				}
			}
			else
			{
				WC_Subscriptions_Manager::process_subscription_payments_on_order( $parent_order );
			}
		}

		/**
		 * Last function which finish orders and set proper status to them
		 *
		 * @param      $order_id
		 * @param      $state
		 * @param null $message
		 */
		public function finish_order( $order_id, $state, $message = null )
		{
			$order           = new WC_Order($order_id);
			$paylane_code    = get_post_meta($order_id, 'paylane-type', true);
			$paylane_methods = self::$paylane_methods;
			$payment_label   = $paylane_methods[$paylane_code];

			if ( empty( $state ) )
			{
				$state = $this->get_option( 'status_' . $paylane_code );
			}

			if ( $state >= 6 )
			{
				$order_status_message = sprintf(
					__('Transaction failed with reason: %s.', 'wc-gateway-paylane'),
					$message
				);

				$order->update_status('failed', $order_status_message);
				return false;
			}

			$order_status_message = sprintf(
				__('Transaction confirmed, payment method: %s. %s', 'wc-gateway-paylane'),
				$payment_label,
				$message
			);

			switch ( $state )
			{
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


			$order->update_status( $order_status, $order_status_message );
			$return_url = $order->get_checkout_order_received_url();
			wp_redirect( $return_url );
			exit;
		}

		/**
		 * Handle manual refund through PayLane in Woocomerce
		 *
		 * @param        $order_id
		 * @param null   $amount
		 * @param string $reason
		 * @return bool
		 */
		public function process_refund( $order_id, $amount = null, $reason = '' )
		{
			$order = wc_get_order( $order_id );

			$refund_params = array(
				'id_sale' => get_post_meta($order_id, 'paylane-id-sale', true),
				'amount'  => $amount,
				'reason'  => $reason,
			);

			include_once ('includes/paylane-rest.php');
			$type = get_post_meta($order_id, 'paylane-type', true);
			$client = new PayLaneRestClient(get_option('login_PayLane'), get_option('password_PayLane'));
			try {
				$status = $client->refund($refund_params);
			} catch (Exception $e) {

			}

			if ($client->isSuccess()){
				$order->add_order_note('Refund completed. ID: '.$status['id_refund']);
				return true;
			}else{
				$error_message = null;
				if(isset($status['error']['id_error'])) $error_message .= __("Error ID:",'wc-gateway-paylane')." {$status['error']['id_error']} <br>";
				if(isset($status['error']['error_number'])) $error_message .= __("Error number:",'wc-gateway-paylane')." {$status['error']['error_number']} <br>";
				if(isset($status['error']['error_description'])) $error_message .= __("Error description:",'wc-gateway-paylane')." {$status['error']['error_description']}";
				$order->add_order_note( __('Refund Failed:','wc-gateway-paylane').' ' . $error_message );
				return false;
			}
		}

		function set_order_paylane_id($order_id, $id){
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
			$this->form_fields = include( __DIR__ . '/includes/paylane-settings.php' );
		}

		/**
		 * Adds Paylane payment gateway to the list of installed gateways
		 *
		 * @param $methods
		 * @return array
		 */
		public function add_paylane_gateway( $methods )
		{
			$methods[] = 'WC_Gateway_Paylane';

			return $methods;
		}

		public function handle_subscriptions_hooks()
		{
			/**
			 * WooCommerce Subscriptions specific hooks
			 */
			if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) )
			{
				add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2);
			}
		}

		/**
		 * @param $methods
		 * @return array
		 */
		public function enable_secure_form_integration( $methods )
		{
			$methods[] = 'Paylane_Gateway_Secure';
			return $methods;
		}

		/**
		 * @param $methods
		 * @return array
		 */
		public function enable_api_integration( $methods )
		{
			$methods[] = 'Paylane_Gateway_CreditCard';
			$methods[] = 'Paylane_Gateway_BankTransfer';
			$methods[] = 'Paylane_Gateway_Sepa';
			$methods[] = 'Paylane_Gateway_Sofort';
			$methods[] = 'Paylane_Gateway_Paypal';
			$methods[] = 'Paylane_Gateway_Ideal';

			return $methods;
		}

		/**
		 * @param $available_gateways
		 * @return mixed
		 */
		public function disable_paylane_main_gateway( $available_gateways )
		{
			if (isset($available_gateways['paylane']))
			{
				unset($available_gateways['paylane']);
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
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_api_wc_gateway_paylane', array( $this, 'data_handler' ) );
			add_action( 'woocommerce_api_wc_gateway_paylane_3ds', array( $this, 'response_check_3ds' ) );
			add_action( 'woocommerce_order_actions', array( $this, 'add_order_meta_box_actions' ) );
			add_action( 'woocommerce_order_action_directdebit_check', array( $this, 'check_direct_debit' ) );
			add_action( 'admin_init', array( $this, 'handle_subscriptions_hooks' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'paylane_payment_style' ) );
		}

		/**
		 * Add filters
		 *
		 * @return void
		 */
		private function add_filters()
		{
			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_paylane_gateway' ) );
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'disable_paylane_main_gateway' ) );
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
			add_filter( 'woocommerce_payment_gateways', array( $this, 'enable_secure_form_integration' ) );
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

			add_filter( 'woocommerce_payment_gateways', array( $this, 'enable_api_integration') );
		}

		/**
		 * @param $order_status_string
		 * @return mixed|null
		 */
		private function get_order_status_id($order_status_string)
		{
			return ( isset($this->order_status_to_id[$order_status_string]) )
				? $this->order_status_to_id[$order_status_string]
				: null;
		}

		/**
		 * @param $error_message
		 */
		private function print_error_page( $error_message )
		{
			global $woocommerce;

			wc_add_notice($error_message, 'paylane_error');

			$redirectUrl = add_query_arg( 'paylane-api-error', '1', $woocommerce->cart->get_checkout_url( ) );
			wp_redirect($redirectUrl);
			exit;
		}
	}

	WC_Gateway_Paylane::instance()->init();
}


add_action( 'plugins_loaded', 'init_paylane' );
