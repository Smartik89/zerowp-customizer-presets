<?php
namespace ZeroWpCustomizerPresets;

use ZeroWpCustomizerPresets\Access;
use ZeroWpCustomizerPresets\FileManager;

class Ajax {
	
	public $access = null;

	public function __construct(){
		$this->access = new Access;
		$this->file_manager = new FileManager;

		add_action( 'wp_ajax_zwpc_presets_create_preset', array( $this, '_ajaxCreatePreset' ) );
		add_action( 'wp_ajax_zwpc_presets_delete_preset', array( $this, '_ajaxDeletePreset' ) );
		add_action( 'wp_ajax_zwpc_presets_download_preset', array( $this, '_ajaxDownloadPreset' ) );
		add_action( 'wp_ajax_zwpc_presets_import_preset', array( $this, '_ajaxImportPreset' ) );
	}

	public function _ajaxCreatePreset(){

		$id     = uniqid( true );
		$data   = $_POST;
		$mods   = get_theme_mods();

		unset( $data['action'] );
		
		$data['name'] = wp_unslash( $data['name'] );
		$data['image'] = !empty( $data['image'] ) ? esc_url_raw( $data['image'] ) : false;
		$data['id']   = sanitize_key( $id );
		$data['time'] = time();
		$data['mods'] = $mods;

		// Setup other mods(actually these are not mods)
		$other_mods = array(
			'show_on_front' => get_option( 'show_on_front' ), 
			'page_on_front' => get_option( 'page_on_front' ), 
			'page_for_posts' => get_option( 'page_for_posts' ), 
		);

		$data['other_mods'] = $other_mods;

		$this->access->createPreset( $id, $data );

		$data['template']  = $this->access->getPresetItemTemplate( $id, $data );
		
		echo wp_json_encode( $data );
		die();
	}

	public function _ajaxDeletePreset(){

		$data   = $_POST;
		$id     = sanitize_key( $data['preset_id'] );

		$this->access->deletePreset( $id );

		echo 'preset_deleted';
		die();
	}

	public function _ajaxDownloadPreset(){

		$data   = $_POST;
		$id     = sanitize_key( $data['preset_id'] );

		$zip_file = $this->access->downloadPreset( $id );

		$response = array( 'status' => 'not_ready_for_download' );
		if( $zip_file ){
			$response[ 'status' ] = 'ready_for_download';
			$response[ 'file' ] = $this->file_manager->pathToUrl( $zip_file );
		}

		echo wp_json_encode( $response );
		die();
	}

	public function _ajaxImportPreset(){
		$data   = $_POST;
		$zip_url = $data['zip_url'];

		$import_response = $this->access->importPreset( $zip_url );

		echo wp_json_encode( $import_response );
		die();
	}

}