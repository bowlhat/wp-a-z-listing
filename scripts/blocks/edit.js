/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */

import { get, includes, invoke, isUndefined, pickBy } from 'lodash';

import { Component, RawHTML } from '@wordpress/element';
import {
	BaseControl,
	FormTokenField,
	PanelBody,
	Placeholder,
	QueryControls,
	RadioControl,
	RangeControl,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
	ToolbarGroup,
	createSlotFill,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import { dateI18n, format, __experimentalGetSettings } from '@wordpress/date';
import {
	InspectorControls,
	BlockAlignmentToolbar,
	BlockControls,
	__experimentalImageSizeControl as ImageSizeControl,
} from '@wordpress/block-editor';
import { withSelect } from '@wordpress/data';
import { pin, list, grid } from '@wordpress/icons';
import { addFilter, applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import {
	MAX_POSTS_COLUMNS,
} from './constants';

import ItemSelection from '../components/ItemSelection';
import DisplayOptions from '../components/DisplayOptions';


/**
 * Filters to kill any stale state when selections are changed in the editor
 */
addFilter( 'a_z_listing_selection_changed_for__display', 'a_z_listing',
	( attributes ) => ( {
		...attributes,
		'post-type': 'page',
		taxonomy: '',
		terms: []
	} ), 5 );
addFilter( 'a_z_listing_selection_changed_for__post-type', 'a_z_listing',
	( attributes ) => ( {
		...attributes,
		taxonomy: '',
		terms: []
	} ), 5 );
addFilter( 'a_z_listing_selection_changed_for__taxonomy', 'a_z_listing',
	( attributes ) => ( {
		...attributes,
		terms: []
	} ), 5 );

const APIQueries = {
	// '/wp/v2/categories': {
	// 	queryParams: { per_page: -1 },
	// 	variableName: 'categoriesList',
	// },
	'/wp/v2/types': {
		queryParams: { per_page: -1 },
		variableName: 'postTypeList',
	},
	'/wp/v2/taxonomies': {
		queryParams: { per_page: -1 },
		variableName: 'taxonomiesList',
	},
};

class A_Z_Listing_Edit extends Component {
	constructor() {
		super( ...arguments );
		this.state = {
			taxonomiesList: [],
			postTypeList: [],
		};
	}

	componentDidMount() {
		this.isStillMounted = true;
		this.fetchRequest = (() => {
			let promises = [];
			for ( const path in APIQueries ) {
				const promise = apiFetch( {
					path: addQueryArgs( path, APIQueries[ path ].queryParams ),
				} )
					.then( ( result ) => {
						if ( this.isStillMounted ) {
							this.setState( { [ APIQueries[ path ].variableName ]: result } );
						}
					})
					.catch( () => {
						if ( this.isStillMounted ) {
							this.setState( { [ APIQueries[ path ].variableName ]: [] } );
						}
					});
				promises.push( promise );
			}
			return Promise.all( promises );
		})()
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	render() {
		const {
			attributes,
			setAttributes,
			imageSizeOptions,
			latestPosts,
			defaultImageWidth,
			defaultImageHeight,
		} = this.props;
		const {
			postTypeList,
			taxonomiesList,
		} = this.state;
		const {
			display,
			displayFeaturedImage,
			postLayout,
			columns,
			featuredImageAlign,
			featuredImageSizeSlug,
			featuredImageSizeWidth,
			featuredImageSizeHeight,
		} = attributes;
		const validateConfiguration = () => {
			let errors = [];
			if ( 'terms' === attributes.display && ! attributes.taxonomy ) {
				errors.push(<>{ __( `You must set a taxonomy when display mode is set to 'terms'.` ) }</>)
			}
			return errors;
		}
		const getFilteredTaxonomies = () => {
			let r = [];
			if ( 'posts' === attributes.display ) {
				const postType = attributes['post-type'];
				if ( postType in postTypeList ) {
					r = postTypeList[ postType ].taxonomies.map( ( tax, idx ) => ( {
						value: tax,
						label: taxonomiesList[ tax ]?.name ?? tax,
					} ) );
				}
			} else if ( 'terms' === attributes.display ) {
				r = Object.keys( taxonomiesList ).map( ( tax, idx ) => ( {
					value: tax,
					label: taxonomiesList[ tax ]?.name ?? tax,
				} ) );
			}
			r.unshift( { value: '', label: '' } );
			return r;
		}
		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Featured image settings' ) }>
					<ToggleControl
						label={ __( 'Display featured image' ) }
						checked={ displayFeaturedImage }
						onChange={ ( value ) =>
							setAttributes( { displayFeaturedImage: value } )
						}
					/>
					{ displayFeaturedImage && (
						<>
							<ImageSizeControl
								onChange={ ( value ) => {
									const newAttrs = {};
									if ( value.hasOwnProperty( 'width' ) ) {
										newAttrs.featuredImageSizeWidth =
											value.width;
									}
									if ( value.hasOwnProperty( 'height' ) ) {
										newAttrs.featuredImageSizeHeight =
											value.height;
									}
									setAttributes( newAttrs );
								} }
								slug={ featuredImageSizeSlug }
								width={ featuredImageSizeWidth }
								height={ featuredImageSizeHeight }
								imageWidth={ defaultImageWidth }
								imageHeight={ defaultImageHeight }
								imageSizeOptions={ imageSizeOptions }
								onChangeImage={ ( value ) =>
									setAttributes( {
										featuredImageSizeSlug: value,
										featuredImageSizeWidth: undefined,
										featuredImageSizeHeight: undefined,
									} )
								}
							/>
							<BaseControl>
								<BaseControl.VisualLabel>
									{ __( 'Image alignment' ) }
								</BaseControl.VisualLabel>
								<BlockAlignmentToolbar
									value={ featuredImageAlign }
									onChange={ ( value ) =>
										setAttributes( {
											featuredImageAlign: value,
										} )
									}
									controls={ [ 'left', 'center', 'right' ] }
									isCollapsed={ false }
								/>
							</BaseControl>
						</>
					) }
				</PanelBody>

				<PanelBody title={ __( 'Item selection' ) }>
					<ItemSelection.Slot>
						{ ( fills ) => (
							<>
								<SelectControl
									label={ __( 'Display mode' ) }
									value={ display }
									options={ [
										{ value: 'posts', label: __( 'Posts' ) },
										{ value: 'terms', label: __( 'Taxonomy terms' ) }
									] }
									onChange={ (value) => setAttributes( applyFilters( 'a_z_listing_selection_changed_for__display', { display: value } ) )
									}
								/>

								{ ( 'posts' === attributes.display ) && (
									<SelectControl
										label={ __( 'Post Type' ) }
										value={ attributes['post-type'] }
										options={ Object.keys( postTypeList ).map( ( type, idx ) => ( {
											value: type,
											label: postTypeList[ type ].name,
										} ) ) }
										onChange={ ( value ) => setAttributes( applyFilters( 'a_z_listing_selection_changed_for__post-type', { 'post-type': value } ) ) }
									/>
								) }

								<SelectControl
									label={ __( 'Taxonomy' ) }
									value={ attributes.taxonomy }
									options={ getFilteredTaxonomies() }
									onChange={ ( value ) => setAttributes( applyFilters( 'a_z_listing_selection_changed_for__taxonomy', { taxonomy: value } ) ) }
								/>

								{ ( 'posts' === attributes.display && !! attributes.taxonomy ) && (
									<FormTokenField
										label={ __( 'Taxonomy terms' ) }
										value={ attributes.terms }
										onChange={ ( value ) => setAttributes( { terms: value } ) }
									/>
								) }

								{ fills }
							</>
						) }
					</ItemSelection.Slot>
				</PanelBody>

				<PanelBody title={ __( 'Display options' ) }>
					<DisplayOptions.Slot>
						{ ( fills ) => (
							<>
								<SelectControl
									label={ __( 'Numbers' ) }
									value={ attributes.numbers }
									options={ [
										{ value: 'hide',   label: __( 'Hide numbers' ) },
										{ value: 'before', label: __( 'Prepend before alphabet' ) },
										{ value: 'after',  label: __( 'Append after alphabet' ) },
									] }
									onChange={ ( value ) => setAttributes( applyFilters( 'a_z_listing_selection_changed_for__numbers', { numbers: value } ) ) }
								/>

								<RangeControl
									label={ __( 'Group letters' ) }
									help={ __( 'The number of letters to include in a single group' ) }
									value={ attributes.grouping || 1 }
									min={ 1 }
									max={ 10 }
									onChange={ ( value ) => setAttributes( applyFilters( 'a_z_listing_selection_changed_for__grouping', { grouping: value } ) ) }
								/>

								{ 'hide' !== attributes.numbers && (
									<ToggleControl
										label={ __( 'Group numbers' ) }
										help={ __( 'Group 0-9 as a single letter' ) }
										checked={ !! attributes['group-numbers'] }
										onChange={ ( value ) => setAttributes( applyFilters( 'a_z_listing_selection_changed_for__group-numbers', { 'group-numbers': !!value } ) ) }
									/>
								) }

								{ fills }
							</>
						) }
					</DisplayOptions.Slot>

					{ postLayout === 'grid' && (
						<RangeControl
							label={ __( 'Columns' ) }
							value={ columns }
							onChange={ ( value ) =>
								setAttributes( { columns: value } )
							}
							min={ 2 }
							max={
								! hasPosts
									? MAX_POSTS_COLUMNS
									: Math.min(
											MAX_POSTS_COLUMNS,
											latestPosts.length
									)
							}
							required
						/>
					) }
				</PanelBody>
			</InspectorControls>
		);

		const layoutControls = [
			{
				icon: list,
				title: __( 'List view' ),
				onClick: () => setAttributes( { postLayout: 'list' } ),
				isActive: postLayout === 'list',
			},
			{
				icon: grid,
				title: __( 'Grid view' ),
				onClick: () => setAttributes( { postLayout: 'grid' } ),
				isActive: postLayout === 'grid',
			},
		];

		const dateFormat = __experimentalGetSettings().formats.date;

		const errors = applyFilters( 'a-z-listing-validation-errors', validateConfiguration() );

		return (
			<>
				{ inspectorControls }
				<BlockControls>
					<ToolbarGroup controls={ layoutControls } />
				</BlockControls>

				{ errors.length > 0 ? (
					<Placeholder icon={ pin } label={ __( 'A-Z Listing' ) }>
						{ __( 'The A-Z Listing configuration is incomplete:' ) }
						<ul>
							{ errors.map( ( error ) => (
								<li>{error}</li>
							) ) }
						</ul>
					</Placeholder>
				) : (
					<ServerSideRender
						block="a-z-listing/block"
						attributes={ attributes }
						LoadingResponsePlaceholder={ () => (<Spinner />) }
						ErrorResponsePlaceholder={ () => (
							<Placeholder icon={ pin } label={ __( 'A-Z Listing' ) }>
								{ __( 'Error Loading the listing...' ) }
							</Placeholder>
						) }
						EmptyRersponsePlaceholder={ () => (
							<Placeholder icon={ pin } label={ __( 'A-Z Listing' ) }>
								{ __( 'The listing has returned an empty page. This is likely an error.' ) }
							</Placeholder>
						) }
					/>
				) }
			</>
		);
	}
}

export default withSelect( ( select, props ) => {
	const {
		featuredImageSizeSlug,
	} = props.attributes;
	const { getSettings } = select( 'core/block-editor' );
	const { imageSizes, imageDimensions } = getSettings();

	const imageSizeOptions = imageSizes
		.filter( ( { slug } ) => slug !== 'full' )
		.map( ( { name, slug } ) => ( { value: slug, label: name } ) );

	return {
		defaultImageWidth: get(
			imageDimensions,
			[ featuredImageSizeSlug, 'width' ],
			0
		),
		defaultImageHeight: get(
			imageDimensions,
			[ featuredImageSizeSlug, 'height' ],
			0
		),
		imageSizeOptions,
	};
} )( A_Z_Listing_Edit );
