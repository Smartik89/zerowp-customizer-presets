<?php 
add_action( 'plugins_loaded', function(){

	add_action( 'wp_ajax_zwpc_presets_create_preset', 'zwpc_presets_create_preset' );
	function zwpc_presets_create_preset(){

		$access = new ZeroWpCustomizerPresets\Access;
		$id     = uniqid( true );
		$data   = $_POST;
		$mods   = get_theme_mods();

		unset( $data['action'] );
		
		$data['id']   = sanitize_key( $id );
		$data['time'] = time();
		$data['mods'] = $mods;

		$access->updatePreset( $id, $data );

		$data['template']  = $access->getPresetItemTemplate( $id, $data );
		
		echo wp_json_encode( $data );
		die();
	}

	/*
	-------------------------------------------------------------------------------
	Preset preview
	-------------------------------------------------------------------------------
	*/
	add_action( 'after_setup_theme', 'zwpc_presets_filter_theme_mods', 99 );
	function zwpc_presets_filter_theme_mods(){
		if( ! is_customize_preview() && ! is_admin() && ! empty($_GET[ 'zwpc_preset' ]) ){
			$theme_slug = get_option( 'stylesheet' );

			// Modify theme mods
			add_filter( 'option_theme_mods_' . $theme_slug, function( $option ){

				$access = new ZeroWpCustomizerPresets\Access;
				$preset = $access->getPresetMods( sanitize_key( $_GET[ 'zwpc_preset' ] ) );
				
				if( !empty( $preset ) && is_array( $preset ) ){
					$option = wp_parse_args( $preset, $option );
				}

				return $option;
			}, 99 );
		}
	}

}, 99 );