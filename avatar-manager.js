( function( $ ) {
	$( document ).ready( function() {
		$( '#your-profile' ).attr( 'enctype', 'multipart/form-data' );

		// Disables upload buttons until files are selected
		( function() {
			var button, input, fieldset = $( 'fieldset' );

			if ( ! fieldset.length )
				return;

			button = fieldset.find( 'input[type="submit"]' );
			input  = fieldset.find( 'input[type="file"]' );

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
