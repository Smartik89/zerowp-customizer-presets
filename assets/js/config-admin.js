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
			addSelector: '.zwpocp_preset_uploader',
			mediaContainder: '#zwpocp_preset_add_image',
			inputImageSelector: '#zwpocp_preset_image',
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
			var image_frame;
			var zip_frame;

			var _base = {

				openFrame: function(){
					plugin.on( 'click', settings.addSelector, function( event ){
						event.preventDefault();

						var _this = $(this);
						var _mode = false;
						if( $(this).hasClass('add-image') ){
							_mode = 'image';
							self.doFrame( _this, _mode, image_frame );
						}
						else if( $(this).hasClass('add-zip') ){
							_mode = 'zip';
							self.doFrame( _this, _mode, zip_frame );
						}

					});
				},

				doFrame: function( _this, _mode, frame ){

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
					else if( _mode === 'image' ){
						_defaults.library = {
							type: 'image'
						};
					}

					return wp.media( _defaults );
				},

				setMedia: function( attachments ){
					var _container = plugin.find( settings.mediaContainer ),
					_image = ( attachments[0] ) ? attachments[0] : false;
					if( _image && _image.url ){
						var _thumb = false;
						if( _image.sizes.medium ){
							_thumb = _image.sizes.medium.url;
						}
						else{
							_thumb = _image.url;
						}
						plugin.find( settings.addSelector + '.add_image' ).html( '<img src="'+ _thumb +'" />' );
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
					$( document ).on( 'click', '#zwpocp_preset_download', function(){
						var _this = $( this );
						var _id = _this.data('preset-id');
					
						$.ajax({
							url: zwpocp_presets.ajax_url,
							type: 'POST',
							data: {
								'action': 'zwpocp_presets_download_preset',
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
					$( document ).on( 'click', '#zwpocp_preset_delete', function(){
						var _this = $( this );
						var _id = _this.data('preset-id');
					
						$.ajax({
							url: zwpocp_presets.ajax_url,
							type: 'POST',
							data: {
								'action': 'zwpocp_presets_delete_preset',
								'preset_id': _id,
							},
							timeout: 90000, //1.5 minutes
							success: function(data, textStatus, xhr) {
								// console.log( data );
								if( data === 'preset_deleted' ){
									$(document).find( '#zwpocp-preset-'+ _id ).slideUp( 150, function(){
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
					var _msg = zwpocp_presets.error_save_before_create_preset;
					var _r = false;

					if( ! _ready_to_create_preset ){
						if( $('.zwpocp-preset-create-block').find('.zwpocp-preset-create-error').length < 1 ){
							$('.zwpocp-preset-create-block').prepend( '<div class="zwpocp-preset-create-error">'+ _msg +'</div>' );
						}
						_r = false;
					}
					else{
						$('.zwpocp-preset-create-block').find('.zwpocp-preset-create-error').remove();
						_r = true;
					}

					return _r;
				},

				createPreset: function(){
					$( '#zwpocp_preset_create' ).on( 'click', function(){
						
						if( self.canCreatePreset() === false ){
							return false;
						}

						var _value = $( '#zwpocp_preset_name' ).val();
						var _image = $( '#zwpocp_preset_image' ).val();
						if( _value.length > 0 ){
							$( '#zwpocp_preset_name' ).removeClass('invalid-name');
							$.ajax({
								url: zwpocp_presets.ajax_url,
								type: 'POST',
								data: {
									'action': 'zwpocp_presets_create_preset',
									'name': _value,
									'image': _image,
								},
								timeout: 90000, //1.5 minutes
								success: function(data, textStatus, xhr) {
									data = JSON.parse( data );
									// console.log( data );

									if( data.template ){
										$('#zwpocp-presets-list').prepend( data.template );
										$('#zwpocp_preset_name').val('').trigger('change');
										$('#zwpocp_preset_image').val('').trigger('change');
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
							$( '#zwpocp_preset_name' ).addClass('invalid-name');
						}
					} );
				},

				importPreset: function(){
					$( '.zwpocp_preset_ready_for_import' ).on( 'click', function(){
						
						var _zip_url = $( this ).parent().find('.zip-url').val();

						if( _zip_url.length > 0 ){
							$.ajax({
								url: zwpocp_presets.ajax_url,
								type: 'POST',
								data: {
									'action': 'zwpocp_presets_import_preset',
									'zip_url': _zip_url,
								},
								timeout: 90000, //1.5 minutes
								success: function(data, textStatus, xhr) {
									data = JSON.parse( data );
									console.log( data );

									if( data.status === 'imported' && data.template ){
										$('#zwpocp-presets-list').prepend( data.template );
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