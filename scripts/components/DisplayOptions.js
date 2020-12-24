import { createSlotFill } from '@wordpress/components';

export const { Fill, Slot } = createSlotFill( 'AZListingDisplayOptions' );

const DisplayOptions = ( { children } ) => <Fill>{ children }</Fill>;

DisplayOptions.Slot = Slot;

export default DisplayOptions;
