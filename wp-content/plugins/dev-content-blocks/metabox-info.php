<?php
defined( 'ABSPATH' ) or die( 'Time for a U turn!' );

add_action('add_meta_boxes', 'dc_dcb_prepend_info_metabox', 10, 2);

function dc_dcb_prepend_info_metabox() {
	add_meta_box('cb-shortcodes', 'Content Block Shortcodes', 'dc_dcb_draw_shortcode_metabox', 'dev_content_block', 'info_metabox', 'high');
}

function dc_dcb_draw_shortcode_metabox(){
	global $post;
	$postID = $post->ID;
	$postName = $post->post_name;
	?>
	<script>
        function selectText( containerid ) {

            var node = document.getElementById( containerid );

            if ( document.selection ) {
                var range = document.body.createTextRange();
                range.moveToElementText( node  );
                range.select();
            } else if ( window.getSelection ) {
                var range = document.createRange();
                range.selectNodeContents( node );
                window.getSelection().removeAllRanges();
                window.getSelection().addRange( range );
            }
        }
	</script>
	<strong>Copy any one of these shortcodes and paste into a post or widget to display the content:</strong><br>
    <i>Recommended to use the first one (ID) which will not change if you change the slug the content block, however you can use the slug (name) if you prefer</i><br>
	<div class="copy_shortcodes">
		<div id="dc_dcb_selectable" onclick="selectText('dc_dcb_selectable')">[dcb id=<?php echo $postID; ?>]</div>
		<div id="dc_dcb_selectable2" onclick="selectText('dc_dcb_selectable2')">[dcb name=<?php echo $postName; ?>]</div>
        <div id="dc_dcb_selectable3" onclick="selectText('dc_dcb_selectable3')">[dcb slug=<?php echo $postName; ?>]</div>
        <br><strong>To use in your theme:</strong>
        <div id="dc_dcb_selectable4" onclick="selectText('dc_dcb_selectable4')"><&#63;php if(function_exists('dc_dcb_dev_content_block'))echo do_shortcode('[dcb id=<?php echo $postID; ?>]'); ?></div>
        <div id="dc_dcb_selectable5" onclick="selectText('dc_dcb_selectable5')"><&#63;php if(function_exists('dc_dcb_dev_content_block'))echo do_shortcode('[dcb name=<?php echo $postName; ?>]'); ?></div>
        <div id="dc_dcb_selectable6" onclick="selectText('dc_dcb_selectable6')"><&#63;php if(function_exists('dc_dcb_dev_content_block'))echo do_shortcode('[dcb slug=<?php echo $postName; ?>]'); ?></div>
	</div>
	<?php
}

function dc_dcb_info_move_deck() {
	# Get the globals:
	global $post, $wp_meta_boxes;

	# Output the "advanced" meta boxes:
	do_meta_boxes( get_current_screen(), 'info_metabox', $post );

	# Remove the initial "advanced" meta boxes:
	unset($wp_meta_boxes['post']['info_metabox']);
}

add_action('edit_form_after_title', 'dc_dcb_info_move_deck');