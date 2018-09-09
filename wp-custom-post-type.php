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

    add_meta_box("cpt-author", "Choose Author", "wpl_owt_cpt_author_call", "movie", "side", "high");
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

function wpl_owt_cpt_custom_columns($columns) {

    $columns = array(
        "cb" => "<input type='checkbox'/>",
        "title" => "Movie Title",
        "pub_email" => "Publisher Email",
        "pub_name" => "Publisher Name",
        "date" => "Date"
    );

    return $columns;
}

add_action("manage_movie_posts_columns", "wpl_owt_cpt_custom_columns");

function wpl_owt_cpt_custom_columns_data($column, $post_id) {

    switch ($column) {

        case 'pub_email':
            $publisher_email = get_post_meta($post_id, "wpl_producer_email", true);
            echo $publisher_email;
            break;
        case 'pub_name':
            $publisher_name = get_post_meta($post_id, "wpl_producer_name", true);
            echo $publisher_name;
            break;
    }
}

add_action("manage_movie_posts_custom_column", "wpl_owt_cpt_custom_columns_data", 10, 2);

add_filter("manage_edit-movie_sortable_columns", "wpl_owt_cpt_sortable_columns");

function wpl_owt_cpt_sortable_columns($columns) {

    $columns['pub_email'] = "owt_email";
    $columns["pub_name"] = "owt_name";

    return $columns;
}

function wpl_owt_cpt_author_call($post) {
    ?>
    <div>
        <label>Select Author</label>
        <select name='ddauthor'>
            <?php
            $users = get_users(array(
                "role" => "author"
            ));

            $saved_author_id = get_post_meta($post->ID, "author_id_movie", true);

            foreach ($users as $index => $user) {
                $selected = '';
                if ($saved_author_id == $user->ID) {
                    $selected = 'selected="selected"';
                }
                ?>
                <option value='<?php echo $user->ID ?>' <?php echo $selected; ?>><?php echo $user->display_name; ?></option>
                <?php
            }
            ?>
        </select>
    </div>
    <?php
}

add_action("save_post", "wpl_owt_save_author_movie", 10, 2);

function wpl_owt_save_author_movie($post_id, $post) {

    $author_id = isset($_REQUEST['ddauthor']) ? intval($_REQUEST['ddauthor']) : "";

    update_post_meta($post_id, "author_id_movie", $author_id);
}

add_action("restrict_manage_posts", "wpl_owt_author_filter_box_layout");

function wpl_owt_author_filter_box_layout() {

    global $typenow;
    if ($typenow == "movie") {

        $author_id = isset($_GET['filter_by_author']) ? intval($_GET['filter_by_author']) : "";

        wp_dropdown_users(array(
            "show_option_none" => "Select author",
            "role" => "author",
            "name" => "filter_by_author",
            "id" => "ddfilterauthorid",
            "selected" => $author_id
        ));
    }
}

add_filter("parse_query", "wpl_owt_filter_by_author");

function wpl_owt_filter_by_author($query) {

    global $typenow;
    global $pagenow;

    $author_id = isset($_GET['filter_by_author']) ? intval($_GET['filter_by_author']) : "";

    if ($typenow == "movie" && $pagenow == "edit.php" && !empty($author_id)) {

        $query->query_vars["meta_key"] = "author_id_movie";
        $query->query_vars["meta_value"] = $author_id;
    }
}

add_action('init', 'wpl_owt_create_movies_category');

function wpl_owt_create_movies_category() {
    register_taxonomy(
            'movie_category', 'movie', array(
        'label' => __('Movie Category'),
        'rewrite' => array('slug' => 'movie_category'),
        'hierarchical' => true,
            )
    );
}

add_action("restrict_manage_posts", "wpl_owt_category_filter_box");

function wpl_owt_category_filter_box() {

    global $typenow;
    $show_taxonomy = "movie_category";

    if ($typenow == "movie") {

        $selected_movie_category_id = isset($_GET[$show_taxonomy]) ? intval($_GET[$show_taxonomy]) : "";

        wp_dropdown_categories(array(
            "show_option_all" => "Show All",
            "name" => $show_taxonomy,
            "selected" => $selected_movie_category_id,
            "taxonomy" => $show_taxonomy,
            "show_count" => true
        ));
    }
}

add_filter("parse_query", "wpl_owt_parse_category_fn");

function wpl_owt_parse_category_fn($query) {

    global $typenow;
    global $pagenow;
    $post_type = "movie";
    $taxonomy = "movie_category";

    $query_variables = &$query->query_vars;

    if ($typenow == $post_type && $pagenow == "edit.php" && isset($query_variables[$taxonomy]) && is_numeric($query_variables[$taxonomy])) {

        $term_details = get_term_by("id", $query_variables[$taxonomy], $taxonomy);

        $query_variables[$taxonomy] = $term_details->slug;
    }
}
