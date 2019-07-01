jQuery(window).on('load', function() {
	var paylaneFieldHidden = (jQuery("#woocommerce_paylane_connection_mode").val() === 'SecureForm');

	function paylaneFieldsHandle() {
		if (paylaneFieldHidden) {
			jQuery('#woocommerce_paylane_credit_card').hide();
			jQuery('#woocommerce_paylane_credit_card').next().hide();

			jQuery('#woocommerce_paylane_transfer').hide();
			jQuery('#woocommerce_paylane_transfer').next().hide();

			jQuery('#woocommerce_paylane_sepa').hide();
			jQuery('#woocommerce_paylane_sepa').next().hide();

			jQuery('#woocommerce_paylane_sofort').hide();
			jQuery('#woocommerce_paylane_sofort').next().hide();

			// jQuery("#woocommerce_paylane_login_PayLane").parents('tr[valign="top"]').hide();
			// jQuery("#woocommerce_paylane_password_PayLane").parents('tr[valign="top"]').hide();
			// jQuery('#woocommerce_paylane_api_key_val').parents('tr[valign="top"]').hide();

			jQuery('#woocommerce_paylane_paypal').hide();
			jQuery('#woocommerce_paylane_paypal').next().hide();

			jQuery('#woocommerce_paylane_ideal').hide();
			jQuery('#woocommerce_paylane_ideal').next().hide();

			jQuery('#woocommerce_paylane_secure_form').show();
			jQuery('#woocommerce_paylane_secure_form').next().show();
			jQuery('#woocommerce_paylane_secure_form').next().next().show();

			jQuery('#woocommerce_paylane_apple_pay_style').parents('table.form-table:first').hide();

		}
		else {

			jQuery('#woocommerce_paylane_credit_card').show();
			jQuery('#woocommerce_paylane_credit_card').next().show();

			jQuery('#woocommerce_paylane_transfer').show();
			jQuery('#woocommerce_paylane_transfer').next().show();

			jQuery('#woocommerce_paylane_sepa').show();
			jQuery('#woocommerce_paylane_sepa').next().show();

			jQuery('#woocommerce_paylane_sofort').show();
			jQuery('#woocommerce_paylane_sofort').next().show();

			jQuery('#woocommerce_paylane_paypal').show();
			jQuery('#woocommerce_paylane_paypal').next().show();

			jQuery('#woocommerce_paylane_ideal').show();
			jQuery('#woocommerce_paylane_ideal').next().show();

			// jQuery("#woocommerce_paylane_login_PayLane").parents('tr[valign="top"]').show();
			// jQuery("#woocommerce_paylane_password_PayLane").parents('tr[valign="top"]').show();
			// jQuery('#woocommerce_paylane_api_key_val').parents('tr[valign="top"]').show();


			jQuery('#woocommerce_paylane_secure_form').hide();
			jQuery('#woocommerce_paylane_secure_form').next().hide();
			jQuery('#woocommerce_paylane_secure_form').next().next().hide();

			jQuery('#woocommerce_paylane_apple_pay_style').parents('table.form-table:first').show();
		}

	}

	paylaneFieldsHandle();

	jQuery("#woocommerce_paylane_connection_mode, #s2id_woocommerce_paylane_connection_mode, #select2-woocommerce_paylane_connection_mode-container").change(function() {
		paylaneFieldHidden = !paylaneFieldHidden;
		paylaneFieldsHandle();
	});
});