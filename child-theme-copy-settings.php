<?php

/**
 * Plugin Name:       Child Theme Copy Settings
 * Plugin URI:        https://github.com/Longkt/child-theme-copy-settings
 * Description:       This plugin helps you to copy the settings of the Customizer from the parent theme to the child theme easier.
 * Version:           1.0.0
 * Author:            Long Nguyen
 * Author URI:        https://profiles.wordpress.org/longnguyen
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       child-theme-copy-settings
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Child_Theme_Copy_Settings {
	public function __construct() {
		// Register a submenu page under Appearance
		add_action( 'admin_menu', array( $this, 'register_submenu_page' ) );

		// Add the action copy when user click submit
		add_action( 'admin_init', array( $this, 'copy_settings_action' ) );
		
		// Add the Settings link when the plugin is activated
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_settings_link' ) ) ;
	}

	public function register_submenu_page() {
		add_submenu_page( 
			'themes.php', 
			__( 'Child Theme Copy Settings', 'child-theme-copy-settings' ), 
			__( 'Child Theme Copy Settings', 'child-theme-copy-settings' ),
		    'manage_options', 
		    'child-theme-copy-settings',
		    array( $this, 'generate_admin_page' )
		);
	}

	public function copy_settings_action() {
		if( current_user_can( 'administrator' ) && isset( $_REQUEST['nonce_copy_settings'] ) && wp_verify_nonce( $_REQUEST['nonce_copy_settings'], 'nonce_copy_settings_action' ) ) {
			if ( isset( $_REQUEST['copy_from'] ) && isset( $_REQUEST['copy_to'] ) ) {
				$from = sanitize_text_field( $_REQUEST['copy_from'] );
				$to = sanitize_text_field( $_REQUEST['copy_to'] );

				if ( $from && $to ) {
					$mods = get_option( 'theme_mods_' . $from) ;
					update_option( 'theme_mods_' . $to, $mods );

					$url = wp_unslash( $_SERVER['REQUEST_URI'] );
					$url = add_query_arg( array( 'from' => $from, 'to' => $to, 'copied' => 'success' ), $url );
					wp_redirect( $url );
					die();
				}
			}
		}	
	}

	public function generate_admin_page() {
		?>
	    <div class="wrap">
	        <h1><?php _e( 'Child Theme Copy Settings Page', 'child-theme-copy-settings' ); ?></h1>
	        <?php 
	        	if( is_child_theme() ) {
	        		$child_theme = wp_get_theme();
	        		$parent_theme = $child_theme->parent();
					?>
	                <form method="post" action="<?php echo admin_url('themes.php?page=child-theme-copy-settings') ?>" class="copy-settings-form">
	                    <h3>
	                        <strong> <?php printf( esc_html__(  'You are using %1$s theme, it is a child theme of %2$s', 'child-theme-copy-settings' ) ,  $child_theme->Name, $parent_theme->Name ); ?></strong>
	                    </h3>
	                    <p><?php printf( esc_html__(  "Child theme uses it's own theme setting name, would you like to copy setting data from parent theme to this child theme?", 'child-theme-copy-settings' ) ); ?></p>
	                    <p>
						<?php
							$select = '<select name="copy_from">';
							$select .= '<option value="">'. esc_html__( 'From Theme', 'child-theme-copy-settings' ) .'</option>';
							$select .= '<option value="'. esc_attr( $parent_theme->stylesheet ) .'">'. $parent_theme->Name .'</option>';
							$select .='</select>';

							$select_2 = '<select name="copy_to">';
							$select_2 .= '<option value="">'. esc_html__( 'To Theme', 'child-theme-copy-settings' ) .'</option>';
							$select_2 .= '<option value="'. esc_attr( $child_theme->stylesheet ) .'">'. $child_theme->Name .'</option>';
							$select_2 .='</select>';

							echo $select . ' to '. $select_2;

							wp_nonce_field( 'nonce_copy_settings_action', 'nonce_copy_settings' );
						?>
	                        <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Copy now', 'child-theme-copy-settings' ); ?>">
	                    </p>
						<?php if ( isset( $_REQUEST['copied'] ) && 'success' == $_REQUEST['copied'] ) { 
							echo '<h3 class="copy-settings-notice">' . esc_html__( 'Your settings were copied.', 'child-theme-copy-settings' ) . '</h3>';
						} ?>
	                </form>
	        		<?php
	        	} else {
	        		echo '<h3 class="copy-settings-notice">' . esc_html__( 'You are using the main theme or not the Administrator!', 'child-theme-copy-settings' ) . '</h3>';
	        	}
	        ?>
	    </div>
	    <style>
	    	.copy-settings-form {
	    		background-color: #fff;
	    		padding: 20px;
	    	}

	    	.copy-settings-notice {
	    		color: red;
	    	}
	    </style>
	    <?php
	}

	public function add_settings_link( $links ) {
		$settings = array( '<a href="' . admin_url( 'themes.php?page=child-theme-copy-settings' ) . '">' . __( 'Settings', 'child-theme-copy-settings' ) . '</a>' );
		$links = array_reverse( array_merge( $links, $settings ) );

		return $links;
	}
}

new Child_Theme_Copy_Settings();