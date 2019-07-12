<?php
$errors = wc_get_notices('paylane_error');
global $woocommerce;
$checkout_url = wc_get_checkout_url();

$body = '';

$body .= '<div style="text-align: center;margin-top:20px;"><a href="' . $checkout_url . '">Wróć do koszyka</a></div>';

$body .= '<script>';

foreach ($errors as $error)
{
  $body .= 'console.error("' . $error . '");';
}

$body .= '</script>';


wc_clear_notices();

return $body;
