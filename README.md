# WooCommerce Cash USA Gateway

A WooCommerce payment gateway that lets customers pay by mailing cash or a money order within the United States. The gateway provides long-form instructional fields that preserve newlines so shop owners can give detailed step-by-step directions.

## Installation

1. Download or clone this repository into your WordPress installation's `wp-content/plugins/` directory.
2. From the WordPress admin, go to **Plugins → Installed Plugins** and activate **WooCommerce Cash USA Gateway**.
3. Navigate to **WooCommerce → Settings → Payments** and enable **Cash (USA Mail)**.
4. Update the title, descriptions, and instructional text with your own payment directions. The checkout and email instruction fields accept multiple lines and basic HTML formatting.

## Features

- Places orders on hold until mailed payment is received.
- Displays detailed instructions on the checkout form, order received page, and customer emails.
- Supports newline and paragraph formatting in instructional text using the built-in textarea settings.
- Compatible with both shortcode and block-based WooCommerce checkout experiences.

## Development

The plugin structure mirrors WooCommerce's recommended patterns and includes an integration for WooCommerce Blocks.

- `woocommerce-cash-usa.php` – Main plugin bootstrap file.
- `includes/class-wc-gateway-cash-usa.php` – Core payment gateway implementation.
- `includes/class-wc-gateway-cash-usa-blocks.php` – Blocks checkout integration.
- `assets/js/cash-usa-gateway-blocks.js` – JavaScript entry point for the block-based checkout UI.

No build tooling is required for the current JavaScript file. Update the script directly if you need custom behavior in the block checkout.
