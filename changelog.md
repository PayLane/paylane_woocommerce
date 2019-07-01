Changelog by Mind Joker Marcin Musiak <marcin@musiak.pl>
=========================================================

[2.1.0]
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
