<?php
/**
* Plugin Name:       Address Autocomplete for Woocommerce Block Checkout
* Description:       Example block scaffolded with Create Block tool.
* Author:            Sterner Stuff, eclev91
* Author URI:	 	 https://sternerstuff.dev
* Requires at least: 6.1
* Requires PHP:      7.0
* Version:           0.1.0
* License:           GPL-2.0-or-later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:       address-autocomplete-woocommerce-block-checkout
* Requires Plugins:  woocommerce
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
	if ( !class_exists( 'WC_Integration' ) ) {
		return;
	}

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

	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'aawbc_settings_link' );
	function aawbc_settings_link( array $links ) {
		$url = AddressAutocompleteForBlocksIntegration::get_settings_url();
		$settings_link = '<a href="' . $url . '">' . __('Settings', 'textdomain') . '</a>';
		$links[] = $settings_link;
		return $links;
	}
});