// props to @claytonchase (https://profiles.wordpress.org/claytonchase)
jQuery( document ).ready( function() {
	const tabs = document.getElementById( 'az-tabs' );
	if ( tabs ) {
		const link = tabs.querySelector(
			`#letters a[href="${ window.location.hash }"]`
		);

		if ( window.location.hash && link ) {
			const activeTab = jQuery( link )
				.parent()
				.index();
			jQuery( tabs ).tabs( { active: activeTab } );
		} else {
			jQuery( tabs ).tabs();
		}
	}
} );
