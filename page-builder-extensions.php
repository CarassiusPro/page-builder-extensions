<?php
/**
 * Plugin Name: Page Builder Extensions
 * Plugin URI: https://www.beaverlodgehq.com/downloads/extensions
 * Description: Enables global features for Page Builder Modules.
 * Version: 1.0.0
 * Author: Beaverlodge HQ
 * Author URI: https://www.beaverlodgehq.com
 */


if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}

function pbext_add_admin_menu(  ) { 
    global $pagebuilder_extension_settings_page;
	$pagebuilder_extension_settings_page = add_submenu_page( 'options-general.php', 'Page Builder Extension', 'Page Builder Extension', 'manage_options', 'page-builder-extension', 'pbext_options_page' );

}

add_action( 'admin_menu', 'pbext_add_admin_menu' );

function pbext_options_page(  ) { 
    ?>
            <h2>Page Builder Extensions</h2>
            
            <ul class="nav nav-tabs" id="pbextTabs">
                <li class="active"><a data-target="#whitelabel" data-toggle="tab">Branding</a></li>
                <li><a data-target="#licenses" data-toggle="tab">License Activations</a></li>
                <?php do_action('page_builder_extension_tabs'); ?>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="whitelabel">
                    <div style="padding: 20px;">
                        <form action='options.php' method='post'>

                            <?php
                            settings_fields( 'pbextPage' );
                            do_settings_sections( 'pbextPage' );
                            submit_button();
                            ?>

                        </form>
                    </div>
                </div>
                <div class="tab-pane" id="licenses">
                    <div style="padding: 20px;">
                        <div class="btn-group" role="group">
                        <?php do_action('page_builder_extension_licence_link'); ?>                        
                        </div>
                        <?php do_action('page_builder_extension_licenses'); ?>
                    </div>
                </div>
                <?php do_action('page_builder_extension_panel'); ?>
            </div>
    <?php    
}

function pbext_add_bootstrap_styles($hook) {
  global $pagebuilder_extension_settings_page;
  if   ( $hook == $pagebuilder_extension_settings_page ) {
      wp_enqueue_style( 'bootstrap_styles', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' );
      wp_enqueue_script( 'bootstrap_script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js' );
      $custom_css = "
                 #wpbody-content{
                        background: #fff;
                }";
        wp_add_inline_style( 'bootstrap_styles', $custom_css );
  }
}
add_action('admin_enqueue_scripts', 'pbext_add_bootstrap_styles');

add_action( 'admin_menu', 'pbext_add_admin_menu' );
add_action( 'admin_init', 'pbext_settings_init' );

function pbext_settings_init(  ) { 

	register_setting( 'pbextPage', 'pbext_settings' );

	add_settings_section(
		'pbext_pbextPage_section', 
		__( '', 'extensions' ), 
		'pbext_settings_section_callback', 
		'pbextPage'
	);

	add_settings_field( 
		'pbext_whitelabel_field', 
		__( 'White Label', 'extensions' ), 
		'pbext_whitelabel_field_render', 
		'pbextPage', 
		'pbext_pbextPage_section' 
	);


}


function pbext_whitelabel_field_render(  ) { 

	$options = get_option( 'pbext_settings' );
	?>
	<input type='text' name='pbext_settings[pbext_whitelabel_field]' value='<?php echo $options['pbext_whitelabel_field']; ?>'>
	<?php

}


function pbext_settings_section_callback(  ) { 

	echo __( '<h3>Branding</h3>', 'extensions' );

}


$options = get_option( 'pbext_settings' );
$branding = $options['pbext_whitelabel_field'];
if ($branding == '') {
    define( 'ADDON', 'Beaverlodge' );
} else {
    define( 'ADDON', $branding );
}