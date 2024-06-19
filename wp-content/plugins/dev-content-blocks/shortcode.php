<?php
defined( 'ABSPATH' ) or die( 'Time for a U turn!' );

function dc_dcb_dev_content_block($args = array()) {

	$defaults = array(
		'id' => '',
		'slug' => '',
		'name' => ''
	);
	$args = wp_parse_args($args, $defaults);

	if ( $args['slug'] != '' && $post = get_page_by_path( $args['slug'], OBJECT, 'dev_content_block' ) ){
		$args['id'] = $post->ID;
		$cb_content = $post->post_content;
	} elseif ( $args['name'] != '' && $post = get_page_by_path( $args['name'], OBJECT, 'dev_content_block' ) ){
		$args['id'] = $post->ID;
		$cb_content = $post->post_content;
	} else {
		$cb_content = get_post_field( 'post_content', $args['id'] );
	}

	$data = get_post_custom($args['id']);
	$dc_dcb_html = isset($data['dc_dcb_html']) ? html_entity_decode(esc_html($data['dc_dcb_html'][0]), ENT_QUOTES) : '';
	$dc_dcb_css = isset($data['dc_dcb_css']) ? html_entity_decode(esc_html($data['dc_dcb_css'][0]), ENT_QUOTES) : '';
	$dc_dcb_js = isset($data['dc_dcb_js']) ? html_entity_decode(esc_html($data['dc_dcb_js'][0]), ENT_QUOTES) : '';

	ob_start();

	$dcb_show_post = isset($data['dc_dcb_show_post']) ? $data['dc_dcb_show_post'][0] : '';
	if($dcb_show_post == 'on'){
		echo apply_filters( 'the_content', do_shortcode($cb_content));
	}

	if($dc_dcb_html != '') {
		?>
		<?php
		echo do_shortcode($dc_dcb_html);
	}


	if($dc_dcb_css != '') {
		?><style><?php
		echo $dc_dcb_css;
		?></style><?php
	}

	if($dc_dcb_js != '') {
		?><script>
            if(typeof(jQuery) !== 'undefined') {
                $ = jQuery.noConflict();
            }
			<?php
			echo $dc_dcb_js;
			?></script><?php
	}

	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

add_shortcode( 'dcb', 'dc_dcb_dev_content_block' );