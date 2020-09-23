<?php
/*
Plugin Name: WPUM Profile Post Types
Plugin URI:  https://wpusermanager.com
Description: Addon for WP User Manager - add other custom post type entries to user profiles
Version:     1.0
Author:      WP User Manager
Author URI:  https://wpusermanager.com/
License:     GPLv3+
*/


/**
 * @param array $paths
 *
 * @return array
 */
function wpumwpp_template_paths( $paths ) {
	$paths[] = dirname( __FILE__ ) . '/templates';

	return $paths;
}
add_filter( 'wpum_template_paths', 'wpumwpp_template_paths' );

/**
 * Register new settings for the addon.
 *
 * @param array $settings
 *
 * @return array
 */
function wpumpp_register_settings( $settings ) {
	$settings['profiles_content'][] = array(
		'id'       => 'profile_cpts',
		'name'     => __( 'Display Custom Post Types' ),
		'desc'     => __( 'Display a tab for each Custom Post Type to display users submitted entries on their profile page.', 'wpum-woocommerce' ),
		'type'     => 'multiselect',
		'options'  => wpumpp_get_post_types(),
		'multiple' => true,
	);

	return $settings;
}


add_action( 'wpum_registered_settings', 'wpumpp_register_settings' );

function wpumpp_get_post_types() {
	$args = array(
		'public'   => true,
		'_builtin' => false,
	);

	$all_post_types = get_post_types( apply_filters( 'wpumpp_post_type_args', $args ), 'objects' );

	$post_types = array();

	foreach ( $all_post_types as $post_type ) {
		$post_types[] = array(
			'value' => $post_type->name,
			'label' => $post_type->label,
		);
	}

	return $post_types;
}

function wpumpp_init() {
	$post_types = wpum_get_option( 'profile_cpts', array() );

	if ( empty( $post_types ) ) {
		return;
	}

	foreach ( $post_types as $key => $slug ) {
		$post_type = get_post_type_object( $slug );

		add_filter( 'wpum_get_registered_profile_tabs', function( $tabs ) use ( $slug, $key, $post_type  ) {
			$tabs[ $slug ] = [
				'name'     => esc_html( apply_filters( 'wpum_profile_tab_post_type_name', $post_type->label, $post_type ) ),
				'priority' => $key + 10,
			];

			return $tabs;
		} );

		add_action( 'wpum_profile_page_content_' . $slug, function ( $data ) use ( $post_type ) {
			WPUM()->templates->set_template_data( [
				'post_type'       => $post_type,
				'user'            => $data->user,
				'current_user_id' => $data->current_user_id,
			] )->get_template_part( 'profiles/custom-post-types' );
		} );

	}
}

add_action( 'init', 'wpumpp_init', 9999 );
