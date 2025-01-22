<?php 

class AddressAutocompleteForBlocksIntegration extends \WC_Integration {
	/**
	* Init and hook in the integration.
	*/
	public function __construct() {
		$this->id = self::get_id();
		$this->method_title       = __( 'Address Autocomplete Settings', 'woocommerce-integration-demo' );
		
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		
		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
	}
	
	/**
	* Initialize integration settings form fields.
	*/
	public function init_form_fields() {
		$this->form_fields = array(
			'api_key' => array(
				'title'       => __( 'API Key', 'woocommerce-integration-demo' ),
				'type'        => 'password',
				'description' => __( 'Enter the API key for the address autocomplete feature. Currently supports Google Maps. <a href="https://developers.google.com/maps/documentation/places/web-service/get-api-key" target="_blank" rel="nofollow">Follow the instructions here.</a>', 'woocommerce-integration-demo' ),
				'desc_tip'    => true,
				'default'     => '',
			),
		);
	}

	public static function get_id()
	{
		return 'address-autocomplete-for-woocommerce-block-checkout';
	}

	public static function get_settings_url()
	{
		return admin_url('admin.php?page=wc-settings&tab=integration&section=' . self::get_id());
	}
}