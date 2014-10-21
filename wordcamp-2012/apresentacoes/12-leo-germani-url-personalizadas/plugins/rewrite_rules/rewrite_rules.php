<?php
/*
Plugin Name: Rewrite Rules!!
Description: Apresentação de Rewrite Rules no WordCamp Curitiba
Author: Leo Germani
Version: 1.0
Author URI: http://hacklab.com.br/
*/

add_filter('query_vars', 'wordcamp_rewrite_custom_query_vars');
add_action('template_redirect', 'wordcamp_rewrite_template_redirect_intercept');

/*
add_filter('rewrite_rules_array', 'wordcamp_rewrite_custom_url_rewrites', 10, 1);

function wordcamp_rewrite_custom_url_rewrites($rules) {
    $new_rules = array(
        "minha-url/?$" => "index.php?wordcamp_url=minhaurl",
    );
    return $new_rules + $rules;
}
*/


add_action('generate_rewrite_rules', 'wordcamp_rewrite_custom_url_rewrites', 10, 1);

function wordcamp_rewrite_custom_url_rewrites($wp_rewrite) {
    $new_rules = array(
        "minha-url/([^/]+)/?$" => "index.php?wordcamp_url=" . $wp_rewrite->preg_index(1),
    );
    $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}


function wordcamp_rewrite_custom_query_vars($public_query_vars) {
    $public_query_vars[] = "wordcamp_url";
    return $public_query_vars;
}

function wordcamp_rewrite_template_redirect_intercept() {
    global $wp_query;
    
/*    
    if ( $wp_query->get('wordcamp_url') == 'minhaurl' ) {
        if (file_exists(dirname(__FILE__) . '/meu_template.php')) {
            require('meu_template.php');
            die;
        }
    }
    
 */   
    
    if ( $wp_query->get('wordcamp_url') ) {
        
        if (file_exists(dirname(__FILE__) . '/' . $wp_query->get('wordcamp_url') . '.php')) {
            require($wp_query->get('wordcamp_url') . '.php');
            die;
        }
        
    }
    
}

?>
