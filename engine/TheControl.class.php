<?php
namespace ZeroWpCustomizerPresets;

use ZeroWpCustomizerPresets\Access;

class TheControl extends ControlBase {
	public function fieldContent() {
		$output  = '';
		$access  = new Access;
		$presets = $access->getAllPresets();
		$presets = array_reverse( $presets, true );

		$output .= '<div class="zwpc-preset-create-block">';
			$output .= '<input id="zwpc_preset_name" type="text" class="fullwidth" value="" />';
			$output .= '<input id="zwpc_preset_image" type="hidden" value="" />';
			$output .= '<span id="zwpc_preset_create" class="button">Create a new preset</span>';
			$output .= '<span class="zwpc_preset_uploader add_image add-image" title="'. __( 'Add screenshot', 'zerop-customizer-presets' ) .'"><span class="dashicons dashicons-format-image"></span></span>';
		$output .= '</div>';
		
		$output .= '<ul id="zwpc-presets-list" class="zwpc-presets-list">';
			foreach ($presets as $preset_id => $preset_data) {
				$output .= $access->getPresetItemTemplate( $preset_id, $preset_data );
			}
		$output .= '</ul>';
		
		$output .= '<div class="zwpc-preset-import-block">';
			$output .= '<input class="zip-url" type="hidden" value="" />';
			$output .= '<span class="zwpc_preset_uploader zwpc-preset-select-zip add-zip">'. __( 'Select preset', 'zerop-customizer-presets' ) .'</span>';
			$output .= '<span class="zwpc_preset_ready_for_import button">'. __( 'Import preset', 'zerop-customizer-presets' ) .'</span>';
		$output .= '</div>';

		echo $output;
	}

}