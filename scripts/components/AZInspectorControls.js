import { createSlotFill, PanelRow } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AZListingInspectorControls' );

const AZInspectorControls = ( { children, className } ) => (
    <Fill>
		{ children }
    </Fill>
);

AZInspectorControls.Slot = Slot;

export default AZInspectorControls;
