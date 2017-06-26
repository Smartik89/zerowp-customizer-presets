<?php 
require_once ZWPC_PRESETS_PATH . 'warnings/abstract-warning.php';

class ZWPC_PRESETS_NoPlugin_Warning extends ZWPC_PRESETS_Astract_Warning{

	public function notice(){
		
		$output = '';
		
		if( count( $this->data ) > 1 ){
			$message = __( 'Please install and activate the following plugins:', 'zerowp-customizer-presets' );
		}
		else{
			$message = __( 'Please install and activate this plugin:', 'zerowp-customizer-presets' );
		}

		$output .= '<h2>' . $message .'</h2>';


		$output .= '<ul class="zwpc_presets-required-plugins-list">';
			foreach ($this->data as $plugin_slug => $plugin) {
				$plugin_name = '<div class="zwpc_presets-plugin-info-title">'. $plugin['plugin_name'] .'</div>';

				if( !empty( $plugin['plugin_uri'] ) ){
					$button = '<a href="'. esc_url_raw( $plugin['plugin_uri'] ) .'" class="zwpc_presets-plugin-info-button" target="_blank">'. __( 'Get the plugin', 'zerowp-customizer-presets' ) .'</a>';
				}
				else{
					$button = '<a href="#" onclick="return false;" class="zwpc_presets-plugin-info-button disabled">'. __( 'Get the plugin', 'zerowp-customizer-presets' ) .'</a>';
				}

				$output .= '<li>'. $plugin_name . $button .'</li>';
			}
		$output .= '</ul>';

		return $output;
	}

}