<?php
namespace ZeroWpCustomizerPresets;

use ZeroWpCustomizerPresets\Access;

class TheControl extends ControlBase {
	public function fieldContent() {
		$output  = '';
		$access  = new Access;
		$presets = $access->getAllPresets();
		
		$output .= '<ul id="zwpc-presets-list" class="zwpc-presets-list">';
			foreach ($presets as $preset_id => $preset_data) {
				$output .= $access->getPresetItemTemplate( $preset_id, $preset_data );
			}
		$output .= '</ul>';
		
		$output .= '<div class="zwpc-preset-create-block">';
			$output .= '<input id="zwpc_preset_name" type="text" class="fullwidth" value="" />';
			$output .= '<span id="zwpc_preset_create" class="button">Create a new preset</span>';
		$output .= '</div>';

		echo $output;
	}

}