<?php 
/*
-------------------------------------------------------------------------------
Customizer scripts and styles
-------------------------------------------------------------------------------
*/
add_action( 'customize_controls_enqueue_scripts', function(){
	
	zwpc_presets()->addStyle( zwpc_presets_config('id') . '-styles-admin', array(
		'src'     =>zwpc_presets()->assetsURL( 'css/styles-admin.css' ),
		'enqueue' => true,
	));
	
	zwpc_presets()->addScript( zwpc_presets_config('id') . '-config-admin', array(
		'src'     => zwpc_presets()->assetsURL( 'js/config-admin.js' ),
		'deps'    => array( 'jquery' ),
		'enqueue' => true,
		'zwpc_presets' => array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		),
	));

});

/*
-------------------------------------------------------------------------------
Front-end scripts and styles
-------------------------------------------------------------------------------
*/
add_action( 'wp_enqueue_scripts', function(){
	
	zwpc_presets()->addStyle( zwpc_presets_config('id') . '-styles', array(
		'src'     =>zwpc_presets()->assetsURL( 'css/styles.css' ),
		'enqueue' => false,
	));
	
	zwpc_presets()->addScript( zwpc_presets_config('id') . '-config', array(
		'src'     => zwpc_presets()->assetsURL( 'js/config.js' ),
		'deps'    => array( 'jquery' ),
		'enqueue' => false,
	));

});