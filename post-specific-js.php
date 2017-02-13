<?php /*
Plugin Name: Post-Specific JavaScripts
Plugin URI: https://github.com/StephenWidom/post-specific-js
Description: Adds a meta box to each post and page for javascript to be executed only on said post or page
Version: 1.0
Author: Stephen Widom
License: GPL
 */

add_action('add_meta_boxes', 'jspp_meta_box_add');

function jspp_meta_box_add() {

    if (current_user_can('administrator'))
        add_meta_box('jspp-meta-box-id', 'Post-Specific JavaScripts', 'jspp_meta_display', array('page', 'post', 'news', 'surveys'), 'normal', 'high');

}

function jspp_meta_display($object, $box) {

    wp_nonce_field(basename(__FILE__), 'jspp_post_class_nonce'); ?>

    <p>
        <label for="jspp-post-class">Paste JavaScript that pertains only to this particular post here, omitting the <code>&lt;script&gt;</code> tags, as well as the jQuery document ready conditional.<br /><br />
        <code>jQuery(document).ready(function($){</code>
        <textarea name="jspp-post-class" style="padding-left: 1em; font-family: monospace; width: 100%; min-height: 9em;" id="jspp-post-class"><?php echo get_post_meta($object->ID, 'jspp_post_class', true); ?></textarea>
        <code>});</code>
    </p>

<?php }

add_action('save_post', 'jspp_save_post_class_meta', 10, 2);

function jspp_save_post_class_meta($post_id, $post) {

    if (!isset($_POST['jspp_post_class_nonce']) || !wp_verify_nonce($_POST['jspp_post_class_nonce'], basename(__FILE__)))
        return $post_id;

    $post_type = get_post_type_object($post->post_type);

    if (!current_user_can('administrator'))
        return $post_id;

    $new_meta_value = (isset($_POST['jspp-post-class']) ? $_POST['jspp-post-class'] : '');
    $meta_key = 'jspp_post_class';
    $meta_value = get_post_meta($post_id, $meta_key, true);

    if ($new_meta_value && '' == $meta_value)
        add_post_meta($post_id, $meta_key, $new_meta_value, true);

    elseif ($new_meta_value && $new_meta_value != $meta_value)
        update_post_meta($post_id, $meta_key, $new_meta_value);

    elseif ('' == $new_meta_value && $meta_value)
        delete_post_meta($post_id, $meta_key, $meta_value);

}

add_action('wp_footer', 'get_jspp', 33);

function get_jspp() {

    global $post;

    if ($javascript = get_post_meta($post->ID, 'jspp_post_class', true)) { ?>
    <script>
        jQuery(document).ready(function($){
<?php
        echo $javascript;
?>
        });
    </script>
<?php
    }

}

