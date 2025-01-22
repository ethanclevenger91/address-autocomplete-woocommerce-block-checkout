<?php
/**
 * Plugin Name:       Address Autocomplete for Woocommerce Block Checkout
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       address-autocomplete-woocommerce-block-checkout
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_woocommerce_block_checkout_address_autocomplete_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_woocommerce_block_checkout_address_autocomplete_block_init' );

/**
 * Callback function to fetch the API key from the wp_options table.
 *
 * @return WP_REST_Response
 */
function fetch_checkout_autocomplete_api_key() {
    // Fetch the option from the database
    $settings = get_option('woocommerce_address-autocomplete-for-woocommerce-block-checkout_settings');

	// If the option doesn't exist, return an error response
    if (!$settings || !is_array($settings) || !$settings['api_key']) {
        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'API key not found',
			'settings_page' => AddressAutocompleteForBlocksIntegration::get_settings_url(),
        ), 404);
    }

    // Return the API key as a successful response
    return new WP_REST_Response(array(
        'success' => true,
        'api_key' => $settings['api_key'],
    ), 200);
}

add_action('plugins_loaded', function() {
	if ( class_exists( 'WC_Integration' ) ) {
		// Include our integration class.
		include_once 'address-autocomplete-woocommerce-block-checkout-settings.php';
		// Register the integration.
		add_filter( 'woocommerce_integrations', function($integrations) {
			$integrations[] = 'AddressAutocompleteForBlocksIntegration';
            return $integrations;
		} );

		/**
		 * Creates a REST API endpoint for our new option
		 * This allows the block to provide feedback to users.
		 **/
		add_action('rest_api_init', function () {
			register_rest_route('aawbc/v1', '/get-autocomplete-api-key', array(
				'methods' => 'GET',
				'callback' => 'fetch_checkout_autocomplete_api_key',
				'permission_callback' => '__return_true', // Allow public access - it gets rendered to the page anyway
			));
		});
		
	} else {
		// throw an admin error if you like
	}
});

// Add a new section to WooCommerce Shipping settings
// add_filter('woocommerce_get_sections_shipping', 'add_autocomplete_api_key_section');
// function add_autocomplete_api_key_section($sections) {
//     $sections['autocomplete_settings'] = __('Address Autocomplete Settings', 'text-domain');
//     return $sections;
// }

// // Add settings to the new section
// add_filter('woocommerce_get_settings_shipping', 'add_autocomplete_api_key_settings', 10, 2);
// function add_autocomplete_api_key_settings($settings, $current_section) {
//     if ($current_section === 'autocomplete_settings') {
//         $settings = array(
//             array(
//                 'title' => __('Address Autocomplete API Key', 'text-domain'),
//                 'type'  => 'title',
//                 'id'    => 'autocomplete_settings_title',
//             ),
//             array(
//                 'title'    => __('API Key', 'text-domain'),
//                 'desc'     => __('Enter the API key for the address autocomplete feature. Currently supports Google Maps. <a href="https://developers.google.com/maps/documentation/places/web-service/get-api-key" target="_blank" rel="nofollow">Follow the instructions here.</a>', 'text-domain'),
//                 'id'       => 'checkout_address_autocomplete_api_key',
//                 'type'     => 'text',
//                 'desc_tip' => true,
//                 'default'  => '',
//             ),
//             array(
//                 'type' => 'sectionend',
//                 'id'   => 'autocomplete_settings_end',
//             ),
//         );
//     }

//     return $settings;
// }