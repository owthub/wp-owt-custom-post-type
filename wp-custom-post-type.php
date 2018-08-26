<?php
/*
  Plugin Name: WP Custom Post Type
  Description: This is a simple plugin for purpose of learning about wordpress CPT
  Version: 1.0.0
  Author: Online Web Tutor
 */

add_action('init', 'wpl_owt_cpt_register_movies');

function wpl_owt_cpt_register_movies() {
    $labels = array(
        'name' => _x('Movies'),
        'singular_name' => __('Movie'),
        'menu_name' => __('Movies'),
        'name_admin_bar' => __('Movie'),
        'add_new' => __('Add New'),
        'add_new_item' => __('Add New Movie'),
        'new_item' => __('New Movie'),
        'edit_item' => __('Edit Movie'),
        'view_item' => __('View Movie'),
        'all_items' => __('All Movies'),
        'search_items' => __('Search Movies'),
        'parent_item_colon' => __('Parent Movies:'),
        'not_found' => __('No movies found.'),
        'not_found_in_trash' => __('No movies found in Trash.')
    );

    $args = array(
        'labels' => $labels,
        'description' => __('Description.'),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'book'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
    );

    register_post_type('movie', $args);
}

function wpl_owt_cpt_register_metabox() {

    add_meta_box("cpt-id", "Producer Details", "wpl_owt_cpt_producer_call", "movie", "side", "high");
}

add_action("add_meta_boxes", "wpl_owt_cpt_register_metabox");

function wpl_owt_cpt_producer_call($post) {
    ?>
    <p>
        <label>Name:</label>
        <?php $name = get_post_meta($post->ID, "wpl_producer_name", true) ?>
        <input type="text" value="<?php echo $name; ?>" name="txtProducerName" placeholder="Name"/>
    </p>
    <p>
        <label>Email:</label>
        <?php $email = get_post_meta($post->ID, "wpl_producer_email", true) ?>
        <input type="email" value="<?php echo $email; ?>" name="txtProducerEmail" placeholder="Email"/>
    </p>
    <?php
}

function wpl_owt_cpt_save_values($post_id, $post) {


    $txtProducerName = isset($_POST['txtProducerName']) ? $_POST['txtProducerName'] : "";
    $txtProducerEmail = isset($_POST['txtProducerEmail']) ? $_POST['txtProducerEmail'] : "";

    update_post_meta($post_id, "wpl_producer_name", $txtProducerName);
    update_post_meta($post_id, "wpl_producer_email", $txtProducerEmail);
}

add_action("save_post", "wpl_owt_cpt_save_values", 10, 2);