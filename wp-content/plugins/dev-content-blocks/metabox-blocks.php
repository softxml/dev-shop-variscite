<?php
defined( 'ABSPATH' ) or die( 'Time for a U turn!' );

add_action('add_meta_boxes', 'dc_dcb_prepend_page_metaboxes', 10, 2);
add_action('save_post', 'dc_dcb_prepend_save_display_metabox');

function dc_dcb_prepend_page_metaboxes() {
	add_meta_box('content-code', 'Content Block Code', 'dc_dcb_prepend_draw_display_metabox', 'dev_content_block', 'content-code', 'high');
}

function dc_dcb_prepend_draw_display_metabox($post) {
	global $post;
	if ( current_user_can( 'unfiltered_html' ) ) {
		$data        = get_post_custom( $post->ID );
		$dc_dcb_html = isset( $data['dc_dcb_html'] ) ? esc_html($data['dc_dcb_html'][0]) : '';
		$dc_dcb_css  = isset( $data['dc_dcb_css'] ) ? esc_html( $data['dc_dcb_css'][0] ) : '';
		$dc_dcb_js   = isset( $data['dc_dcb_js'] ) ? esc_html( $data['dc_dcb_js'][0] ) : '';

		wp_nonce_field( 'dc_dcb_prepend_display_metabox_nonce', 'dc_dcb_display_metabox_nonce' );
		?>

        <style>
            .dc_dcb_editor_container {
                width: 100%;
                height: 250px;
                position: relative;
                max-width: 100%;
                min-width: 100%;
            }
            .dc_dcb_cb_ace_editor {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                outline: 1px solid #d0d0d0;
            }
            .dc_dcb_mask.scrolling {
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 999;
            }
        </style>
        <div class="dc_dcb_mask"></div>
        <p>
            <label for="dc_dcb_html_editor" style="font-weight: bold;"><?php esc_attr_e( 'HTML Code', 'dev_cb' ); ?></label>
        </p>
        <p>Please Note. This plugin lets you use raw HTML, JS, and CSS therefore be careful if copying and pasting from random web pages as in order to allow you maximum control with this plugin, you will be able to paste JS that is not entirely validated.</p>
        <div class="dc_dcb_editor_container dc_dcb_html_editor_container">
            <div id="dc_dcb_html_editor" class="dc_dcb_cb_ace_editor"></div>
        </div>
		<?php
		?>
        <textarea title="HTML Code" style="display:none;" name="dc_dcb_html" id="dc_dcb_html"><?php echo $dc_dcb_html ?></textarea>

        <p>
            <label for="dc_dcb_css_editor" style="font-weight: bold;"><?php esc_attr_e( 'CSS Code', 'dev_cb' ); ?></label>
        </p>
        <div class="dc_dcb_editor_container dc_dcb_css_editor_container">
            <div id="dc_dcb_css_editor" class="dc_dcb_cb_ace_editor"></div>
        </div>
        <textarea title="CSS Code" style="display:none;" name="dc_dcb_css" id="dc_dcb_css"><?php echo $dc_dcb_css ?></textarea>

        <p>
            <label for="dc_dcb_js_editor" style="font-weight: bold;"><?php esc_attr_e( 'JS Code', 'dev_cb' ); ?></label>
        </p>
        <div class="dc_dcb_editor_container dc_dcb_js_editor_container">
            <div id="dc_dcb_js_editor" class="dc_dcb_cb_ace_editor"></div>
        </div>
        <textarea title="JS Code" style="display:none;" name="dc_dcb_js" id="dc_dcb_js"><?php echo $dc_dcb_js; ?></textarea>

		<?php
	}

}

function dc_dcb_prepend_save_display_metabox($page_id) {
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if(!isset($_POST['dc_dcb_display_metabox_nonce']) || !wp_verify_nonce($_POST['dc_dcb_display_metabox_nonce'], 'dc_dcb_prepend_display_metabox_nonce' )) return;
	if(!current_user_can('edit_pages', $page_id)) return;

	if(isset($_POST['dc_dcb_html'])) {
		$dc_dcb_html = $_POST['dc_dcb_html'];
		$dc_dcb_html = trim($dc_dcb_html);
		$dc_dcb_html = apply_filters( 'format_to_edit', $dc_dcb_html );
		$dc_dcb_html = esc_textarea( $dc_dcb_html );
		update_post_meta($page_id, 'dc_dcb_html', $dc_dcb_html);
	}
	if(isset($_POST['dc_dcb_css'])) {
		$dc_dcb_css = $_POST['dc_dcb_css'];
		$dc_dcb_css = trim($dc_dcb_css);
		$dc_dcb_css = apply_filters( 'format_to_edit', $dc_dcb_css );
		$dc_dcb_css = esc_textarea( $dc_dcb_css );
		update_post_meta($page_id, 'dc_dcb_css', $dc_dcb_css);
	}
	if(isset($_POST['dc_dcb_js'])) {
		$dc_dcb_js = $_POST['dc_dcb_js'];
		$dc_dcb_js = trim($dc_dcb_js);
		$dc_dcb_js = apply_filters( 'format_to_edit', $dc_dcb_js );
		$dc_dcb_js = esc_textarea( $dc_dcb_js );
		update_post_meta($page_id, 'dc_dcb_js', $dc_dcb_js);
	}
}

function dc_dcb_blocks_move_deck() {
	# Get the globals:
	global $post, $wp_meta_boxes;

	# Output the "advanced" meta boxes:
	do_meta_boxes( get_current_screen(), 'content-code', $post );

	# Remove the initial "advanced" meta boxes:
	unset($wp_meta_boxes['post']['content-code']);
}

add_action('edit_form_after_title', 'dc_dcb_blocks_move_deck');