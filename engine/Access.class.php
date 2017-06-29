<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\FileManager;

class Access {

	public $main_option_key;
	public $theme_option_key;
	public $theme_slug;
	
	public function __construct(){
		$this->theme_slug = get_stylesheet();
		$this->main_option_key = 'zwpocp_presets_oneclick_presets';
		$this->theme_option_key = $this->main_option_key . '_' . $this->theme_slug;
		$this->file_manager = new FileManager;
	}

	public function getAllPresets(){
		$presets = get_option( $this->theme_option_key );
		
		if( empty( $presets ) ){
			$presets = array();
		}

		return $presets;
	}

	public function getPreset( $preset_id ){
		$presets = $this->getAllPresets();
		if( !empty( $presets ) && array_key_exists( $preset_id, $presets ) ){
			return $presets[ $preset_id ];
		}
		else{
			return false;
		}
	}

	public function getPresetName( $preset_id ){
		$preset = $this->getPreset( $preset_id );
		return !empty($preset['name']) ? $preset['name'] : $preset['id'];
	}

	public function getPresetMods( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['mods'] ) ){
			return $mods['mods'];
		}
		else{
			return false;
		}
	}

	public function getPresetOtherMods( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['other_mods'] ) ){
			return $mods['other_mods'];
		}
		else{
			return false;
		}
	}

	// Include current theme option key name in the list of backups
	protected function _addCurrentThemeToTheListInMainOption(){
		$list = get_option( $this->main_option_key, array() );
		if( ! array_key_exists( $this->theme_option_key ) ){
			$list[ $this->theme_option_key ] = $this->theme_option_key;
			update_option( $this->main_option_key, $list );
		}
	}

	public function createPreset( $preset_id, $preset_data ){
		$presets = $this->getAllPresets();
		$presets[ $preset_id ] = $preset_data;
		 
		update_option( $this->theme_option_key, $presets );
		$this->_addCurrentThemeToTheListInMainOption();

		do_action( 'zwpocp_preset:create_preset', $preset_id, $this );
	}

	public function deletePreset( $preset_id ){
		$presets = $this->getAllPresets();

		unset( $presets[ $preset_id ] );
		update_option( $this->theme_option_key, $presets );

		if( file_exists( $this->zipFilePath( $preset_id ) ) ){
			unlink( $this->zipFilePath( $preset_id ) );
		}

		do_action( 'zwpocp_preset:delete_preset', $preset_id );
	}

	/*
	-------------------------------------------------------------------------------
	Download preset
	-------------------------------------------------------------------------------
	*/
	public function downloadPreset( $preset_id ){
		$preset = $this->getPreset( $preset_id );
		
		unset( $preset['other_mods'] ); // When exported, the front page will return 404. Better to remove this.

		$serialized_data = maybe_serialize( $preset );
		$json_data = wp_json_encode( $preset );
		$zip_file = $this->zipFilePath( $preset_id );

		// if( ! file_exists( $zip_file ) ){
			
			/* Get all images
			We get the images from a serialized array, so it treats it all together as one big string
			----------------------*/
			$images = $this->file_manager->getAllImagesFromString( $serialized_data );

			/* Copy all images
			-----------------------*/
			$image_paths = array();
			if( !empty($images[0]) ){
				foreach ($images[0] as $img) {
					$img_path = $this->file_manager->urlToPath( $img );
					$this->file_manager->copyFile( 
						$img_path, 
						$this->staticPresetPath( 
							$preset_id, 
							'img/'. str_ireplace( $this->file_manager->getUploadPath(), '', $img_path ) 
						) 
					);
				}
			}

			/* Save preset data
			-----------------------------------------*/
			$this->file_manager->putContents( 
				$this->staticPresetPath( $preset_id, 'preset.json' ),
				str_ireplace( str_replace( '/', '\/', $this->file_manager->getUploadUrl() ), '{{IMG_ABSTRACT_PRESET_URL}}/img/', $json_data )
			);

			do_action( 'zwpocp_presets:before_zip_preset', $preset_id, $this );

			/* Create the zip archive of this preset
			---------------------------------------------*/
			$this->file_manager->zipDir( $this->staticPresetPath( $preset_id ), $zip_file );

			/* Finally, remove the temp dir
			------------------------------------*/
			// $this->file_manager->removeDir( $this->staticPresetPath( $preset_id ) );
			
		// }

		do_action( 'zwpocp_preset:download_preset', $preset_id );

		return $zip_file;
	}

	public function staticPresetPath( $preset_id, $file_name = '' ){
		return $this->staticPresetsPath() . $preset_id .'/'. $file_name;
	}

	public function staticPresetsPath(){
		return $this->file_manager->getUploadPath() . $this->theme_slug . '-presets/';
	}

	public function zipFilePath( $preset_id ){
		return $this->staticPresetsPath() . $preset_id . '.zip';
	}

	/*
	-------------------------------------------------------------------------------
	Import preset
	-------------------------------------------------------------------------------
	*/
	public function importPreset( $zip_url ){
		$zip_path = $this->file_manager->urlToPath( $zip_url );
		$response = array( 'status' => 'not_imported' );
		
		if( file_exists( $zip_path ) ){
			$temp_path = $this->staticPresetsPath() . '/temp';

			// Remove the temporary dir if it already exists to avoid colisions
			$this->file_manager->removeDir( $temp_path );

			// Unzip preset to a temporary directory
			$unziped = $this->file_manager->unzip( $zip_path, $temp_path );

			// Get preset json data
			$preset_data = file_get_contents( $temp_path .'/preset.json' );

			// Extract the preset ID
			$preset_data_array = json_decode( $preset_data, true );
			$preset_id = is_array( $preset_data_array ) && !empty( $preset_data_array['id'] ) 
							? sanitize_key( $preset_data_array['id'] ) 
							: false;

			// Check if a preset with this name exists. If it does, then change the ID
			$new_preset_id = false;
			
			$base_preset_path = $this->staticPresetsPath() . $preset_id;
			$preset_temp_path = $base_preset_path;
			$i = 1;
			while( file_exists( $preset_temp_path )) {
				$preset_temp_path = $base_preset_path . $i;
				$new_preset_id = basename( $preset_temp_path );
				$i++;
			}

			// Replace the ID in preset array
			if( $new_preset_id ){
				$preset_id = $new_preset_id;
				$preset_data_array['id'] = $preset_id;
				$preset_data = wp_json_encode( $preset_data_array );
			}

			// Replace the images URL
			$data_with_current_server = str_ireplace( 
				'{{IMG_ABSTRACT_PRESET_URL}}', 
				str_replace( 
					'/', 
					'\/', 
					untrailingslashit( $this->file_manager->pathToUrl( $this->staticPresetPath( $preset_id ) ) )
				), 
				$preset_data 
			);

			$this->file_manager->putContents( 
				$temp_path .'/preset.json',
				$data_with_current_server
			);

			if( $preset_id ){
				// Copy preset from temp dir to permanent dir
				$this->file_manager->copyDir( $temp_path, $this->staticPresetsPath() . $preset_id );

				// Copy dynamic CSS if it exists(Support for 'ZeroWP LESS CSS Compiler' plugin)
				// $this->file_manager->copyDir( $temp_path .'/dynamic-css', $this->file_manager->getUploadPath() );

				// Add this preset to DB
				$this->createPreset( $preset_id, json_decode( $data_with_current_server, true ) );

				$response['status'] = 'imported';
				$response['template']  = $this->getPresetItemTemplate( $preset_id, false );
			}
			else{
				error_log( "Invalid preset ID: {$preset_id}" );
			}
		}

		return $response;
	}


	/*
	-------------------------------------------------------------------------------
	A single preset template from list
	-------------------------------------------------------------------------------
	*/
	public function getPresetItemTemplate( $preset_id, $preset_data ){
		$preset_data = $preset_data ? $preset_data : $this->getPreset( $preset_id );
		$name        = !empty( $preset_data[ 'name' ] ) ? $preset_data[ 'name' ] : $preset_id;
		$id          = esc_attr( $preset_id );
		$time        = !empty( $preset_data[ 'time' ] ) ? date_i18n( 'Y-m-d H:i:s', $preset_data[ 'time' ] ) : '';
		$url         = esc_url_raw( add_query_arg( array( 'zwpocp_preset' => $id ), home_url() ) );
		$image       = !empty( $preset_data[ 'image' ] ) ? '<div class="preset-image"><img src="'. $preset_data[ 'image' ] .'" /></div>' : '';

		return '<li id="zwpocp-preset-'. $id .'"><div class="preset" data-preset-id="'. $id .'">
			<a href="'. $url .'" target="_blank" class="preset-preview">
				'. $image .'
				<div class="preset-title">'. $name .' </div>
				<div class="preset-creation-date">'. $time .'</div>
			</a>
			<div class="preset-actions">
				<span id="zwpocp_preset_use" data-preset-id="'. $id .'" class="preset-use">'. __( 'Use preset', 'zerowp-oneclick-presets' ) .'</span>
				<span id="zwpocp_preset_download" data-preset-id="'. $id .'" class="preset-download" title="'. __( 'Download', 'zerowp-oneclick-presets' ) .'"><span class="dashicons dashicons-download"></span></span>
				<span id="zwpocp_preset_delete" data-preset-id="'. $id .'" class="preset-delete" title="'. __( 'Delete', 'zerowp-oneclick-presets' ) .'"><span class="dashicons dashicons-trash"></span></span>
			</div>
		</div></li>';
	}

}