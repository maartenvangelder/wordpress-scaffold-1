<?php

namespace Grrr\Utils\Extras;

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');

/**
 * Move Yoast to bottom
 */
function yoasttobottom() {
    return 'low';
}
add_filter('wpseo_metabox_prio', __NAMESPACE__ . '\\yoasttobottom');

/**
 * Allow svg uploads
 */
function mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', __NAMESPACE__ . '\\mime_types');

/**
 * Clear W3 Total Cache when publishing any 'post'.
 * Executes AFTER save_post hook (else use 1 as priority).
 */
function custom_cache_clearing($post_id) {
    if (!defined('DOING_AUTOSAVE') && function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }
    return $post_id;
}
add_action('save_post', __NAMESPACE__ . '\\custom_cache_clearing', 20);
add_action('acf/save_post', __NAMESPACE__ . '\\custom_cache_clearing', 20);

/**
* Overrides the function user_can_richedit and only checks the
* user preferences instead of doing UA sniffing.
* @see: https://benjaminhorn.io/code/wordpress-visual-editor-not-visible-because-of-user-agent-sniffing/
*/
function user_can_richedit_custom() {
    global $wp_rich_edit;
    if (get_user_option('rich_editing') == 'true' || !is_user_logged_in()) {
        $wp_rich_edit = true;
        return true;
    }
    $wp_rich_edit = false;
    return false;
}

add_filter('user_can_richedit', __NAMESPACE__ . '\\user_can_richedit_custom');

/**
 * Admin footer timing
 */
function my_admin_footer_function() {
    ?>
    <!--<?php echo get_num_queries(); ?> queries in <?php timer_stop(1); ?> seconds.-->
    <?php
}
add_action('admin_footer-index.php', __NAMESPACE__ . '\\my_admin_footer_function');

/**
 * Add term query argument to get terms for a specific post type
 * Based on https://1fix.io/blog/2016/10/25/get-terms-cpt-taxonomy
 */
function my_terms_clauses( $clauses, $taxonomy, $args) {
    if (empty($args['post_type'])) {
        return $clauses;
    }
    global $wpdb;
    $post_types = is_array($args['post_type'])
        ? implode("','", $args['post_type'])
        : $args['post_type'];

    $clauses['join'] .= " INNER JOIN $wpdb->term_relationships AS r"
        . " ON r.term_taxonomy_id = tt.term_taxonomy_id"
        . " INNER JOIN $wpdb->posts AS p ON p.ID = r.object_id";
    $clauses['where'] .= " AND p.post_type IN ('". esc_sql( $post_types ). "') GROUP BY t.term_id";
    return $clauses;
}
add_filter('terms_clauses', __NAMESPACE__ . '\\my_terms_clauses', 99999, 3);

/**
 * Set max srcset size
 */
function set_max_srcset(int $max, array $sizes) {
    return 2560;
}
add_filter('max_srcset_image_width', __NAMESPACE__ . '\\set_max_srcset', 10, 2);
