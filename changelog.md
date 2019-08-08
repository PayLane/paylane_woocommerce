Changelog by Mind Joker Marcin Musiak <marcin@musiak.pl>
=========================================================

= 2.1.3 - 2019-07-24 = 
* Improve display payment methods names 
* Add option to display/hide payment methods logo 
* Add input mask to fields: credit card number, credit card valid date, credit card CVV/CVC 
* Improve display choosed bank type in bank transfer method

= 2.1.2 - 2019-07-15 =
* Support older versions of WooCommerce
* Add switches for payment methods in older versions of WooCommerce
* Fix Redirect Method. Now correctly support POST method
* Fix set order status when custom status was added in WooCommerce
* Automatically set chosen status instead of note on handle payment notification from provider
* Improve rules of payment notifications


= 2.1.1 - 2019-07-09 =
* Fix the loading plugin settings from the admin panel
* Fix the non-existence of the token on the checkout page
* Fix the possible conflicts with other JS plugins on the checkout page on the order submit action
* Fix the PayLane error page -> currently the plugin display nice wordpress ready notice
* Clean not used methods
* Add payment type prefix in the WooCommerce admin section (e.g. PayLane: SOFORT)
* Add payment type prefix in the order note after notification handle (e.g. PayLane: Transaction complete)
* Add notification flags to the order (last valid type + timestamp) and prevent change "final" types (S and R) when hosting server will have some performance issues
* Unify the order description on the ApplePay method


= 2.1.0 - 2019-06-21 =
* Export declaration PAYLANE_VALIDATION_MESSAGES
    PAYLANE_VALIDATION_MESSAGES.ts
* Import constant to the file
    paylane-woocommerce.ts
* Delete declaration PAYLANE_VALIDATION_MESSAGES
    paylane-woocommerce.ts
* Fix static method instance()
    woocommerce-gateway-paylane.php
* Fix method name.
    Before: Paylane -
    Now: Paylane - Credit Card
    Base.php
* Translation update
* REST API update by ApplePay method
* Add ApplePay in ADMIN section (cert, style, language)
* Add new function - createing file with cert without loging into FTP (Unless WP don't give permission)
* Add ApplePay in WooCoommerce Payments section 
* Add ApplePay button in WooCommerce Checkout section (only if ApplePay is enable)
* Add many currencies to ApplePay method (tested with WPML plugin)
