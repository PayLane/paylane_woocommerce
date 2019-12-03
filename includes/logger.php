<?php
if (!defined('ABSPATH')) {
    exit;
}

class WCPL_Logger
{
    public static $logger;
    const LOG_FILENAME = 'woocommerce-gateway-paylane';

    public static function log($message, $type = null)
    {
        if (!class_exists('WC_Logger')) {
            return;
        }

        if (empty(self::$logger)) {
            if (version_compare(WC_VERSION, '3.0', '<')) {
                self::$logger = new WC_Logger();
            } else {
                self::$logger = wc_get_logger();
            }
        }

        $settings = get_option('woocommerce_paylane_settings');

        if (empty($settings) || isset($settings['logging']) && $settings['logging'] !== 'yes') {
            return;
        }

        if(is_null($type)){
            $type = 'info';
        }

        $txt = "\n" . '======= ['.strtoupper($type).'] =======' . "\n" . $message . "\n" . '======= End Log =======' . "\n\n";

        if (version_compare(WC_VERSION, '3.0', '<')) {
            self::$logger->add(self::LOG_FILENAME, $txt);
        } else {
            self::$logger->debug($txt, array('source' => self::LOG_FILENAME));
        }
    }

    public static function secure(array $data){
        // if(isset($data['hash'])){
        //     $data['hash'] = '===SECRET===';
        // }

        return $data;
    }

    public static function jsonifySecure(array $data){
        $arr = self::secure($data);

        return json_encode($arr);
    }
}
