<?php
/*
 Plugin Name: Filter WP_Query
 Plugin URI: http://wordpress.org/plugins/filter-wp-query/
 Description: Include more post types to main query; filter what posts to show in the feed, home page and/or search page. You can filter by terms and post types. (Go to: Dashboard -> Plugins -> Filter WP_Query)
 Version: 1.0
 Author: Alexandru Vornicescu
 Author URI: http://alexvorn.com
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Global variable
$plugin_file           = __FILE__;
$plugin_dir_path       = trailingslashit( plugin_dir_path( $plugin_file ) );

// Include actions.php file
require( $plugin_dir_path . 'actions.php' );

// Quick admin check and load if needed
if ( is_admin() ) {
	// Include admin/actions.php file
	require( $plugin_dir_path . 'admin/actions.php' );

	// Include admin/functions.php file
	require( $plugin_dir_path . 'admin/functions.php' );
}

// Function on plugin 
function filter_wp_query__activation_action() {
	// Nothing, just because
}

// Function on plugin deactivation
function filter_wp_query__deactivation_action() {
	// Nothing, just because
}

// Array
function filter_wp_query__get_post_types_and_taxonomies_array() {

	$post_types = array();
	$post_types_post = array( 'post' => 'post' );
	$post_types_args = array(
		'_builtin' => false
	);
	$post_types = array_merge( $post_types_post, get_post_types( $post_types_args ) );
	$post_types_taxonomies = array();
	
	foreach ( $post_types as $post_type ) {
		$taxonomies         = get_object_taxonomies( $post_type );

		if ( $post_type == 'post' ) {
			foreach ( $taxonomies as $tax_key => $tax_array ) {
				if ( $tax_array == 'post_format' ) {
					unset( $taxonomies[$tax_key] );
				}
			}
		}
		
		foreach ( $taxonomies as $tax ) {
			$post_types_taxonomies[$post_type][$tax] = array();
		}
	}
	
	return $post_types_taxonomies;
}

// Array by id from database
function filter_wp_query__get_post_types_and_taxonomies_array_by_id( $id ) {

	// All post types and taxonomies array
	$post_types = array();
	$post_types_post = array( 'post' => 'post' );
	$post_types_args = array(
		'_builtin' => false
	);
	$post_types = array_merge( $post_types_post, get_post_types( $post_types_args ) );
	$post_types_taxonomies = array();
	
	foreach ( $post_types as $post_type ) {
		$taxonomies         = get_object_taxonomies( $post_type );
		
		if ( $post_type == 'post' ) {
			foreach ( $taxonomies as $tax_key => $tax_array ) {
				if ( $tax_array == 'post_format' ) {
					unset( $taxonomies[$tax_key] );
				}
			}
		}
		
		foreach ( $taxonomies as $tax ) {
			$post_types_taxonomies[$post_type][$tax] = get_option( 'filter_wp_query_settings_' . $id . '_by_terms_' . $post_type . '_' . $tax, array() );
		}
	}
	
	return $post_types_taxonomies;
}

function filter_wp_query__include_settings_in_wp_query( $query, $id ) {

	// Use multiple post types
	$filter_wp_query_settings_add_more_post_types_to_main_query = get_option( 'filter_wp_query_settings_add_more_post_types_to_main_query', false );

	// Get array by id from database
	$post_types_taxonomies = filter_wp_query__get_post_types_and_taxonomies_array_by_id( $id );

	// If checkbox to filter is selected
	$filter_wp_query_settings_filter_id = get_option( 'filter_wp_query_settings_filter_' . $id, false );

	// If filter selected
	if ( $filter_wp_query_settings_filter_id ) {
		$tax_query = array();
		
		// If multiple post types selected, next if filter feed
		if ( $filter_wp_query_settings_add_more_post_types_to_main_query ) {

			$post_type_c = get_option( 'filter_wp_query_settings_what_post_types_to_show_on_main_query', array( 'post' ) );
			foreach ( $post_types_taxonomies as $post_type_taxonomies => $post_types_taxonomies_arr ) {
				foreach ( $post_type_c as $multi_post_type ) {
					if ( $post_type_taxonomies == $multi_post_type ) {
						foreach ( $post_types_taxonomies_arr as $post_type_taxonomy => $post_type_taxonomy_arr ) {
							if ( ! empty( $post_type_taxonomy_arr ) ) {
								$new_array_query               = array();
								$new_array_query['taxonomy']   = $post_type_taxonomy;
								$new_array_query['field']      = 'slug';
								$new_array_query['terms']      = $post_type_taxonomy_arr;
								$new_array_query['operator']   = 'NOT IN';
								$tax_query[]                   = $new_array_query;
							}
						}
					}
				}
			}
			$query->set( 'post_type', $post_type_c );
		} else {

			$post_type_c = get_option( 'filter_wp_query_settings_filter_' . $id . '_by_post_type', 'post' );
			foreach ( $post_types_taxonomies as $post_type_taxonomies => $post_types_taxonomies_arr ) {
				if ( $post_type_taxonomies == $post_type_c ) {
					foreach ( $post_types_taxonomies_arr as $post_type_taxonomy => $post_type_taxonomy_arr ) {
						if ( ! empty( $post_type_taxonomy_arr ) ) {
							$new_array_query                = array();
							$new_array_query['taxonomy']    = $post_type_taxonomy;
							$new_array_query['field']       = 'slug';
							$new_array_query['terms']       = $post_type_taxonomy_arr;
							$tax_query[]                    = $new_array_query;
						}
					}
				}
			}
			$query->set( 'post_type', $post_type_c );
		}
		$query->set( 'tax_query', $tax_query );
	} else {
		
		// If multiple post types selected for main (post type query) query
		if ( $filter_wp_query_settings_add_more_post_types_to_main_query ) {
			$post_type_c = get_option( 'filter_wp_query_settings_what_post_types_to_show_on_main_query', array( 'post' ) );
			$query->set( 'post_type', $post_type_c );
		}

	}
	
	return $query;
}

// Filter main query
function filter_wp_query__filter_main_query_filter( $query ) {

	// Use multiple post types
	$filter_wp_query_settings_add_more_post_types_to_main_query = get_option( 'filter_wp_query_settings_add_more_post_types_to_main_query', false );

	// If is not admin 
	if ( ! is_admin() ) {
	
		// If is main query ( primary )
		if ( $query->is_main_query() ) {

			// Home
			if ( is_home() ) {
				$query = filter_wp_query__include_settings_in_wp_query( $query, 'home' );
			
			// Search
			} else if ( is_search() ) {
				$query = filter_wp_query__include_settings_in_wp_query( $query, 'search' );
			
			// Feed
			} else if ( is_feed() ) {
				$query = filter_wp_query__include_settings_in_wp_query( $query, 'rss_feed' );
			
			// Archive pages
			} else if ( is_archive() ){
			
				// If multiple post types selected for main (post type query) query
				if ( $filter_wp_query_settings_add_more_post_types_to_main_query ) {

					// If is not a post type archive
					if ( ! is_post_type_archive() ) {
						$post_type_c = get_option( 'filter_wp_query_settings_what_post_types_to_show_on_main_query', array( 'post' ) );
						$query->set( 'post_type', $post_type_c );
					}
				}
			}
		}
	}

	return $query;
}