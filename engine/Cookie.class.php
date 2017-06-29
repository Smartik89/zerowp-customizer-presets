<?php
namespace ZeroWpOneClickPresets;

use ZeroWpOneClickPresets\Access;

class Cookie {
	
	public function __construct(){
		add_action( 'after_setup_theme', array( $this, '_setupCookie' ), 9 );
		add_action( 'after_setup_theme', array( $this, '_removeCookie' ), 5 );
		add_action( 'template_redirect', array( $this, '_removeCookieRedirect' ) );
		add_action( 'admin_bar_menu', array( $this, 'notice' ), 499 );
	}

	public function _setupCookie(){
		if( ! is_customize_preview() && ! is_admin() && ! empty($_GET[ 'zwpocp_preset' ]) ){
			setcookie( 'zwpocp_preset', sanitize_key( $_GET[ 'zwpocp_preset' ] ), time()*60*60*3 ); // 3 hours
		}
	}

	public function _removeCookie(){
		if( isset($_GET[ 'zwpocp_preset_remove' ]) ){
			setcookie( 'zwpocp_preset', '', time() - 60*60*3 ); // - 3 hours
		}
	}

	public function _removeCookieRedirect(){
		if( isset($_GET[ 'zwpocp_preset_remove' ]) ){
			wp_redirect( site_url() );
			exit();
		}
	}

	public static function current(){
		if( !empty( $_GET[ 'zwpocp_preset' ] ) ){
			$cookie = sanitize_key( $_GET[ 'zwpocp_preset' ] );
		}

		else if( !empty( $_COOKIE[ 'zwpocp_preset' ] ) ){
			$cookie = sanitize_key( $_COOKIE[ 'zwpocp_preset' ] );
		}

		else{
			$cookie = false;
		}

		return $cookie;
	}

	public function notice(){
		global $wp_admin_bar;

		$access = new Access;

		if( !empty( self::current() ) ){
			$preset_name = $access->getPresetName( self::current() );
			$preset_name = esc_html( $preset_name );

			$wp_admin_bar->add_node(array(
				'id' => 'zerowp-presets-adminbar-notice',
				'parent' => null,
				'href' => esc_url_raw( 
					add_query_arg( 
						array( 'zwpocp_preset_remove' => 1 ), 
						remove_query_arg( 'zwpocp_preset', site_url() )
					) 
				),
				'title' => '<span style="display: inline-block;background: #d73c2c;padding: 5px 8px 6px;margin: 0;height: auto;line-height: 1;color: #fff;">'. 
					sprintf( __( 'Preset "%s" is active: Turn OFF', 'zerowp-oneclick-presets' ), $preset_name ) 
				.'</span>',
			));
		}
	}

}