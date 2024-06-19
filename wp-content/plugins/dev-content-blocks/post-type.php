<?php
defined( 'ABSPATH' ) or die( 'Time for a U turn!' );

if ( ! function_exists('dc_dcb_dev_content_block_cpt') ) {

// Register Custom Post Type
	function dc_dcb_dev_content_block_cpt() {

		$labels = array(
			'name'                  => _x( 'Dev Content Blocks', 'Post Type General Name', 'dc' ),
			'singular_name'         => _x( 'Dev Content Block', 'Post Type Singular Name', 'dc' ),
			'menu_name'             => __( 'Dev Content Blocks', 'dc' ),
			'name_admin_bar'        => __( 'Dev Content Blocks', 'dc' ),
			'archives'              => __( 'Dev Content Blocks', 'dc' ),
			'attributes'            => __( 'Content Block Attributes', 'dc' ),
			'parent_item_colon'     => __( 'Parent Item:', 'dc' ),
			'all_items'             => __( 'All Content Blocks', 'dc' ),
			'add_new_item'          => __( 'Add New Content Block', 'dc' ),
			'add_new'               => __( 'Add New Content Block', 'dc' ),
			'new_item'              => __( 'New Content Block', 'dc' ),
			'edit_item'             => __( 'Edit Content Block', 'dc' ),
			'update_item'           => __( 'Update Content Block', 'dc' ),
			'view_item'             => __( 'View Content Block', 'dc' ),
			'view_items'            => __( 'View Content Blocks', 'dc' ),
			'search_items'          => __( 'Search Dev Content Blocks', 'dc' ),
			'not_found'             => __( 'Not found', 'dc' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'dc' ),
			'featured_image'        => __( 'Featured Image', 'dc' ),
			'set_featured_image'    => __( 'Set featured image', 'dc' ),
			'remove_featured_image' => __( 'Remove featured image', 'dc' ),
			'use_featured_image'    => __( 'Use as featured image', 'dc' ),
			'insert_into_item'      => __( 'Insert into item', 'dc' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'dc' ),
			'items_list'            => __( 'Items list', 'dc' ),
			'items_list_navigation' => __( 'Items list navigation', 'dc' ),
			'filter_items_list'     => __( 'Filter items list', 'dc' ),
		);
		$args = array(
			'label'                 => __( 'Dev Content Block', 'dc' ),
			'description'           => __( 'Dev Content Blocks', 'dc' ),
			'labels'                => $labels,
			'supports'              => array( 'editor', 'title', 'thumbnail', 'revisions' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 80,
			'menu_icon'             => 'dashicons-grid-view',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);

		register_post_type( 'dev_content_block', $args );

	}
	add_action( 'init', 'dc_dcb_dev_content_block_cpt', 0 );

}

// Use clean template
function dc_dcb_template($single) {

	global $post;


	/* Checks for single template by post type */
	if ( $post->post_type == 'dev_content_block' ) {
		if ( file_exists( dirname( __FILE__ ) . '/single.php' ) ) {
			return dirname( __FILE__ ) . '/single.php';
		}
	}

	return $single;

}
add_filter('single_template', 'dc_dcb_template', 99);

// Force 404 for non authors
function dc_dcb_force_404()
{
	global $post;
	if ( !is_admin() && $post->post_type == 'dev_content_block' ) {
		if ( ! current_user_can( 'level_2' ) ) {
			status_header( 404 );
			nocache_headers();
			include( get_query_template( '404' ) );
			die();
		}
	}
}
add_action( 'wp', 'dc_dcb_force_404' );

function dc_dcb_register_dcb_scripts(){
	if ( get_post_type() == 'dev_content_block' ) {
		wp_register_script( 'ace', plugin_dir_url( __FILE__ ) . 'ace/src-min-noconflict/ace.js', 'jquery', DC_DCB_VERSION, true );
		wp_register_script( 'ace_ext_language_tools', plugin_dir_url( __FILE__ ) . 'ace/src-min-noconflict/ext-language_tools.js', 'jquery', DC_DCB_VERSION, true );
		wp_register_script( 'dcb_scripts', plugin_dir_url( __FILE__ ) . 'js/scripts.js', 'jquery', DC_DCB_VERSION, true );
		wp_register_style( 'jquery_ui_css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', '', DC_DCB_VERSION, 'all' );
		wp_register_style( 'dcb_styles', plugin_dir_url( __FILE__ ) . 'css/styles.css', '', DC_DCB_VERSION, 'all' );

		wp_enqueue_script( 'ace' );
		wp_enqueue_script( 'ace_ext_language_tools' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'dcb_scripts' );
		wp_enqueue_style( 'jquery_ui_css' );
		wp_enqueue_style( 'dcb_styles' );
	}
}
add_action( 'admin_enqueue_scripts', 'dc_dcb_register_dcb_scripts', 1);