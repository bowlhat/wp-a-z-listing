import { createSlotFill, PanelRow } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AZListingDisplayOptions' );

const DisplayOptions = ( { children, className } ) => (
    <Fill>
		{ children }
    </Fill>
);

DisplayOptions.Slot = Slot;

export default DisplayOptions;
