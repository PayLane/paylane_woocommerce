<?php if ( !defined( 'ABSPATH' ) ) exit;

/**'
 * Class Paylane_Gateway_Base
 */
abstract class Paylane_Gateway_Base extends WC_Payment_Gateway
{
	/**
	 * @var array
	 */
	public $settings = array();

	/**
	 * @var array
	 */
	public $countries = array();

	/**
	 * @var string
	 */
    protected $gateway_id = 'paylane-custom';

	/**
	 * @var string
	 */
    protected $design;

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

	/**
	 * Paylane_Gateway_Base constructor.
	 */
	public function __construct()
	{
		global $woocommerce;

		$this->id              = $this->gateway_id;
		$this->has_fields      = true;
		$this->method_title    = $this->getMethodTitle();
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

		$this->title               = $this->getMethodTitle();
		$this->description         = $this->get_option('description');
		$this->payment_method      = $this->get_option('payment_method');
		$this->secure_form         = $this->get_option('secure_form');
		$this->merchant_id         = $this->get_option('merchant_id');
		$this->fraud_check         = $this->get_option('fraud_check');
		$this->ds_check            = $this->get_option('3ds_check');
		$this->enable_notification = $this->get_option('notifications_enabled');
		$this->design              = $this->get_option('design', 'basic');
		$this->first_name          = '';
		$this->last_name           = '';

		$this->method_description  = __(
			sprintf(
				'All settings can be adjusted %s',
				'<a href=" ' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paylane' ) . '">' . __('here') . '</a>'
			),
			'wc-gateway-paylane'
		);

		add_filter('payment_fields', array($this, 'payment_fields'));

		$this->init_settings();
		$this->settings = array_merge( get_option('woocommerce_paylane_settings'), $this->settings );
	}

	/**
	 * Init settings for gateways.
	 */
	public function init_settings() {
		parent::init_settings();
		$this->enabled  = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
	}

	/**
	 * @param      $key
	 * @param null $empty_value
	 * @return mixed|null
	 */
	public function get_option($key, $empty_value = null)
    {
        if (isset($this->settings[$key]))
        {
            return $this->settings[$key];
        }

        return $empty_value;
    }

	/**
	 * @return string
	 */
    protected function getMethodTitle()
    {
        return get_called_class();
    }

	/**
	 * @return string
	 */
	protected function getGatewayTitle()
	{
		return get_called_class();
	}

	/**
	 * @param string $version
	 * @return bool
	 */
	function woocommerce_version_check($version = '3.0')
	{
		if ( class_exists( 'WooCommerce' ) )
		{
			global $woocommerce;

			if ( version_compare( $woocommerce->version, $version, ">=" ) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return mixed
	 */
	public function get_icon()
	{
		$iconUrl = plugins_url('../assets/paylane.png', __FILE__);
		$iconHtml = '';
		$iconHtml .= '<img src="' . $iconUrl . '" class="paylane-payment-method-label-logo" alt="' . esc_attr__(
				'PayLane image', 'woocommerce'
			) . '">';

		return apply_filters( 'woocommerce_gateway_icon', $iconHtml, $this->id );
	}

	/**
	 * Show PayLane methods fields at checkout
	 */
	function payment_fields()
	{
        echo $this->prepare_paylane_form($this->form_name);
	}

	/**
	 * @return bool
	 */
	public function validate_fields()
	{
		$method = $this->get_method_by_class(get_called_class());

		switch ($method)
		{
			case WC_Gateway_Paylane::PAYMENT_METHOD_CREDIT_CARD:
				$errors = $this->validate_credit_card();
				break;

			case WC_Gateway_Paylane::PAYMENT_METHOD_SEPA:
				$errors = $this->validate_sepa();
				break;

			case WC_Gateway_Paylane::PAYMENT_METHOD_BANK_TRANSFER:
				$errors = $this->validate_transfer();
				break;
			default:
				$errors = array();
		}


		if ( ! empty($errors) )
		{
			foreach($errors as $error)
			{
				wc_add_notice($error, 'error');
			}

			return false;
		}

		return true;
	}

	/**
	 * Function which prepare data and parameters for gateway API and process it to communication function
	 *
	 * @param $order_id
	 * @return array
	 */
	public function process_payment($order_id)
    {
	    $method = $this->get_method_by_class(get_called_class());

	    if (!$method)
		{
			wc_add_notice(__('Unsupported payment method', 'wc-gateway-paylane'), 'error');
			return array( 'success' => false );
		}

		if ( ! $this->validate_fields() )
		{
			return array( 'success' => false );
		}

		global $woocommerce;

		$order = new WC_Order($order_id);
		$order->update_status('on-hold', __('Awaiting payment confirmation', 'wc-gateway-paylane'));

		update_post_meta($order_id, '_payment_method_title', 'Paylane - ' . self::$paylane_methods[$_POST['paylane_payment_method']]);

		if ($this->woocommerce_version_check())
		{
			wc_reduce_stock_levels($order_id);
		}
		else
		{
			$order->reduce_order_stock();
		}

		if($method != "secure_form")
		{
			if($method === 'paypal')
			{
				$data = array(
					'sale'     => array(
						'amount'      => $order->get_total(),
						'currency'    => get_woocommerce_currency(),
						'description' => $order_id
					),
					'back_url'  => $this->notify_link,
				);
			}
			else
			{
				$address          = $order->get_address('billing');
				$customer_name    = $address['first_name'] . ' ' . $address['last_name'];
				$customer_address = $address['address_1'] . ' ' . $address['address_2'];

				$data = array(
					'sale'     => array(
						'amount'      => $order->get_total(),
						'currency'    => get_woocommerce_currency(),
						'description' => $order_id
					),
					'customer' => array(
						'name'    => $customer_name,
						'email'   => $address['email'],
						'ip'      => WC_Geolocation::get_ip_address(),
						'address' => array (
							'street_house' => $customer_address,
							'city'         => $address['city'],
							'zip'          => $address['postcode'],
							'country_code' => $address['country']
						),
					)
				);

				switch($method)
				{
					case 'credit_card':
						$data['card'] = array(
							"token" => $_POST['payment_params_token']
						);
						$data['back_url'] = $this->notify_link_3ds;
						break;

					case 'sepa':
						$data['account'] = array(
							'account_holder'  => $_POST['sepa_account_holder'],
							'account_country' => $_POST['sepa_account_country'],
							'iban'            => $_POST['sepa_iban'],
							'bic'             => $_POST['sepa_bic'],
						);
						$data['account']['mandate_id'] = $order_id;
						break;

					case 'ideal':
						$data['back_url']  = $this->notify_link;
						$data['bank_code'] = $_POST['bank-code'];
						break;

					case 'transfer':
						$data['payment_type'] = $_POST['transfer_bank'];
						$data['back_url']     = $this->notify_link;
						break;

					case 'sofort':
						$data['back_url'] = $this->notify_link;
						break;
				}

			}

			@session_start();

			$_SESSION['paylane-data'] = $data;
			$_SESSION['paylane-type'] = $method;
		}

		$this->set_order_paylane_type($order_id, $method);
		return array(
			'result'   => 'success',
			'redirect' => add_query_arg(array('order_id' => $order_id, 'type' => $method), $this->notify_link)
		);
	}

	/**
	 * Set meta data required to process orders in gateway
	 *
	 * @param $order_id
	 * @param $type
	 * @return void
	 */
	private function set_order_paylane_type($order_id, $type)
	{
		update_post_meta($order_id, 'paylane-type', $type);
	}

	/**
	 * @param $order_id
	 * @param $id
	 * @return void
	 */
	private function set_order_paylane_id($order_id, $id)
	{
		update_post_meta($order_id, 'paylane-id-sale', $id);
	}

	/**
	 * @param $class
	 * @return null|string
	 */
	private function get_method_by_class($class)
    {
        switch ($class)
        {
            case 'Paylane_Gateway_BankTransfer':
                return 'transfer';
                break;

			case 'Paylane_Gateway_CreditCard':
				return 'credit_card';

				break;
			case 'Paylane_Gateway_Ideal':
				return 'ideal';
				break;

			case 'Paylane_Gateway_Paypal':
				return 'paypal';
				break;

			case 'Paylane_Gateway_Secure':
				return 'secure_form';
				break;

			case 'Paylane_Gateway_Sepa':
				return 'sepa';
				break;

			case 'Paylane_Gateway_Sofort':
				return 'sofort';
				break;

			// Unsupported payment method
			default:
				return null;
        }
    }

	/**
	 * @param $data
	 * @param $token
	 * @param $communication_id
	 */
	function handle_notification($data, $token, $communication_id)
	{
		// check communication
		if($this->get_option('notifications_http_auth') == 'yes')
		{
			$this->checkBasicAuth();
		}

		if (empty($_POST['communication_id']))
		{
			die('Empty communication id');
		}
		/*
					// check if token correct
					if ((!empty($this->get_option('notifications_token')))&&($this->get_option('notifications_token') !== $_POST['token']))
						die('Wrong token');
		*/
		foreach ($data as $notification)
		{
			$id_sale  = $notification['id_sale'];
			$order_id = $notification['text'];
			$order    = new WC_Order($order_id);

			if ($notification['type'] === 'S')
			{
				$order->add_order_note( __( 'Transaction complete', 'wc-gateway-paylane' ) );
			}

			if ($notification['type'] === 'R')
			{
				$order->add_order_note( __( 'Refund complete', 'wc-gateway-paylane' ) );
			}

			if ($notification['type'] === 'RV')
			{
				$order->update_status( 'on-hold', __( 'Reversal received', 'wc-gateway-paylane' ) );
			}

			if ($notification['type'] === 'RRO')
			{
				$order->update_status( 'on-hold', __( 'Retrieval request / chargeback opened', 'wc-gateway-paylane' ) );
			}

			if ($notification['type'] === 'CAD')
			{
				$order->update_status( 'on-hold', __( 'Retrieval request / chargeback opened', 'wc-gateway-paylane' ) );
			}
		}

		die($_POST['communication_id']);
	}

	/**
	 * @param $method
	 * @return false|null|string
	 */
	public function prepare_paylane_form($method)
	{
		$form = null;

		switch($method)
		{
			case "secure_form":
				$form = $this->get_form('secure_form');
				break;

			case "credit_card":
				$form = $this->get_form('credit_card', array(
					'api_key' => $this->get_option('api_key_val')
				));
				break;

			case "transfer":
				$form = $this->get_form('forms/transfer');
				break;

			case "sepa":
				$form = $this->get_form('forms/sepa', array(
				    'countries' => $this->get_countries()
                ));
				break;

			case "sofort":
				$form = $this->get_form('forms/sofort');
				break;

			case "paypal":
				$form = $this->get_form('forms/paypal');
				break;

			case "ideal":
				$banks = Paylane_Woocommerce_Tools::getIdealBanks(
					$this->get_option('login_PayLane'),
					$this->get_option('password_PayLane')
				);

				$form = $this->get_form('forms/ideal', array(
					'banks' => $banks
				));
				break;
		}

		return $form;
	}

	/**
	 * @return array
	 */
	private function validate_credit_card()
	{
		$errors = array();

		if( ! isset($_POST['payment_params_token']) || empty($_POST['payment_params_token']) )
		{
			$errors[] = __('Card token is empty', 'wc-gateway-paylane');
		}
		else if ( ! preg_match('/^[a-z\d]{64}$/u', $_POST['payment_params_token']) )
		{
			$errors[] = __('Unrecognized or malformed token', 'wc-gateway-paylane');
		}

		return $errors;
	}

	/**
	 * @return array
	 */
	private function validate_sepa()
	{
		$errors = array();

		if (!$_POST["sepa_account_holder"])
		{
			$errors[] = __( 'Account holder name is empty', 'wc-gateway-paylane' );
		}

		if (!$_POST["sepa_account_country"])
		{
			$errors[] = __( 'Account country is empty', 'wc-gateway-paylane' );
		}

		if (!$_POST["sepa_iban"])
		{
			$errors[] = __( 'IBAN is empty', 'wc-gateway-paylane' );
		}

		if (!$_POST["sepa_bic"])
		{
			$errors[] = __( 'BIC is empty', 'wc-gateway-paylane' );
		}

		return $errors;
	}

	private function validate_transfer()
	{
		$errors = array();

		if ( ! isset( $_POST['transfer_bank'] ) || empty( $_POST['transfer_bank'] ) )
		{
			$errors[] = __( 'The bank was not chosen', 'wc-gateway-paylane' );
		}

		return $errors;
	}

	/**
	 * @return void
	 */
	protected function checkBasicAuth()
	{
		$user     = $this->get_option('notifications_login');
		$password = $this->get_option('notifications_password');

		if (
			!isset($_SERVER['PHP_AUTH_USER']) ||
			!isset($_SERVER['PHP_AUTH_PW']) ||
			$user != $_SERVER['PHP_AUTH_USER'] ||
			$password != $_SERVER['PHP_AUTH_PW']
		)
		{
			// authentication failed
			header("WWW-Authenticate: Basic realm=\"Secure Area\"");
			header("HTTP/1.0 401 Unauthorized");
			exit();
		}
	}

	/**
	 * @return array
	 */
	private function get_countries()
    {
        if (!isset($this->countries_obj))
		{
			$countries_obj = new WC_Countries();
			$this->countries = $countries_obj->__get('countries');
		}

		return $this->countries;
    }

	/**
	 * @param       $form_name
	 * @param array $vars
	 * @return false|null|string
	 */
	private function get_form($form_name, $vars = array())
	{
		extract($vars);

		$form = __DIR__ . '/../forms/' . $this->design . '/' . basename($form_name) . '.php';

		if ( ! file_exists($form))
		{
			return null;
		}

		ob_start();
		include $form;

		return ob_get_clean();
	}
}
