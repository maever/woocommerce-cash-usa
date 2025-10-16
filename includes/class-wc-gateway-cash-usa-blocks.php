<?php
/**
 * WooCommerce Blocks integration for the Cash USA gateway.
 */

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

defined('ABSPATH') || exit;

/**
 * Registers the Cash USA payment method with the block based checkout.
 */
class WC_Gateway_Cash_USA_Blocks extends AbstractPaymentMethodType
{
    /**
     * Ensure the Blocks integration uses the same identifier as the core gateway.
     *
     * @var string
     */
    protected $name = WC_CASH_USA_GATEWAY_ID;

    /**
     * Load the stored gateway settings.
     */
    public function initialize()
    {
        $settings = get_option('woocommerce_' . $this->name . '_settings', []);

        if (! is_array($settings)) {
            $settings = [];
        }

        $this->settings = array_merge(
            [
                'enabled'              => 'no',
                'title'                => __('Cash (USA Mail)', 'wc-cash-usa'),
                'description'          => __('Mail your cash or money order within the United States.', 'wc-cash-usa'),
                'checkout_instructions' => '',
            ],
            $settings
        );
    }

    /**
     * Determine whether the payment method should load for block checkout.
     */
    public function is_active(): bool
    {
        return 'yes' === $this->get_setting('enabled', 'no');
    }

    /**
     * Register the JavaScript implementation for the Blocks checkout UI.
     *
     * @return string[]
     */
    public function get_payment_method_script_handles(): array
    {
        $handle = 'wc-cash-usa-blocks';

        if (! wp_script_is($handle, 'registered')) {
            $script_url = plugins_url('assets/js/cash-usa-gateway-blocks.js', WC_CASH_USA_PLUGIN_FILE);

            wp_register_script(
                $handle,
                $script_url,
                [
                    'wc-blocks-registry',
                    'wc-settings',
                    'wp-element',
                    'wp-i18n',
                ],
                WC_CASH_USA_VERSION,
                true
            );

            wp_set_script_translations($handle, 'wc-cash-usa');
        }

        return [$handle];
    }

    /**
     * Provide settings to the JavaScript component.
     *
     * @return array<string, mixed>
     */
    public function get_payment_method_data(): array
    {
        $checkout_instructions = $this->get_setting('checkout_instructions', '');

        return [
            'title'           => $this->get_setting('title'),
            'description'     => wpautop(wp_kses_post($this->get_setting('description', ''))),
            'checkoutMessage' => wpautop(wp_kses_post($checkout_instructions)),
            'supports'        => [
                'features' => ['products'],
            ],
        ];
    }
}
