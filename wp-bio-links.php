<?php
/**
 * Plugin Name: WP Bio Links
 * Plugin URI:  https://www.guglielmopepe.com/#wp-bio-links
 * Description: Direct your visitors where you need they to go.
 * Version:     1.0.0
 * Author:      Guglielmo Pepe
 * Author URI:  https://www.guglielmopepe.com
 * License:     GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-bio-links
 * Domain Path: /languages
 *
 * WP Bio Links is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WP Bio Links is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Bio Links. If not, see https://www.gnu.org/licenses/gpl-2.0.html}.
 * 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


// unistall plugin
register_uninstall_hook( __FILE__, 'uninstall_wp_bio_links' );

function uninstall_wp_bio_links() {
    global $wpdb;
    $result = $wpdb->query( 
        $wpdb->prepare("
            DELETE posts,pt,pm
            FROM wp_posts posts
            LEFT JOIN wp_term_relationships pt ON pt.object_id = posts.ID
            LEFT JOIN wp_postmeta pm ON pm.post_id = posts.ID
            WHERE posts.post_type = %s
            ", 
            'wp-bio-links'
        ) 
    );

    $plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'wp_bio_links_%'" );

    foreach( $plugin_options as $option ) {
        delete_option( $option->option_name );
    }


    return $result!==false;
}


// activate plugin
register_activation_hook( __FILE__, 'activate_wp_bio_links' );

function activate_wp_bio_links() {
    // trigger our function that registers the custom post type
    wp_bio_links_post_type_setup();
 
    // clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}


// deactivate plugin
register_deactivation_hook( __FILE__, 'deactivate_wp_bio_links' );

function deactivate_wp_bio_links() {
    // unregister the post type, so the rules are no longer in memory
    unregister_post_type( 'wp-bio-links' );
    // clear the permalinks to remove our post type's rules from the database
    flush_rewrite_rules();
}


// setup custom post type
add_action( 'init', 'wp_bio_links_post_type_setup', 0 );

function wp_bio_links_post_type_setup() {

	$labels = array(
		'name'                  => _x( 'WP Bio Links', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'WP Bio Links', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'WP Bio Links', 'text_domain' ),
		'name_admin_bar'        => __( 'WP Bio Links', 'text_domain' ),
		'archives'              => __( 'Item Archives', 'text_domain' ),
		'attributes'            => __( 'Item Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Items', 'text_domain' ),
		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Item', 'text_domain' ),
		'edit_item'             => __( 'Edit Item', 'text_domain' ),
		'update_item'           => __( 'Update Item', 'text_domain' ),
		'view_item'             => __( 'View Item', 'text_domain' ),
		'view_items'            => __( 'View Items', 'text_domain' ),
		'search_items'          => __( 'Search Item', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$rewrite = array(
		'slug'                  => 'wp-bio-links',
		'with_front'            => true,
		'pages'                 => false,
		'feeds'                 => false,
	);
	$args = array(
		'label'                 => __( 'WP Bio Link Page', 'text_domain' ),
		'description'           => __( 'Direct your visitors where you need they to go.', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
        'menu_icon'             => 'dashicons-admin-links',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'rewrite'               => $rewrite,
		'capability_type'       => 'page',
	);

	register_post_type( 'wp-bio-links', $args );
}


// add meta box
add_action( 'add_meta_boxes', 'wp_bio_links_add_meta_box' );

function wp_bio_links_add_meta_box() {
    add_meta_box(
        'wp_bio_links_meta_box', // Unique ID
        'Items', // Box title
        'wp_bio_links_add_meta_box_html', // Content callback, must be of type callable
        'wp-bio-links' // Post type
    );
}


function wp_bio_links_add_meta_box_html( $post ) {
    $values = get_post_meta($post->ID, 'wp_bio_links', true);

    ?>
    <?php wp_nonce_field( basename( __FILE__ ), 'wp_bio_links_nonce' ); ?>

    <div id="wp_bio_links_nickname">
        <p>
            <label>Nickname</label><br>
            <input name="wp_bio_links[nickname]" type="text" value="<?php echo ( isset( $values['nickname'] ) && !empty( $values['nickname'] ) ? $values['nickname'] : '' ); ?>"><br />
        </p>
        <hr />
    </div>

    <div id="wp_bio_links_items">

    <?php if ( isset( $values['items'] ) ) : ?>

        <?php foreach ( $values['items'] as $key => $value ) : ?>

            <div class="postbox">
                <h2 style="background-color:#e9e9e9;">Link</h2>
                <div class="inside">

                    <p>
                        <label>Link text</label><br>
                        <input name="wp_bio_links[items][<?php echo $key; ?>][text]" type="text" value="<?php echo $value['text']; ?>"><br />
                    </p>
                    <p>
                        <label>Link url</label><br>
                        <input name="wp_bio_links[items][<?php echo $key; ?>][url]" type="text" value="<?php echo $value['url']; ?>"><br />
                    </p>
                    <hr />
                    <p>
                        <button type="button" class="wp_bio_links_item_move_down">Move Down Link</button>
                        <button type="button" class="wp_bio_links_item_move_up">Move Up Link</button>
                        <button type="button" class="wp_bio_links_item_remove">Remove Link</button>
                    </p>
                </div>
            </div>

        <?php endforeach; ?>

    <?php else : ?>

        <div class="postbox">
            <h2 style="background-color:#e9e9e9;">Link</h2>
            <div class="inside">
                <p>
                    <label>Title</label><br>
                    <input name="wp_bio_links[items][1][text]" type="text" value=""><br />
                </p>
                <p>
                    <label>Link url</label><br>
                    <input name="wp_bio_links[items][1][url]" type="text" value=""><br />
                </p>
                <hr />
                <p>
                    <button type="button" class="wp_bio_links_item_move_down">Move Down Link</button>
                    <button type="button" class="wp_bio_links_item_move_up">Move Up Link</button>
                    <button type="button" class="wp_bio_links_item_remove">Remove Link</button>
                </p>
            </div>
        </div>

    <?php endif; ?>

    </div>

    <div>
        <button type="button" id="wp_bio_links_add_link">Add Link</button>
    </div>

    <?php
}


// save post meta
add_action( 'save_post', 'wp_bio_links_save_post_meta' );

function wp_bio_links_save_post_meta( $post_id ) {

    // Stop the script when doing autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Verify the nonce. If is not there, stop the script
    if ( !isset( $_POST['wp_bio_links_nonce'] ) || !wp_verify_nonce( $_POST['wp_bio_links_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    // Stop the script if the user does not have edit permissions
    if ( !current_user_can( 'edit_post' ) ) {
        return;
    }

    // Save the items
    if ( isset( $_POST['wp_bio_links'] ) ) {

        $data = [];

        // sanitize nickname
        if ( isset( $_POST['wp_bio_links']['nickname'] ) && !empty( $_POST['wp_bio_links']['nickname'] ) ) {
            $data['nickname'] = sanitize_text_field( $_POST['wp_bio_links']['nickname'] );
        }

        // sanitize links
        if ( isset( $_POST['wp_bio_links']['items'] ) ) {

            foreach ( $_POST['wp_bio_links']['items'] as $key => $item ) {

                if ( empty( $item['text'] ) || empty( $item['url'] ) ) {
                    continue;
                }

                $data['items'][$key] = [
                    'text' => sanitize_text_field( $item['text'] ),
                    'url' => esc_url_raw( $item['url'] ),
                ];
            }
        }

        if ( !empty( $data ) ) {
            update_post_meta(
                $post_id,
                'wp_bio_links',
                $data
            );
        }
    }
}


// enqueue meta box javascript
add_action( 'admin_enqueue_scripts', 'wp_bio_links_enqueue_scripts' );

function wp_bio_links_enqueue_scripts()
{
    // get current admin screen, or null
    $screen = get_current_screen();

    // verify admin screen object
    if ( is_object( $screen ) ) {
        // enqueue only for specific post types
        if ( in_array( $screen->post_type, ['wp-bio-links'] ) ) {
            // enqueue style
            wp_enqueue_style( 'wp-color-picker' );
            // enqueue script
            wp_enqueue_media();
            wp_enqueue_script('wp_bio_links_scripts', plugin_dir_url( __FILE__ ) . 'wp-bio-links.js', ['jquery', 'wp-color-picker'], false, true );


        }
    }
}


// enqueue frontend css
add_action( 'wp_enqueue_scripts', 'wp_bio_links_wp_enqueue_scripts' );

function wp_bio_links_wp_enqueue_scripts() {
    // add frontend inline css
    $nickname_color = get_option( 'wp_bio_links_nickname_color', '#bbb' );
    $link_bg_color = get_option( 'wp_bio_links_link_bg_color', '#fff' );
    $link_border_color = get_option( 'wp_bio_links_link_border_color', '#3d3b3c' );
    $link_text_color = get_option( 'wp_bio_links_link_text_color', '#3d3b3c' );
    $custom_style = '
.wp-bio-links {
    background-color: ' . $link_text_color . '; 
    height:100%;
    width:100%;
}

.wp-bio-links-container {
    font-size: 16px;
    line-height: 1.5;
    margin: 0 auto;
    max-width: 700px;
    padding: 1.5em;
    text-align: center;
}


.wp-bio-links-thumbnail {
    border-radius: 50%;
    display: block;
    margin-bottom: 1.5em;
    margin-left: auto;
    margin-right: auto;
    max-height: 150px;
    width: auto;
}


.wp-bio-links-nickname {
    color: ' . $nickname_color . ';
    font-size: 16px;
    font-weight: 600;
    line-height: 1.25;
    margin: 0 auto 1.875em;
    text-overflow: ellipsis;
}


.wp-bio-links-items {
    font-weight: 600;
}

.wp-bio-links-item,
.wp-bio-links-item:link,
.wp-bio-links-item:visited,
.wp-bio-links-item:active {
    background-color: ' . $link_bg_color . '; 
    border: 2px solid ' . $link_border_color . ';
    color: ' . $link_text_color . ';
    display: block;
    margin-bottom: 1.5em;
    outline: 0;
    padding: 1em 1.5em;
    text-decoration: none;
}


.wp-bio-links-item:hover {
    background-color: ' . $link_text_color . '; 
    border: 2px solid ' . $link_bg_color . ';
    color: ' . $link_bg_color . ';
    outline: 0;
}

.wp-bio-links-credits {
    color: ' . $link_bg_color . ';
    outline: 0;
    text-decoration: none;
}

.wp-bio-links-credits:link,
.wp-bio-links-credits:visited,
.wp-bio-links-credits:hover,
.wp-bio-links-credits:active {
    color: ' . $link_bg_color . '; 
    outline: 0;
    text-decoration: none;
}
';

    wp_register_style( 'wp_bio_links_css', false );
    wp_enqueue_style( 'wp_bio_links_css' );
    wp_add_inline_style( 'wp_bio_links_css', $custom_style );
}


// options page
add_action( 'admin_menu', 'wp_bio_links_options_page' );

function wp_bio_links_options_page() {
    add_submenu_page(
        'edit.php?post_type=wp-bio-links',
        'WP Bio Links',
        __( 'Settings', 'wp-bio-links' ),
        'manage_options',
        'wp-bio-links',
        'wp_bio_links_options_page_html'
    );
}


function wp_bio_links_options_page_html() {

    $screen = get_current_screen();

    if ( is_admin() && $screen->id !== 'wp-bio-links_page_wp-bio-links' ) {
        return;
    }

    if ( is_admin() && !current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error(
            'wp_bio_links_messages',
            'wp_bio_links_message',
            __( 'Settings updated!', 'wp-bio-links' ),
            'updated'
        );
    }

    settings_errors( 'wp_bio_links_messages' );

    ?>

    <div class="wrap">

        <h1><?php esc_html_e( get_admin_page_title() ); ?></h1>

        <form action="options.php" method="post">

        <?php 

        settings_fields( 'wp-bio-links' );
        do_settings_sections( 'wp-bio-links' );
        submit_button( __( 'Update Settings', 'wp-bio-links' ) );

        ?>

        </form>
    </div>

    <?php 
}


// register settings
add_action( 'admin_init', 'wp_bio_links_register_settings' );

function wp_bio_links_register_settings() {
    add_settings_section(
        'wp_bio_links_section_general_settings',
        __( 'Header', 'wp-bio-links' ),
        false,
        //'wp_bio_links_section_general_settings_callback',
        'wp-bio-links'
    );

    // thumbnail field setting
    register_setting( 'wp-bio-links', 'wp_bio_links_thumbnail' );

    add_settings_field(
        'wp_bio_links_thumbnail',
        __( 'Thumbnail', 'wp-bio-links' ),
        'wp_bio_links_thumbnail_callback',
        'wp-bio-links',
        'wp_bio_links_section_general_settings',
        []
    );

    // nickname color field setting
    register_setting( 'wp-bio-links', 'wp_bio_links_nickname_color' );

    add_settings_field(
        'wp_bio_links_nickname_color',
        __( 'Nickname Color', 'wp-bio-links' ),
        'wp_bio_links_nickname_color_callback',
        'wp-bio-links',
        'wp_bio_links_section_general_settings',
        []
    );

    // link background color field setting
    register_setting( 'wp-bio-links', 'wp_bio_links_link_bg_color' );

    add_settings_field(
        'wp_bio_links_link_bg_color',
        __( 'Background Link Color', 'wp-bio-links' ),
        'wp_bio_links_link_bg_color_callback',
        'wp-bio-links',
        'wp_bio_links_section_general_settings',
        []
    );

    // link border color field setting
    register_setting( 'wp-bio-links', 'wp_bio_links_link_border_color' );

    add_settings_field(
        'wp_bio_links_link_border_color',
        __( 'Border Link Color', 'wp-bio-links' ),
        'wp_bio_links_link_border_color_callback',
        'wp-bio-links',
        'wp_bio_links_section_general_settings',
        []
    );


    // link text color field setting
    register_setting( 'wp-bio-links', 'wp_bio_links_link_text_color' );

    add_settings_field(
        'wp_bio_links_link_text_color',
        __( 'Text Link Color', 'wp-bio-links' ),
        'wp_bio_links_link_text_color_callback',
        'wp-bio-links',
        'wp_bio_links_section_general_settings',
        []
    );
}


function wp_bio_links_thumbnail_callback( $args )
{
    $wp_bio_links_thumbnail = get_option( 'wp_bio_links_thumbnail' );

    $default_image = plugins_url( 'placeholder.png', __FILE__ );

    if ( empty( $wp_bio_links_thumbnail ) ) {
        $wp_bio_links_thumbnail = $default_image;
    }

    ?>

    <div id="wp_bio_links_thumbnail">
        <img data-src="<?php echo $default_image; ?>" src="<?php echo $wp_bio_links_thumbnail; ?>" height="100" style="border: 1px solid #ccc; margin-bottom: 20px;" />
        <div>
            <input type="hidden" name="wp_bio_links_thumbnail" id="wp_bio_links_thumbnail" value="<?php echo  esc_attr( $wp_bio_links_thumbnail ) ; ?>" />
            <button id="wp_bio_links_upload_thumbnail" type="submit"><?php _e( 'Upload', 'wp-bio-links' ); ?></button>
            <button id="wp_bio_links_remove_thumbnail" type="submit">&times;</button>
        </div>
    </div>

    <?php 
}


function wp_bio_links_nickname_color_callback( $args )
{
    $nickname_color = get_option( 'wp_bio_links_nickname_color', '#bbb' );

    ?>

    <input type="text" class="wp_bio_links_color_picker" id="wp_bio_links_nickname_color" name="wp_bio_links_nickname_color" value="<?php echo  esc_attr( $nickname_color ) ; ?>" minlength="4" maxlength="7"/>

    <?php

}


function wp_bio_links_link_bg_color_callback( $args )
{
    $link_bg_color = get_option( 'wp_bio_links_link_bg_color', '#fff' );

    ?>

    <input type="text" class="wp_bio_links_color_picker" id="wp_bio_links_link_bg_color" name="wp_bio_links_link_bg_color" value="<?php echo  esc_attr( $link_bg_color ) ; ?>" minlength="4" maxlength="7"/>

    <?php
}


function wp_bio_links_link_border_color_callback( $args )
{
    $link_border_color = get_option( 'wp_bio_links_link_border_color', '#fff' );

    ?>

    <input type="text" class="wp_bio_links_color_picker" id="wp_bio_links_link_border_color" name="wp_bio_links_link_border_color" value="<?php echo  esc_attr( $link_border_color ) ; ?>" minlength="4" maxlength="7"/>

    <?php
}


function wp_bio_links_link_text_color_callback( $args )
{
    $link_text_color = get_option( 'wp_bio_links_link_text_color', '#3d3b3c' );

    ?>

    <input type="text" class="wp_bio_links_color_picker" id="wp_bio_links_link_text_color" name="wp_bio_links_link_text_color" value="<?php echo  esc_attr( $link_text_color ) ; ?>" minlength="4" maxlength="7"/>

    <?php
}


// Filter the single_template with our custom function
add_filter( 'single_template', 'wp_bio_links_single_template' );

function wp_bio_links_single_template( $single ) {

    global $post;

    /* Checks for single template by post type */
    if ( $post->post_type == 'wp-bio-links' ) {

        $filepath = plugin_dir_path( __FILE__ ) . 'wp-bio-links.tpl.php';

        if ( file_exists( $filepath ) ) {

            return $filepath;
        }
    }

    return $single;
}

