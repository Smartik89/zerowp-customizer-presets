;(function( $ ) {

	"use strict";

	$.fn.ZWPC_Presets = function( options ) {

		if (this.length > 1){
			this.each(function() {
				$(this).ZWPC_Presets(options);
			});
			return this;
		}

		// Defaults
		var settings = $.extend({
			addSelector: '.zwpc_preset_uploader',
			mediaContainder: '#zwpc_preset_add_image',
			inputImageSelector: '#zwpc_preset_image',
			frameMethod: 'select', //select or post
			multiple: false,
		}, options );

		// Cache current instance
		var plugin = this;

		//Plugin go!
		var init = function() {
			plugin.build();
		}

		// Build structure
		this.build = function() {
			var self = false;
			var frame;

			var _base = {

				openFrame: function(){
					plugin.on( 'click', settings.addSelector, function( event ){
						event.preventDefault();

						var _this = $(this);
						var _mode = false;
						if( $(this).hasClass('add-image') ){
							_mode = 'image';
						}
						else if( $(this).hasClass('add-file') ){
							_mode = 'file';
						}
						else if( $(this).hasClass('add-zip') ){
							_mode = 'zip';
						}

						// If the media frame already exists, reopen it.
						if ( frame ) {
							frame.open();
							return;
						}

						// Create a new media frame
						frame = self.createMediaFrame( _mode );

						// When an image is selected in the media frame...
						frame.on( 'select', function() {

							// Get media attachment details from the frame state
							var attachments = frame.state().get('selection').toJSON();
							
							console.log( attachments );

							if( _mode === 'image' ){
								self.setMedia( attachments );
							}
							else if( _mode === 'zip' ){
								self.setZip( _this, attachments );
							}

						});

						// Finally, open the modal on click
						frame.open();

					});
				},

				// Create a media frame
				createMediaFrame: function( _mode ){

					var _defaults = {
						frame: settings.frameMethod,
						title: plugin.data('frame-title'),
						button: {
							text: plugin.data('frame-button-label'),
						},
						multiple: false
					};

					if( _mode === 'zip' ){
						_defaults.library = {
							type: 'application/zip'
						};
					}

					return wp.media( _defaults );
				},

				setMedia: function( attachments ){
					var _container = plugin.find( settings.mediaContainer ),
					_image = ( attachments[0] ) ? attachments[0] : false;
					if( _image && _image.url ){
						var _thumb = false;
						if( _image.sizes.medium.url ){
							_thumb = _image.sizes.medium.url;
						}
						else{
							_thumb = _image.url;
						}
						plugin.find( settings.addSelector ).html( '<img src="'+ _thumb +'" />' );
						plugin.find( settings.inputImageSelector ).val( _thumb ).trigger('change');
					}
				},

				setZip: function( _this, attachments ){
					var _zip = ( attachments[0] ) ? attachments[0] : false;
					if( _zip && _zip.url ){
						_this.html( '<span class="dashicons dashicons-media-archive"></span>' );
						_this.parent().find( '.zip-url' ).val( _zip.url ).trigger('change');
					}
				},

				downloadPreset: function(){
					$( document ).on( 'click', '#zwpc_preset_download', function(){
						var _this = $( this );
						var _id = _this.data('preset-id');
					
						$.ajax({
							url: zwpc_presets.ajax_url,
							type: 'POST',
							data: {
								'action': 'zwpc_presets_download_preset',
								'preset_id': _id,
							},
							timeout: 90000, //1.5 minutes
							success: function(data, textStatus, xhr) {
								data = JSON.parse( data );
								// console.log( data );
								if( data.status === 'ready_for_download' ){
									window.location.assign( data.file );
								}

							},
							error: function(xhr, textStatus, errorThrown) {
								// console.log( xhr );
								
							},
							complete: function( xhr ){
								// console.log( xhr );
							}
						});
					
					} );
				},

				deletePreset: function(){
					$( document ).on( 'click', '#zwpc_preset_delete', function(){
						var _this = $( this );
						var _id = _this.data('preset-id');
					
						$.ajax({
							url: zwpc_presets.ajax_url,
							type: 'POST',
							data: {
								'action': 'zwpc_presets_delete_preset',
								'preset_id': _id,
							},
							timeout: 90000, //1.5 minutes
							success: function(data, textStatus, xhr) {
								// console.log( data );
								if( data === 'preset_deleted' ){
									$(document).find( '#zwpc-preset-'+ _id ).slideUp( 150, function(){
										$(this).remove();
									} );
								}

							},
							error: function(xhr, textStatus, errorThrown) {
								// console.log( xhr );
								
							},
							complete: function( xhr ){
								// console.log( xhr );
							}
						});
					
					} );
				},

				canCreatePreset: function(){
					var _ready_to_create_preset = $('#save').prop('disabled');
					var _msg = zwpc_presets.error_save_before_create_preset;
					var _r = false;

					if( ! _ready_to_create_preset ){
						if( $('.zwpc-preset-create-block').find('.zwpc-preset-create-error').length < 1 ){
							$('.zwpc-preset-create-block').prepend( '<div class="zwpc-preset-create-error">'+ _msg +'</div>' );
						}
						_r = false;
					}
					else{
						$('.zwpc-preset-create-block').find('.zwpc-preset-create-error').remove();
						_r = true;
					}

					return _r;
				},

				createPreset: function(){
					$( '#zwpc_preset_create' ).on( 'click', function(){
						
						if( self.canCreatePreset() === false ){
							return false;
						}

						var _value = $( '#zwpc_preset_name' ).val();
						var _image = $( '#zwpc_preset_image' ).val();
						if( _value.length > 0 ){
							$( '#zwpc_preset_name' ).removeClass('invalid-name');
							$.ajax({
								url: zwpc_presets.ajax_url,
								type: 'POST',
								data: {
									'action': 'zwpc_presets_create_preset',
									'name': _value,
									'image': _image,
								},
								timeout: 90000, //1.5 minutes
								success: function(data, textStatus, xhr) {
									data = JSON.parse( data );
									// console.log( data );

									if( data.template ){
										$('#zwpc-presets-list').prepend( data.template );
										$('#zwpc_preset_name').val('').trigger('change');
										$('#zwpc_preset_image').val('').trigger('change');
										plugin.find( settings.addSelector ).html('<span class="dashicons dashicons-format-image"></span>');
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
						else{
							$( '#zwpc_preset_name' ).addClass('invalid-name');
						}
					} );
				},

				importPreset: function(){
					$( '.zwpc_preset_ready_for_import' ).on( 'click', function(){
						
						var _zip_url = $( this ).parent().find('.zip-url').val();

						if( _zip_url.length > 0 ){
							$.ajax({
								url: zwpc_presets.ajax_url,
								type: 'POST',
								data: {
									'action': 'zwpc_presets_import_preset',
									'zip_url': _zip_url,
								},
								timeout: 90000, //1.5 minutes
								success: function(data, textStatus, xhr) {
									data = JSON.parse( data );
									console.log( data );

									if( data.status === 'imported' && data.template ){
										$('#zwpc-presets-list').prepend( data.template );
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
				},

				/*
				-------------------------------------------------------------------------------
				Construct plugin
				-------------------------------------------------------------------------------
				*/
				__construct: function(){
					self = this;

					self.openFrame();
					self.downloadPreset();
					self.deletePreset();
					self.createPreset();
					self.importPreset();

					return this;
				}

			};

			/*
			-------------------------------------------------------------------------------
			Rock it!
			-------------------------------------------------------------------------------
			*/
			_base.__construct();

		}

		//Plugin go!
		init();
		return this;

	};

	$( document ).on( 'ready load', function(){
		$(document).ZWPC_Presets();
	} );

})(jQuery);