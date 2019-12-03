== Changelog ==

= 2.2.3 - 2019-11-18 =
* Fix update status by Sofort notifications

= 2.2.2 - 2019-09-19 =
* Rebranding: PayLane to PeP Online
* Update

= 2.2.1 - 2019-09-19 =
* Refactoring

= 2.2.0 - 2019-08-18 =
* Support WooCommerce Subscriptions with: Credit Card, SEPA, PayPal and Apple Pay

= 2.1.4 - 2019-08-07 =
* Fix the 3-D Secure flow when 3DS is required
* Add descriptions in the admin section
* Improved the non successful payments. From now client will be able to retry it using different payment method or different payment data
* Add debug logs feature
* Add new translations of payment errors

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
* Deklaracja exportu stałej PAYLANE_VALIDATION_MESSAGES
    PAYLANE_VALIDATION_MESSAGES.ts
* Zaimportowanie stałej do pliku, w którym jest wykorzystywana
    paylane-woocommerce.ts
* Usunięcie deklaracji globalnej PAYLANE_VALIDATION_MESSAGES
    paylane-woocommerce.ts
* Fix deklaracji metody statycznej instance()
    woocommerce-gateway-paylane.php
* Fix nazwy metody płatności po jej dokonaniu.
    Przed zmianą: Paylane -
    Po zmianie: Paylane - Credit Card
    Base.php
* Aktualizacja pliku tłumaczeń - wersja polska i angielska
* Aktualizacja klienta REST API o metodę ApplePay
* Dodanie sekcji konfiguracyjnej ApplePay w sekcji ADMIN (certyfikat, styl, język)
* Dodanie funkcji tworzenia pliku z certyfikatem bez konieczności logowania się poprzez FTP (chyba, że WP nie ma uprawnień to wyświetla się stosowny komunikat)
* Dodanie wyboru metody płatności ApplePay w sekcji WooCoommerce Płatnosci zgodnie ze wcześniejszym schematem wtyczki
* Dodanie przycisku ApplePay w sekcji WooCommerce Checkout (tylko, jeżeli ApplePay jest obsługiwany)
* Dodanie obsługi wielu walut dla ApplePay (testowano z popularną wtyczką WPML)