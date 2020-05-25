import { createSlotFill, PanelRow } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AZListingItemSelection' );

const ItemSelection = ( { children, className } ) => (
    <Fill>
		{ children }
    </Fill>
);

ItemSelection.Slot = Slot;

export default ItemSelection;
