<?php
/**
 * Plugin Name:       WooCommerce Cash USA Gateway
 * Description:       Accept domestic cash and money order payments for WooCommerce orders.
 * Author:            Incloo
 * Version:           0.1.0
 * Requires Plugins:  woocommerce
 * Text Domain:       wc-cash-usa
 * Domain Path:       /languages
 */

defined('ABSPATH') || exit;

define('WC_CASH_USA_GATEWAY_ID', 'cash_usa');
define('WC_CASH_USA_VERSION', '0.1.0');
define('WC_CASH_USA_PLUGIN_FILE', __FILE__);
define('WC_CASH_USA_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Register the plugin text domain for translations.
 */
function wc_cash_usa_load_textdomain(): void
{
    load_plugin_textdomain('wc-cash-usa', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'wc_cash_usa_load_textdomain');

/**
 * Load the gateway once WooCommerce is available.
 */
function wc_cash_usa_plugins_loaded(): void
{
    if (! class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once WC_CASH_USA_PLUGIN_PATH . 'includes/class-wc-gateway-cash-usa.php';
}
add_action('plugins_loaded', 'wc_cash_usa_plugins_loaded');

/**
 * Register the gateway with WooCommerce.
 *
 * @param string[] $gateways
 * @return string[]
 */
function wc_cash_usa_register_gateway(array $gateways): array
{
    $gateways[] = 'WC_Gateway_Cash_USA';

    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wc_cash_usa_register_gateway');

/**
 * Register WooCommerce Blocks support when available.
 */
function wc_cash_usa_register_blocks_support(): void
{
    if (! class_exists('Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType')) {
        return;
    }

    require_once WC_CASH_USA_PLUGIN_PATH . 'includes/class-wc-gateway-cash-usa-blocks.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        static function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry): void {
            $payment_method_registry->register(new WC_Gateway_Cash_USA_Blocks());
        }
    );
}
add_action('woocommerce_blocks_loaded', 'wc_cash_usa_register_blocks_support');
