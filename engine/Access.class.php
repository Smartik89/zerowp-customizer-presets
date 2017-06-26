<?php
namespace ZeroWpCustomizerPresets;

class Access {

	public $option_key = 'zwpc_presets_customizer_presets';
	
	public function getAllPresets(){
		return get_option( $this->option_key );
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

	public function getPresetMods( $preset_id ){
		$mods = $this->getPreset( $preset_id );
		if( !empty( $mods['mods'] ) ){
			return $mods['mods'];
		}
		else{
			return false;
		}
	}

	public function updatePreset( $preset_id, $preset_data ){
		$presets = $this->getAllPresets();

		if( empty( $presets ) ){
			$presets = array();
		}

		$presets[ $preset_id ] = $preset_data;
		 
		update_option( $this->option_key, $presets );

		do_action( 'zwpc_preset:update_preset', $preset_id, $preset_data );
	}

	public function getPresetItemTemplate( $preset_id, $preset_data ){
		$name = !empty( $preset_data[ 'name' ] ) ? $preset_data[ 'name' ] : $preset_id;
		$id   = esc_attr( $preset_id );
		$time = !empty( $preset_data[ 'time' ] ) ? date_i18n( 'Y-m-d H:i:s', $preset_data[ 'time' ] ) : '';
		$url  = esc_url_raw( add_query_arg( array( 'zwpc_preset' => $id ), home_url() ) );

		return '<li><div class="preset" data-preset-id="'. $id .'">
			<div class="preset-title">'. $name .' </div>
			<div class="preset-creation-date">'. $time .'</div>
			<div class="preset-actions">
				<span id="zwpc_preset_use" data-preset-id="'. $id .'" class="preset-use">Use preset</span>
				<a href="'. $url .'" target="_blank" id="zwpc_preset_preview" data-preset-id="'. $id .'" class="preset-preview">Preview</a>
				<span id="zwpc_preset_delete" data-preset-id="'. $id .'" class="preset-delete">Delete</span>
			</div>
		</div></li>';
	}

}