<?php
/**
 * Plugin Name: Digital Contact Dev
 * Description: Default development enhancements used by Digital Contact. Simple extras like disabling the admin bar (?noadminbar) and disabling autoptimize (?nooptimize) using query strings on any page. Also adds an option to add a body class to all post types as well as enqueuing a custom style per page for all post types. And more...
 * Version: 1
 * Author: Allon Sacks
 * Author URI: http://www.digitalcontact.co.il
 * License: GPL2
 */
defined( 'ABSPATH' ) or die( 'Time for a U turn!' );
define( "DC_DCD_VERSION", "1" );

# Disable admin bar using query string
if ( isset( $_GET['noadminbar'] ) ) {
	add_filter( 'show_admin_bar', '__return_false' );
}

# Disable autoptimize with query string
if ( class_exists( 'autoptimizeCache' ) ) {

	add_filter( 'autoptimize_filter_noptimize', 'dc_ao_noptimize', 10, 0 );
	function dc_ao_noptimize() {
		if ( $_GET['nooptimize'] == true ) {
			return true;
		} else {
			return false;
		}
	}

# Automatically clear autoptimizeCache if above 300 MB
	$myMaxSize = 300000;
	$statArr   = autoptimizeCache::stats();
	$cacheSize = round( $statArr[1] / 1024 );

	if ( $cacheSize > $myMaxSize ) {
		autoptimizeCache::clearall();
		header( "Refresh:0" );
	}
}

# Create meta fields for body classes and for custom CSS files

add_action('add_meta_boxes', 'dc_dcd_add_metaboxes', 10, 2);
add_action('save_post', 'dc_dcd_save_custom_metaboxes');
function dc_dcd_add_metaboxes() {
	global $post;
	if($post) {
		$post_types = array();
		foreach ( get_post_types( '', 'names' ) as $post_type ) {
			if ( $post_type ) {
				$post_types[] = $post_type;
			}
		}
		if ( $post_types ) {
			foreach ( $post_types as $type ) {
				add_meta_box( 'dc_dcd_custom_fields', 'Digital Contact custom fields', 'dc_dcd_draw_custom_metaboxes', $type, 'side', 'default' );
			}
		}
	}
}

function dc_dcd_draw_custom_metaboxes($post) {
	global $post;
	if($post) {
		$data              = get_post_custom( $post->ID );
		$dc_dcd_classes    = isset( $data['dc_dcd_body_classes'] ) ? esc_attr( $data['dc_dcd_body_classes'][0] ) : '';
		$dc_dcd_custom_css = isset( $data['dc_dcd_custom_css'] ) ? esc_attr( $data['dc_dcd_custom_css'][0] ) : '';

		wp_nonce_field( 'dc_dcd_draw_custom_metaboxes_nonce', 'draw_dc_dcd_custom_metabox_nonce' );
		?>
		<p>Add classes separated by a space. These classes will be added to the body tag</p>
		<p><label for="body_classes"><?php esc_attr_e( 'List of classes', 'dc_dcd_dev' ); ?></label>
			<input type="text" name="dc_dcd_body_classes" id="body_classes" placeholder="classes" value="<?php echo $dc_dcd_classes ?>">
		</p>

		<p>Add path to stylesheet files (within theme folder) to be enqueued separated by a space. These stylesheets
			will be added to the body tag</p>
		<p><label for="dc_dcd_custom_css"><?php esc_attr_e( 'List of stylesheets', 'dc_dcd_dev' ); ?></label>
			<input type="text" name="dc_dcd_custom_css" id="dc_dcd_custom_css" placeholder="/custom/css/filename.css" value="<?php echo $dc_dcd_custom_css ?>">
		</p>

		<?php
	}
}

function dc_dcd_save_custom_metaboxes($page_id) {
	global $post;
	if($post) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['draw_dc_dcd_custom_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['draw_dc_dcd_custom_metabox_nonce'], 'dc_dcd_draw_custom_metaboxes_nonce' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_pages', $page_id ) ) {
			return;
		}

		if ( isset( $_POST['dc_dcd_body_classes'] ) ) {
			update_post_meta( $page_id, 'dc_dcd_body_classes', strip_tags( $_POST['dc_dcd_body_classes'] ) );
		}

		if ( isset( $_POST['dc_dcd_custom_css'] ) ) {
			update_post_meta( $page_id, 'dc_dcd_custom_css', strip_tags( $_POST['dc_dcd_custom_css'] ) );
		}
	}
}

# Check if custom class is set

function dc_custom_body_class( $classes ) {
	global $post;
	if($post) {
		$dcpostid             = $post->ID;
		$dc_custom_page_class = get_post_meta( $dcpostid, 'dc_dcd_body_classes', true );

		if ( $dc_custom_page_class != '' ) {
			$classes[] = $dc_custom_page_class;
		}

		return $classes;
	}
}

function body_class_action() {
	global $post;
	if($post) {
		add_filter( 'body_class', 'dc_custom_body_class' );
	}
}

add_action( 'wp_head', 'body_class_action' );

# Check if custom style is set
function dc_dcd_custom_enqueue() {
	global $post;
	if($post) {

		if ( get_post_meta( $post->ID, 'dc_dcd_custom_css', true ) ) {
			$headerFiles = get_post_meta( $post->ID, 'dc_dcd_custom_css', true );
			$headerFiles = explode( ' ', $headerFiles );
			foreach ( $headerFiles as $headerFile ) {
				wp_register_style( $headerFile . '-page-css', get_stylesheet_directory_uri() . '/' . $headerFile, null, null, 'all' );
				wp_enqueue_style( $headerFile . '-page-css' );
			}
		}
	}
}

add_action( 'wp_enqueue_scripts', 'dc_dcd_custom_enqueue', 100 );
