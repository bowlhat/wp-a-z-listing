/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */

// import { get } from 'lodash';

import { Component } from '@wordpress/element';
import {
	// BaseControl,
	FormTokenField,
	PanelBody,
	Placeholder,
	RangeControl,
	SelectControl,
	Spinner,
	ToggleControl,
	// ToolbarGroup,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	// BlockAlignmentToolbar,
	// BlockControls,
	// __experimentalImageSizeControl as ImageSizeControl,
} from '@wordpress/block-editor';
// import { withSelect } from '@wordpress/data';
import {
	pin,
	// list,
	// grid
} from '@wordpress/icons';
import { addFilter, applyFilters } from '@wordpress/hooks';

import {v4 as uuid} from 'uuid';

/**
 * Internal dependencies
 */
import { MAX_POSTS_COLUMNS } from './constants';

import ItemSelection from '../components/ItemSelection';
import DisplayOptions from '../components/DisplayOptions';
import AZInspectorControls from '../components/AZInspectorControls';

/**
 * Filters to kill any stale state when selections are changed in the editor
 */
addFilter(
	'a_z_listing_selection_changed_for__display',
	'a_z_listing',
	( attributes ) => ( {
		...attributes,
		'post-type': 'page',
		taxonomy: '',
		terms: [],
	} ),
	5
);
addFilter(
	'a_z_listing_selection_changed_for__post-type',
	'a_z_listing',
	( attributes ) => ( {
		...attributes,
		taxonomy: '',
		terms: [],
	} ),
	5
);
addFilter(
	'a_z_listing_selection_changed_for__taxonomy',
	'a_z_listing',
	( attributes ) => ( {
		...attributes,
		terms: [],
	} ),
	5
);
const displayTypes = applyFilters(
	'a_z_listing_display_types',
	[
		{ value: 'posts', label: __( 'Posts', 'a-z-listing' ) },
		{ value: 'terms', label: __( 'Taxonomy Terms', 'a-z-listing' ) },
	] 
);
const alphabet = __( 'AÁÀÄÂaáàäâ,Bb,CÇcç,Dd,EÉÈËÊeéèëê,Ff,Gg,Hh,IÍÌÏÎiíìïî,Jj,Kk,Ll,Mm,Nn,OÓÒÖÔoóòöô,Pp,Qq,Rr,Ssß,Tt,UÚÙÜÛuúùüû,Vv,Ww,Xx,Yy,Zz', 'a-z-listing' );

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
		this.fetchRequest = ( () => {
			const promises = [];
			for ( const path in APIQueries ) {
				const promise = apiFetch( {
					path: addQueryArgs( path, APIQueries[ path ].queryParams ),
				} )
					.then( ( result ) => {
						if ( this.isStillMounted ) {
							this.setState( {
								[ APIQueries[ path ].variableName ]: result,
							} );
						}
					} )
					.catch( () => {
						if ( this.isStillMounted ) {
							this.setState( {
								[ APIQueries[ path ].variableName ]: [],
							} );
						}
					} );
				promises.push( promise );
			}
			return Promise.all( promises );
		} )();
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	render() {
		const {
			attributes,
			setAttributes,
			imageSizeOptions,
			defaultImageWidth,
			defaultImageHeight,
		} = this.props;
		const { postTypeList, taxonomiesList } = this.state;
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
			const errors = [];
			if ( 'terms' === attributes.display && ! attributes.taxonomy ) {
				errors.push(
					__(
						`You must set a taxonomy when display mode is set to 'terms'.`,
						'a-z-listing'
					)
				);
			}
			return errors;
		};

		if ( ! ( 'instanceId' in attributes && attributes.instanceId ) ) {
			attributes.instanceId = uuid();
		}

		const getFilteredTaxonomies = () => {
			let r = [];
			if ( 'posts' === attributes.display ) {
				const postType = attributes[ 'post-type' ];
				if ( postType in postTypeList ) {
					r = postTypeList[ postType ].taxonomies.map( ( tax ) => ( {
						value: tax,
						label: taxonomiesList[ tax ]?.name ?? tax,
					} ) );
				}
			} else if ( 'terms' === attributes.display ) {
				r = Object.keys( taxonomiesList ).map( ( tax ) => ( {
					value: tax,
					label: taxonomiesList[ tax ]?.name ?? tax,
				} ) );
			}
			r.unshift( { value: '', label: '' } );
			return r;
		};
		const inspectorControls = (
			<InspectorControls>
				<AZInspectorControls.Slot>
					{ ( fills ) => (
						<>
							{/* <PanelBody
								title={ __( 'Featured image settings' ) }
							>
								<ToggleControl
									label={ __( 'Display featured image' ) }
									checked={ displayFeaturedImage }
									onChange={ ( value ) =>
										setAttributes( {
											displayFeaturedImage: value,
										} )
									}
								/>
								{ displayFeaturedImage && (
									<>
										<ImageSizeControl
											onChange={ ( value ) => {
												const newAttrs = {};
												if (
													value.hasOwnProperty(
														'width'
													)
												) {
													newAttrs.featuredImageSizeWidth =
														value.width;
												}
												if (
													value.hasOwnProperty(
														'height'
													)
												) {
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
											imageSizeOptions={
												imageSizeOptions
											}
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
												controls={ [
													'left',
													'center',
													'right',
												] }
												isCollapsed={ false }
											/>
										</BaseControl>
									</>
								) }
							</PanelBody> */}

							<PanelBody title={ __( 'Item selection', 'a-z-listing' ) }>
								<ItemSelection.Slot>
									{ ( subFills ) => (
										<>
											<SelectControl
												label={ __( 'Display mode', 'a-z-listing' ) }
												value={ display }
												options={ displayTypes }
												onChange={ ( value ) =>
													setAttributes(
														applyFilters(
															'a_z_listing_selection_changed_for__display',
															{ display: value }
														)
													)
												}
											/>

											{ 'posts' ===
												attributes.display && (
												<SelectControl
													label={ __( 'Post Type', 'a-z-listing' ) }
													value={
														attributes[
															'post-type'
														]
													}
													options={ Object.keys(
														postTypeList
													).map( ( type ) => ( {
														value: type,
														label:
															postTypeList[ type ]
																.name,
													} ) ) }
													onChange={ ( value ) =>
														setAttributes(
															applyFilters(
																'a_z_listing_selection_changed_for__post-type',
																{
																	'post-type': value,
																}
															)
														)
													}
												/>
											) }

											{ (
												'posts' === attributes.display ||
												'terms' === attributes.display
											  ) && (
												<SelectControl
													label={ __( 'Taxonomy', 'a-z-listing' ) }
													value={ attributes.taxonomy }
													options={ getFilteredTaxonomies() }
													onChange={ ( value ) =>
														setAttributes(
															applyFilters(
																'a_z_listing_selection_changed_for__taxonomy',
																{ taxonomy: value }
															)
														)
													}
												/>
											) }

											{ 'posts' === attributes.display &&
												!! attributes.taxonomy && (
													<FormTokenField
														label={ __(
															'Taxonomy terms',
															'a-z-listing'
														) }
														value={
															attributes.terms
														}
														onChange={ ( value ) =>
															setAttributes( {
																terms: value,
															} )
														}
													/>
												) }

											{ subFills }
										</>
									) }
								</ItemSelection.Slot>
							</PanelBody>

							<PanelBody title={ __( 'Display options', 'a-z-listing' ) }>
								<DisplayOptions.Slot>
									{ ( subFills ) => (
										<>
											<TextControl
												label={ __( 'Alphabet', 'a-z-listing' ) }
												value={ attributes.alphabet }
												onChange={ ( value ) =>
													setAttributes( { alphabet: value } )
												}
											/>
											<SelectControl
												label={ __( 'Numbers', 'a-z-listing' ) }
												value={ attributes.numbers }
												options={ [
													{
														value: 'hide',
														label: __(
															'Hide numbers',
															'a-z-listing'
														),
													},
													{
														value: 'before',
														label: __(
															'Prepend before alphabet',
															'a-z-listing'
														),
													},
													{
														value: 'after',
														label: __(
															'Append after alphabet',
															'a-z-listing'
														),
													},
												] }
												onChange={ ( value ) =>
													setAttributes(
														applyFilters(
															'a_z_listing_selection_changed_for__numbers',
															{ numbers: value }
														)
													)
												}
											/>

											<RangeControl
												label={ __( 'Group letters', 'a-z-listing' ) }
												help={ __(
													'The number of letters to include in a single group',
													'a-z-listing'
												) }
												value={
													attributes.grouping || 1
												}
												min={ 1 }
												max={ 10 }
												onChange={ ( value ) =>
													setAttributes(
														applyFilters(
															'a_z_listing_selection_changed_for__grouping',
															{ grouping: value }
														)
													)
												}
											/>

											{ 'hide' !== attributes.numbers &&
												! (
													1 < attributes.grouping
												) && (
													<ToggleControl
														label={ __(
															'Group numbers',
															'a-z-listing'
														) }
														help={ __(
															'Group 0-9 as a single letter',
															'a-z-listing'
														) }
														checked={
															!! attributes[
																'group-numbers'
															]
														}
														onChange={ ( value ) =>
															setAttributes(
																applyFilters(
																	'a_z_listing_selection_changed_for__group-numbers',
																	{
																		'group-numbers': !! value,
																	}
																)
															)
														}
													/>
												) }
											
											<ToggleControl
												label={ __( 'Display symbols entry first', 'a-z-listing' ) }
												checked={ attributes.symbolsFirst }
												onChange={ ( value ) =>
													setAttributes( {
														symbolsFirst: value,
													} )
												}
											/>

											{ subFills }
										</>
									) }
								</DisplayOptions.Slot>

								{ postLayout === 'grid' && (
									<RangeControl
										label={ __( 'Columns', 'a-z-listing' ) }
										value={ columns }
										onChange={ ( value ) =>
											setAttributes( { columns: value } )
										}
										min={ 2 }
										max={ MAX_POSTS_COLUMNS }
										required
									/>
								) }
							</PanelBody>

							{ fills }
						</>
					) }
				</AZInspectorControls.Slot>
			</InspectorControls>
		);

		// const layoutControls = [
		// 	{
		// 		icon: list,
		// 		title: __( 'List view' ),
		// 		onClick: () => setAttributes( { postLayout: 'list' } ),
		// 		isActive: postLayout === 'list',
		// 	},
		// 	{
		// 		icon: grid,
		// 		title: __( 'Grid view' ),
		// 		onClick: () => setAttributes( { postLayout: 'grid' } ),
		// 		isActive: postLayout === 'grid',
		// 	},
		// ];

		const errors = applyFilters(
			'a-z-listing-validation-errors',
			validateConfiguration()
		);

		return (
			<>
				{ inspectorControls }
				{/* <BlockControls>
					<ToolbarGroup controls={ layoutControls } />
				</BlockControls> */}

				{ errors.length > 0 ? (
					<Placeholder icon={ pin } label={ __( 'A-Z Listing', 'a-z-listing' ) }>
						{ __( 'The A-Z Listing configuration is incomplete:', 'a-z-listing' ) }
						<ul>
							{ errors.map( ( error, idx ) => (
								<li key={ idx }>{ error }</li>
							) ) }
						</ul>
					</Placeholder>
				) : (
					<ServerSideRender
						block="a-z-listing/block"
						attributes={ attributes }
						LoadingResponsePlaceholder={ () => <Spinner /> }
						ErrorResponsePlaceholder={ () => (
							<Placeholder
								icon={ pin }
								label={ __( 'A-Z Listing', 'a-z-listing' ) }
							>
								{ __( 'Error Loading the listing...', 'a-z-listing' ) }
							</Placeholder>
						) }
						EmptyResponsePlaceholder={ () => (
							<Placeholder
								icon={ pin }
								label={ __( 'A-Z Listing', 'a-z-listing' ) }
							>
								{ __(
									'The listing has returned an empty page. This is likely an error.',
									'a-z-listing'
								) }
							</Placeholder>
						) }
					/>
				) }
			</>
		);
	}
}

// export default withSelect( ( select, props ) => {
// 	const { featuredImageSizeSlug } = props.attributes;
// 	const { getSettings } = select( 'core/block-editor' );
// 	const { imageSizes, imageDimensions } = getSettings();

// 	const imageSizeOptions = imageSizes
// 		.filter( ( { slug } ) => slug !== 'full' )
// 		.map( ( { name, slug } ) => ( { value: slug, label: name } ) );

// 	return {
// 		defaultImageWidth: get(
// 			imageDimensions,
// 			[ featuredImageSizeSlug, 'width' ],
// 			0
// 		),
// 		defaultImageHeight: get(
// 			imageDimensions,
// 			[ featuredImageSizeSlug, 'height' ],
// 			0
// 		),
// 		imageSizeOptions,
// 	};
// } )( A_Z_Listing_Edit );
export default A_Z_Listing_Edit;
