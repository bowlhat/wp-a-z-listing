/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
import { __ } from '@wordpress/i18n';

import { registerStore, withSelect } from '@wordpress/data';
import { createHooks } from '@wordpress/hooks';
import { registerBlockType } from '@wordpress/blocks';
import { postList as icon } from '@wordpress/icons';
import { registerPlugin } from '@wordpress/plugins';

import edit from './edit';
import attributes from './attributes.json';
import DisplayOptions from '../components/DisplayOptions';
import ItemSelection from '../components/ItemSelection';
import AZInspectorControls from '../components/AZInspectorControls';

const hooks = createHooks();

registerStore( 'a-z-listing/slotfills', {
	reducer( state = {}, action ) { return state },
	actions: {},
	selectors: {
		getDisplayOptions() {
			return DisplayOptions;
		},
		getItemSelection() {
			return ItemSelection;
		},
		getInspectorControls() {
			return AZInspectorControls;
		},
	}
} );

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/#registering-a-block
 */
registerBlockType( 'a-z-listing/wp-a-z-listing-block', {
	/**
	 * This is the display title for your block, which can be translated with `i18n` functions.
	 * The block inserter will show this name.
	 */
	title: __( 'A-Z Listing', 'a-z-listing' ),

	/**
	 * This is a short description for your block, can be translated with `i18n` functions.
	 * It will be shown in the Block Tab in the Settings Sidebar.
	 */
	description: __(
		'Show your posts in an alphabetically-ordered rolodex-style list',
		'a-z-listing'
	),

	/**
	 * Blocks are grouped into categories to help users browse and discover them.
	 * The categories provided by core are `common`, `embed`, `formatting`, `layout` and `widgets`.
	 */
	category: 'widgets',

	/**
	 * An icon property should be specified to make it easier to identify a block.
	 * These can be any of WordPress’ Dashicons, or a custom svg element.
	 */
	icon,

	/**
	 * Optional block extended support features.
	 */
	supports: {
        align: true,
        html: false,
	},

	attributes: hooks.applyFilters( 'a_z_listing_attributes', attributes ),

	edit,

	save: () => null,
} );
