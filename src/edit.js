/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { useState, useEffect } from '@wordpress/element';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	const [apiKeyExists, setApiKeyExists] = useState(null);
	const [settingsPage, setSettingsPage] = useState(null);
    const [loading, setLoading] = useState(true);

        useEffect(() => {
            // Fetch the API key status from the REST API
            fetch('/wp-json/aawbc/v1/get-autocomplete-api-key')
                .then((response) => {
					if(response.ok) {
						setApiKeyExists(true);
						setLoading(false);
					} else if(response.status === 404) {
						response.json().then((data) => {
							setApiKeyExists(false);
							setSettingsPage(data.settings_page);
							setLoading(false);
						});
					} else {
						console.error('Error fetching API key status:', error);
						setLoading(false);
					}
				})
                .catch((error) => {
                    console.error('Error fetching API key status:', error);
                    setLoading(false);
                });
        }, []);

	if(loading) {
		return (
			<div { ...useBlockProps() }>
				<h3 className="wc-block-components-title">Autocomplete Address Settings</h3>
				<p className="wc-block-components-description">Checking API key...</p>
			</div>
		);
	} else if(apiKeyExists === false) {
		return (
			<div { ...useBlockProps() }>
				<h3 className="wc-block-components-title">Autocomplete Address Settings</h3>
				<p className="wc-block-components-description">Please <a href={settingsPage} target="_blank">set up the Google Maps API key in the WooCommerce settings</a> to enable the autocomplete address feature.</p>
			</div>
		);
	} else if(apiKeyExists === true) {
		return (
			<div { ...useBlockProps() }>
				<h3 className="wc-block-components-title">Autocomplete Address Settings</h3>
				<p className="wc-block-components-description">You're all set! You can edit your API key here.</p>
			</div>
		);
	}
}
