<?php
/*
Plugin Name: Movies
Description: Plugin de exemplo pra testar capabilities
Author: Justin Tadlock
Version: 1.0
Author URI: http://justintadlock.com/

Exemplo extraído desse post:
http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types

*/



add_action( 'init', 'create_my_post_types' );

function create_my_post_types() {
    $labels = array(
    'name' => _x('Movies', 'post type general name'),
    'singular_name' => _x('Movie', 'post type singular name'),
    'add_new' => _x('Add New', 'book'),
    'add_new_item' => __('Add New Movie'),
    'edit_item' => __('Edit Movie'),
    'new_item' => __('New Movie'),
    'all_items' => __('All Movies'),
    'view_item' => __('View Movie'),
    'search_items' => __('Search Movies'),
    'not_found' =>  __('No Movies found'),
    'not_found_in_trash' => __('No Movies found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => 'Movies'

    );

    register_post_type(
		'movie',
		array(
			'public' => true,
            'labels' => $labels,
			'capability_type' => 'movie',
			'capabilities' => array(
				'publish_posts' => 'publish_movies',
				'edit_posts' => 'edit_movies',
				'edit_others_posts' => 'edit_others_movies',
				'delete_posts' => 'delete_movies',
				'delete_others_posts' => 'delete_others_movies',
				'read_private_posts' => 'read_private_movies',
				//meta caps
                'edit_post' => 'edit_movie',
				'delete_post' => 'delete_movie',
				'read_post' => 'read_movie',
			),
		)
	);
}


register_activation_hook(__FILE__, function() {
    
    $roles = array('administrator', 'editor', 'contributor');
    
    foreach ($roles as $role) {
        $r = get_role($role);
        $r->add_cap('publish_movies');
        $r->add_cap('edit_movies');
        $r->add_cap('delete_movies');
        $r->add_cap('read_private_movies');
        
        if ($role == 'administrator') {
            $r->add_cap('delete_others_movies');
            $r->add_cap('edit_others_movies');
        }
    }

});


add_filter( 'map_meta_cap', 'my_map_meta_cap', 10, 4 );

function my_map_meta_cap( $caps, $cap, $user_id, $args ) {

	/* If editing, deleting, or reading a movie, get the post and post type object. */
	if ( 'edit_movie' == $cap || 'delete_movie' == $cap || 'read_movie' == $cap ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post->post_type );

		/* Set an empty array for the caps. */
		$caps = array();
	}

	/* If editing a movie, assign the required capability. */
	if ( 'edit_movie' == $cap && !current_user_can($post_type->cap->edit_others_posts) {
		if ( get_user_meta($user_id, 'acendencia', true) == 'holandes' && date('Hi') > 1200 )
			$caps[] = 'not_allow'; // qualquer permissão que não exista
		elseif($user_id == $post->post_author)
            $caps[] = $post_type->cap->edit_posts;
			
	}

	/* If deleting a movie, assign the required capability. */
	elseif ( 'delete_movie' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->delete_posts;
		else
			$caps[] = $post_type->cap->delete_others_posts;
	}

	/* If reading a private movie, assign the required capability. */
	elseif ( 'read_movie' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[] = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
		else
			$caps[] = $post_type->cap->read_private_posts;
	}

	/* Return the capabilities required by the user. */
	return $caps;
}
