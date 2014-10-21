<?php
/*
Plugin Name: WordCamp Like!
Description: Plugin de exemplo pra testar capabilities
Author: Leo Germani
Version: 1.0
Author URI: http://hacklab.com.br/

http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types

*/



add_action('wp_print_scripts', function() {
    wp_enqueue_script('wordcamp_like', WP_CONTENT_URL . '/plugins/wordcamp_like/like.js', array('jquery'));
    wp_localize_script('wordcamp_like', 'wordcamp_like', array('ajaxurl' => admin_url('admin-ajax.php')));
});

add_filter('the_content', function($content) {

    global $post;
    
    $html = wordcamp_like_get_post_html($post->ID);
    
    return $html . $content;

});

function wordcamp_like_get_post_html($post_id) {

    if (!current_user_can('like_posts'))
        return '';
    
    $current_user = wp_get_current_user();
        
    $likes = get_post_meta($post_id, '_user_like');
    $totalLikes = is_array($likes) ? sizeof($likes) : 0;
    $jaCurtiu = is_array($likes) ? in_array($current_user->ID, $likes) : false;
    
    if (!$jaCurtiu) {
        $html = "<span class='wordcamp_like' data-post_id='{$post_id}' >Curtir</span>";
    } else {
        $html = "<span  >Já curtiu</span>";
    }
    
    $s = $totalLikes != 1 ? 's' : '';
    
    $html .= " | <span class='wordcamp_like_count' data-post_id='{$post_id}' >$totalLikes curtida$s</span>";
    
    $html = "<div class='wordcamp_like_wrapper' id='wordcamp_like_{$post_id}'>$html<hr/></div>";
    
    return $html;

}

add_action('wp_ajax_wordcamp_like', function() {

    if ( is_numeric($_POST['post_id']) ) {
        
        if (current_user_can('like_posts')) {
            
            $current_user = wp_get_current_user();
            add_post_meta($_POST['post_id'], '_user_like', $current_user->ID);
            echo wordcamp_like_get_post_html($_POST['post_id']);
            
        } 
        
        
    } else {
        
        echo 'erro';
    
    }
    
    die;

});

register_activation_hook( __FILE__, function() {
    
    $role = get_role('administrator');
    $role->add_cap('like_posts');
    
    $role = get_role('editor');
    $role->add_cap('like_posts');
    
    $role = get_role('contributor');
    $role->add_cap('like_posts');
    
    
} );



//mysql_query("DELETE FROM wp_postmeta WHERE meta_key = '_user_like'");
?>
