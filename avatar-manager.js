( function( $ ) {
	$( document ).ready( function() {
		$( '#your-profile' ).attr( 'enctype', 'multipart/form-data' );

		// Disables upload buttons until files are selected.
		( function() {
			var button, input, avatarManager = $( '#avatar-manager' );

			if ( ! avatarManager.length )
				return;

			button = avatarManager.find( 'input[type="submit"]' );
			input  = avatarManager.find( 'input[type="file"]' );

			function toggleUploadButton() {
				button.prop( 'disabled', '' === input.map( function() {
					return $( this ).val();
				} ).get().join( '' ) );
			}

			toggleUploadButton();

			input.on( 'change', toggleUploadButton );
		} )();
	} );
} )( jQuery );
