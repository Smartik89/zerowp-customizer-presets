;(function( $ ) {

	"use strict";

	$( document ).on( 'ready load', function(){
		$( '#zwpc_preset_create' ).on( 'click', function(){
			var _value = $( '#zwpc_preset_name' ).val();
			if( _value.length > 0 ){
				$.ajax({
					url: zwpc_presets.ajax_url,
					type: 'POST',
					data: {
						'action': 'zwpc_presets_create_preset',
						'name': _value,
					},
					timeout: 90000, //1.5 minutes
					success: function(data, textStatus, xhr) {
						data = JSON.parse( data );
						console.log( data );

						if( data.template ){
							$('#zwpc-presets-list').append( data.template );
						}

					},
					error: function(xhr, textStatus, errorThrown) {
						// console.log( xhr );
						
					},
					complete: function( xhr ){
						// console.log( xhr );
					}
				});
				
			}
		} );
	} );

})(jQuery);