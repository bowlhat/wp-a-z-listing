import { createSlotFill, PanelBody } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AZListingInspectorControls' );

const AZInspectorControls = ( { children, title } ) => (
	<Fill>
		<PanelBody title={ title }>{ children }</PanelBody>
	</Fill>
);

AZInspectorControls.Slot = Slot;

export default AZInspectorControls;
