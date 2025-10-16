<?php
/**
 * Cash USA payment gateway for WooCommerce.
 */

defined('ABSPATH') || exit;

/**
 * Provides a manual cash payment gateway tailored for domestic U.S. mail payments.
 */
class WC_Gateway_Cash_USA extends WC_Payment_Gateway
{
    /**
     * Instructions shown on checkout.
     *
     * @var string
     */
    protected $checkout_instructions = '';

    /**
     * Instructions shown on thank-you page and in emails.
     *
     * @var string
     */
    protected $email_instructions = '';

    /**
     * Bootstraps the gateway.
     */
    public function __construct()
    {
        $this->id                 = WC_CASH_USA_GATEWAY_ID;
        $this->method_title       = __('Cash (USA Mail)', 'wc-cash-usa');
        $this->method_description = __('Allow customers to complete their WooCommerce orders by mailing cash or a money order within the United States.', 'wc-cash-usa');
        $this->has_fields         = true;
        $this->supports           = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title                = $this->get_option('title');
        $this->description          = $this->get_option('description');
        $this->checkout_instructions = $this->get_option('checkout_instructions');
        $this->email_instructions    = $this->get_option('email_instructions');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_thankyou_' . $this->id, [$this, 'thankyou_page']);
        add_action('woocommerce_email_before_order_table', [$this, 'email_instructions'], 10, 3);
        add_action('woocommerce_order_details_after_order_table', [$this, 'order_details_instructions']);
    }

    /**
     * Define the admin settings available for the gateway.
     */
    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Enable/Disable', 'wc-cash-usa'),
                'type'    => 'checkbox',
                'label'   => __('Enable Cash USA payments', 'wc-cash-usa'),
                'default' => 'no',
            ],
            'title' => [
                'title'       => __('Title', 'wc-cash-usa'),
                'type'        => 'text',
                'description' => __('Controls the payment method title customers see during checkout.', 'wc-cash-usa'),
                'default'     => __('Cash (USA Mail)', 'wc-cash-usa'),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Short description', 'wc-cash-usa'),
                'type'        => 'textarea',
                'description' => __('Shown beside the payment option during checkout. Supports basic HTML and preserves line breaks.', 'wc-cash-usa'),
                'default'     => __('Mail your cash or money order within the United States.', 'wc-cash-usa'),
                'desc_tip'    => true,
            ],
            'checkout_instructions' => [
                'title'       => __('Checkout instructions', 'wc-cash-usa'),
                'type'        => 'textarea',
                'description' => __('Detailed instructions displayed under the payment method on checkout. Supports basic HTML and multiline content.', 'wc-cash-usa'),
                'default'     => '',
                'css'         => 'height: 12em;',
            ],
            'email_instructions' => [
                'title'       => __('Thank you & email instructions', 'wc-cash-usa'),
                'type'        => 'textarea',
                'description' => __('Shown on the order received page and added to customer order emails. Supports basic HTML and multiline content.', 'wc-cash-usa'),
                'default'     => '',
                'css'         => 'height: 12em;',
            ],
        ];
    }

    /**
     * Present content on the checkout form.
     */
    public function payment_fields(): void
    {
        if ($this->description) {
            echo wp_kses_post(wpautop($this->description));
        }

        if ($this->checkout_instructions) {
            echo '<div class="wc-cash-usa-instructions">' . wpautop(wp_kses_post($this->checkout_instructions)) . '</div>';
        }
    }

    /**
     * Process the payment and put the order on hold pending receipt of funds.
     *
     * @param int $order_id
     * @return array<string, string>
     */
    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);

        if (! $order instanceof WC_Order) {
            return [
                'result'   => 'failure',
                'redirect' => '',
            ];
        }

        $order->update_status('on-hold', __('Awaiting mailed cash or money order payment.', 'wc-cash-usa'));
        $order->add_order_note(__('Customer opted to pay with mailed cash or money order. Awaiting arrival of funds.', 'wc-cash-usa'));

        wc_reduce_stock_levels($order_id);

        $cart = WC()->cart;

        if ($cart && is_callable([$cart, 'empty_cart'])) {
            $cart->empty_cart();
        }

        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }

    /**
     * Display instructions on the thank you page.
     *
     * @param int $order_id
     */
    public function thankyou_page($order_id): void
    {
        if (empty($this->email_instructions)) {
            return;
        }

        echo '<section class="wc-cash-usa-thankyou">';
        echo wpautop(wp_kses_post($this->email_instructions));
        echo '</section>';
    }

    /**
     * Display instructions when customers review their order in My Account.
     *
     * @param WC_Order $order
     */
    public function order_details_instructions($order): void
    {
        if (! $order instanceof WC_Order || $order->get_payment_method() !== $this->id || empty($this->email_instructions)) {
            return;
        }

        echo '<section class="wc-cash-usa-order-details">';
        echo wpautop(wp_kses_post($this->email_instructions));
        echo '</section>';
    }

    /**
     * Append instructions to customer emails.
     *
     * @param WC_Order $order
     * @param bool     $sent_to_admin
     * @param bool     $plain_text
     */
    public function email_instructions($order, $sent_to_admin, $plain_text = false): void
    {
        if (! $order instanceof WC_Order || $sent_to_admin || $order->get_payment_method() !== $this->id || empty($this->email_instructions)) {
            return;
        }

        $instructions = wp_kses_post($this->email_instructions);

        if ($plain_text) {
            $plain = preg_replace('#<br\s*/?>#i', PHP_EOL, $instructions);
            $plain = wp_specialchars_decode(strip_tags($plain));
            $plain = preg_replace("/\n{3,}/", PHP_EOL . PHP_EOL, $plain);

            echo PHP_EOL . trim($plain) . PHP_EOL;
            return;
        }

        echo '<section class="wc-cash-usa-email-instructions">';
        echo wpautop($instructions);
        echo '</section>';
    }
}
