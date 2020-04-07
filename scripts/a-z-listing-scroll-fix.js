function fixAZListingScroll() {
    document.querySelectorAll( '.az-links a[href^="#letter-"]' )
    .forEach( function( a ) {
        a.addEventListener( 'click', function( e ) {
            e.preventDefault();
            const selector = this.href.replace( /.*(#letter-.*)/, '$1' );
            document.querySelector( selector ).scrollIntoView();
            window.scrollBy( 0, -120 );
        });
    });
}
if ( document.readyState === 'loading' ) {
    document.addEventListener('DOMContentLoaded', fixAZListingScroll);
} else {
    fixAZListingScroll();
}
