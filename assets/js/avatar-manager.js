( function( $ ) {
	$( document ).ready( function() {
		var frame;

		$( '#your-profile' ).attr( 'enctype', 'multipart/form-data' );

		// Disables upload buttons until files are selected.
		( function() {
			var button, input, avatarManager = $( '#avatar-manager' );

			if ( ! avatarManager.length ) {
				return;
			}

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

		$( '#avatar-manager-choose-from-library-link' ).click( function( event ) {
			var $el = $( this );

			event.preventDefault();

			// Reopens the media frame if it already exists.
			if ( frame ) {
				frame.open();

				return;
			}

			// Creates the media frame.
			frame = wp.media.frames.customAvatar = wp.media( {

				// Sets the title of the modal.
				title: $el.data( 'choose' ),

				// Tells the modal to show only images.
				library: {
					type: 'image'
				},

				// Customizes the submit button.
				button: {

					// Sets the text of the button.
					text: $el.data( 'update' ),

					// Tells the button not to close the modal, since we're
					// going to refresh the page when the image is selected.
					close: false
				}
			} );

			// Runs a callback when an image is selected.
			frame.on( 'select', function() {

				// Grabs the selected attachment.
				var attachment = frame.state().get( 'selection' ).first(),
					link = $el.data( 'updateLink' );

				// Tells the browser to navigate to the update link.
				window.location = link + '&avatar_manager_attachment_id=' + attachment.id;
			} );

			// Opens the modal.
			frame.open();
		} );
	} );
} )( jQuery );
