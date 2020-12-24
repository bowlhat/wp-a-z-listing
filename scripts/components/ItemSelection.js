import { createSlotFill } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AZListingItemSelection' );

const ItemSelection = ( { children } ) => <Fill>{ children }</Fill>;

ItemSelection.Slot = Slot;

export default ItemSelection;
