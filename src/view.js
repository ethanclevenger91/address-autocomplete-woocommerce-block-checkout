/**
* Use this file for JavaScript code that you want to run in the front-end
* on posts/pages that contain this block.
*
* When this file is defined as the value of the `viewScript` property
* in `block.json` it will be enqueued on the front end of the site.
*
* Example:
*
* ```js
* {
*   "viewScript": "file:./view.js"
* }
* ```
*
* @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
*/

import { Loader } from "@googlemaps/js-api-loader"
import { dispatch } from "@wordpress/data";

const SHORT_NAME_ADDRESS_COMPONENT_TYPES =
new Set(['street_number', 'administrative_area_level_1',  'postal_code', 'country', ]);

const ADDRESS_COMPONENT_TYPES_IN_FORM = {
	'location': 'address_1',
	'locality': 'city',
	'administrative_area_level_1': 'state',
	'postal_code': 'postcode',
	'country': 'country',
};

function getFormInputElement(componentType) {
	return document.getElementById(componentType);
}

function fillInAddress(place) {
	function getComponentName(componentType) {
		for (const component of place.address_components || []) {
			if (component.types[0] === componentType) {
				return SHORT_NAME_ADDRESS_COMPONENT_TYPES.has(componentType) ?
				component.short_name :
				component.long_name;
			}
		}
		return '';
	}
	
	function getComponentText(componentType) {
		return (componentType === 'location') ?
		`${getComponentName('street_number')} ${getComponentName('route')}` :
		getComponentName(componentType);
	}
	
	let newShippingAddress = {};
	
	Object.entries(ADDRESS_COMPONENT_TYPES_IN_FORM).forEach(([component, field]) => {
		newShippingAddress[field] = getComponentText(component)
	});
	
	dispatch('wc/store/cart').setShippingAddress(newShippingAddress);
}

fetch('/wp-json/aawbc/v1/get-autocomplete-api-key').then((response) => {
	if(!response.ok) {
		console.error('Error fetching API key for autocomplete.');
		return;
	}
	response.json().then((data) => {
		const loader = new Loader({
			apiKey: data.api_key,
			version: "weekly",
		});

		loader.importLibrary('places').then(({ Autocomplete }) => {

			const autocomplete = new Autocomplete(getFormInputElement('shipping-address_1'), {
				fields: ['address_components', 'geometry', 'name'],
				types: ['address'],
			});

			autocomplete.addListener('place_changed', () => {
				const place = autocomplete.getPlace();
				if (!place.geometry) {
					// User entered the name of a Place that was not suggested and
					// pressed the Enter key, or the Place Details request failed.
					window.alert(`No details available for input: '${place.name}'`);
					return;
				}
				fillInAddress(place);
			});
		});
	});
});

