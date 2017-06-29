<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;

class TheControl extends ControlBase {
	public function fieldContent() {
		$output  = '';
		$access  = new Access;
		$presets = $access->getAllPresets();
		$presets = array_reverse( $presets, true );

		$output .= '<div class="zwpocp-preset-create-block">';
			$output .= '<input id="zwpocp_preset_name" type="text" class="fullwidth" value="" />';
			$output .= '<input id="zwpocp_preset_image" type="hidden" value="" />';
			$output .= '<span id="zwpocp_preset_create" class="button">'. __( 'Create a new preset', 'zerowp-oneclick-presets' ) .'</span>';
			$output .= '<span class="zwpocp_preset_uploader add_image add-image" title="'. __( 'Add screenshot', 'zerowp-oneclick-presets' ) .'"><span class="dashicons dashicons-format-image"></span></span>';
		$output .= '</div>';
		
		$output .= '<ul id="zwpocp-presets-list" class="zwpocp-presets-list">';
			foreach ($presets as $preset_id => $preset_data) {
				$output .= $access->getPresetItemTemplate( $preset_id, $preset_data );
			}
		$output .= '</ul>';
		
		$output .= '<div class="zwpocp-preset-import-block">';
			$output .= '<input class="zip-url" type="hidden" value="" />';
			$output .= '<span class="zwpocp_preset_uploader zwpocp-preset-select-zip add-zip">'. __( 'Select preset', 'zerowp-oneclick-presets' ) .'</span>';
			$output .= '<span class="zwpocp_preset_ready_for_import button">'. __( 'Import preset', 'zerowp-oneclick-presets' ) .'</span>';
		$output .= '</div>';

		echo $output;
	}

}