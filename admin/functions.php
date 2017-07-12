<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Registering scripts and sryles for later
function filter_wp_query__register_scripts_and_styles_admin_action() {
	$file        = __FILE__;
	$plugin_url  = plugin_dir_url ( $file );

	// Script file
	wp_register_script( 'filter_wp_query_admin_js', $plugin_url . 'js/admin.js' ); 
	
	// Style file
	wp_register_style( 'filter_wp_query_admin_css', $plugin_url . 'css/admin.css' ); 
}

// Function for printng script files in the footer
function filter_wp_query__admin_print_footer_scripts_action() {
	global $hook_suffix;

	if ( $hook_suffix == 'plugins_page_filter_wp_query' ) {
		wp_enqueue_script( 'filter_wp_query_admin_js' );
	}
}

// Function for printing styles in the header
function filter_wp_query__admin_print_styles_action() {
	global $hook_suffix;

	if ( $hook_suffix == 'plugins_page_filter_wp_query' ) {
		wp_enqueue_style( 'filter_wp_query_admin_css' );
	}
}

// Add submenu page
function filter_wp_query__add_submenu_page_action() {
	add_submenu_page( 'plugins.php', 'Filter WP_Query Settings', 'Filter WP_Query', 'administrator', 'filter_wp_query', 'filter_wp_query__main_function' );
}

// Function of what to display on submenu page
function filter_wp_query__main_function() { 
	global $title;
	
	// All options
	$options_array = filter_wp_query__settings_array(); ?>

	<div class="wrap">
		<div id="filter_wp_query-admin-panel">
			<form enctype="multipart/form-data" id="filter_wp_queryform">
				<div id="filter_wp_query-admin-panel-header">
				
					<h3><?php echo $title; ?></h3>
					
				</div>
				<div id="filter_wp_query-admin-panel-main">
					<div id="filter_wp_query-admin-panel-menu">
					
						<?php echo filter_wp_query__machine_menu( $options_array ); ?>
						
					</div>
					<div id="filter_wp_query-admin-panel-content">
					
						<?php echo filter_wp_query__machine( $options_array ); ?>
						
					</div>
					<div class="clear"></div>
				</div>
				<div id="filter_wp_query-admin-panel-footer">
					<div id="filter_wp_query-admin-panel-footer-submit">
						<input type="submit" value="Apply Changes" class="button button-primary" id="filter_wp_query__settings_array" />
						
						<?php wp_nonce_field( 'wp_ajax_filter_wp_query_ajax', 'filter_wp_query_nonce' ); ?>
						
						<div style="clear: both;"></div>
					</div>
					<div class="clear"></div>
				</div>
			</form>
		</div>
	</div>

<?php }


function filter_wp_query__multiple_form_inputs( $id ) {

	// Array
	$post_types_taxonomies = filter_wp_query__get_post_types_and_taxonomies_array();
	
	$all_array_list = array();
	
	foreach ( $post_types_taxonomies as $post_type_taxonomies => $post_types_taxonomies_arr ) {
		foreach ( $post_types_taxonomies_arr as $post_type_taxonomy => $post_type_taxonomy_arr ) {

			$terms = get_terms( $post_type_taxonomy, 'hide_empty=0' );

			if ( ! empty( $terms ) ) {

				$all_terms_list = array();
				foreach( $terms as $term ) {
					$all_terms_list[$term->slug] = $term->name;
				}
				
				if ( get_option( 'filter_wp_query_settings_filter_' . $id . '_by_post_type', 'post' ) != $post_type_taxonomies ) {
					$this_post_type_style = 'display: none;';
				} else {
					if ( ! get_option( 'filter_wp_query_settings_filter_' . $id, false ) ) {
						$this_post_type_style = 'display: none;';
					} else {
						$this_post_type_style = '';
					}
				}

				$all_array_list['filter_wp_query_settings_' . $id . '_by_terms_' . $post_type_taxonomies . '_' . $post_type_taxonomy] = array(
					'name'         => 'Filter by terms of ' . $post_type_taxonomy . ' taxonomy',
					'desc'         => 'Select terms from ' . $post_type_taxonomy . ' taxonomy you would like to filter',
					'default'      => '',
					'type'         => 'multiple',
					'options'      => $all_terms_list,
					'class'        => 'hide-item-' . $id . ' show-item-' . $id . ' show-item-' . $id . '-' . $post_type_taxonomies,
					'style'        => $this_post_type_style
				);
			}
		}
	}
	
	return $all_array_list;
}

// Settings array
function filter_wp_query__settings_array() {
	$post_types = array();
	
	$post_types_post = array( 'post' => 'post' );
	
	$post_types_args = array(
		'_builtin' => false
	);
	
	$post_types = array_merge( $post_types_post, get_post_types( $post_types_args ) );
	

	// Main query
	if ( ! get_option( 'filter_wp_query_settings_add_more_post_types_to_main_query', false ) ) {
		$main_query_style = 'display: none;';
	} else {
		$main_query_style = '';
	}
	
	$settings_options['main_query_settings'] = array(
		'name'          => __( 'Main Query Settings', 'filter_wp_query' ),
		'options'       => array(
			'filter_wp_query_settings_add_more_post_types_to_main_query' => array(
				'name'          => __( 'Add more post types?', 'filter_wp_query' ),
				'desc'          => __( 'Check this if you would like to include more post types to main query.', 'filter_wp_query' ),
				'default'       => '0',
				'type'          => 'checkbox',
				'onchange'      => "if(jQuery(this).is(':checked')) { jQuery(this).parent().parent().children('.show-item-main_query').show(); } else { jQuery(this).parent().parent().children('.hide-item-main_query').hide(); }"
			),
			
			'filter_wp_query_settings_what_post_types_to_show_on_main_query' => array(
				'name'          => __( 'Select post types:', 'filter_wp_query' ),
				'desc'          => __( 'Select from the list what post types to include to main query.', 'filter_wp_query'),
				'options'       => $post_types,
				'type'          => 'multiple',
				'default'       => 'post',
				'class'         => 'hide-item-main_query show-item-main_query',
				'style'         => $main_query_style
			),

			'posts_per_page' => array(
				'name'          => __( 'Pages show at most', 'filter_wp_query' ),
				'desc'          => __( 'Choose the maximum numbers of posts to be displayed', 'filter_wp_query' ),
				'default'       => get_option( 'posts_per_page' ),
				'type'          => 'text'
			),
			
			'posts_per_rss' => array(
				'name'          => __( 'RSS items to show at most', 'filter_wp_query' ),
				'desc'          => __( 'Choose the maximum numbers of feed posts to be displayed', 'filter_wp_query' ),
				'default'       => get_option( 'posts_per_rss' ),
				'type'          => 'text'
			),
		)
	);
	
	// Front page
	if ( ! get_option( 'filter_wp_query_settings_filter_home', false ) ) {
		$home_style = 'display: none;';
	} else {
		$home_style = '';
	}
	
	$home_array = filter_wp_query__multiple_form_inputs( 'home' );
	
	$settings_options['home_settings'] = array(
		'name'          => __( 'Home Page Settings', 'filter_wp_query' ),
		'options'       => array(
			'filter_wp_query_settings_filter_home' => array(
				'name'          => __( 'Filter Home Page?', 'filter_wp_query' ),
				'desc'          => __( 'Select what post type and terms to show or to exclude from the blog posts index page. If multiple post types selected (in Main Query Settings) - then you can filter what terms to not display, otherwise the post type and terms will be used to what exactly to show.', 'filter_wp_query' ),
				'default'       => '0',
				'type'          => 'checkbox',
				'onchange'      => "if(jQuery(this).is(':checked')) { jQuery(this).parent().parent().children('.show-item-select-home, .show-item-home-' + jQuery('#filter_wp_query_settings_filter_home_by_post_type').val()).show(); } else { jQuery(this).parent().parent().children('.hide-item-select-home, .hide-item-home').hide(); }"
			),
			
			'filter_wp_query_settings_filter_home_by_post_type' => array(
				'name'          => __( 'Filter by post type:', 'filter_wp_query' ),
				'desc'          => __( 'Select the post type you would like to filter', 'filter_wp_query' ),
				'default'       => 'post',
				'options'       => $post_types,
				'type'          => 'select',
				'onchange'      => "jQuery(this).parent().parent().children('.hide-item-home').hide(); jQuery(this).parent().parent().children('.show-item-home-' + jQuery(this).val()).show();",
				'style'         => $home_style,
				'class'         => 'hide-item-select-home show-item-select-home',
			)

		) + $home_array
	);
	
	// RSS Feed
	if ( ! get_option( 'filter_wp_query_settings_filter_rss_feed', false ) ) {
		$rss_feed_style = 'display: none;';
	} else {
		$rss_feed_style = '';
	}
	
	$rss_feed_array = filter_wp_query__multiple_form_inputs( 'rss_feed' );
	
	$settings_options['rss_feed_settings'] = array(
		'name'          => __( 'RSS Feed Settings', 'filter_wp_query' ),
		'options'       => array(
			'filter_wp_query_settings_filter_rss_feed' => array(
				'name'          => __( 'Filter RSS Feed?', 'filter_wp_query' ),
				'desc'          => __( 'Select what post type and terms to show or to exclude from the feed. If multiple post types selected (in Main Query Settings) - then you can filter what terms to not display, otherwise the post type and terms will be used to what exactly to show.', 'filter_wp_query' ),
				'default'       => '0',
				'type'          => 'checkbox',
				'onchange'      => "if(jQuery(this).is(':checked')) { jQuery(this).parent().parent().children('.show-item-select-rss_feed, .show-item-rss_feed-' + jQuery('#filter_wp_query_settings_filter_rss_feed_by_post_type').val()).show(); } else { jQuery(this).parent().parent().children('.hide-item-select-rss_feed, .hide-item-rss_feed').hide(); }"
			),
			
			'filter_wp_query_settings_filter_rss_feed_by_post_type' => array(
				'name'          => __( 'Filter by post type:', 'filter_wp_query' ),
				'desc'          => __( 'Select the post type you would like to filter', 'filter_wp_query' ),
				'default'       => 'post',
				'options'       => $post_types,
				'type'          => 'select',
				'onchange'      => "jQuery(this).parent().parent().children('.hide-item-rss_feed').hide(); jQuery(this).parent().parent().children('.show-item-rss_feed-' + jQuery(this).val()).show();",
				'style'         => $rss_feed_style,
				'class'         => 'hide-item-select-rss_feed show-item-select-rss_feed',
			)

		) + $rss_feed_array
	);
	
	// Search
	if ( ! get_option( 'filter_wp_query_settings_filter_search', false ) ) {
		$search_style = 'display: none;';
	} else {
		$search_style = '';
	}
	
	$search_array = filter_wp_query__multiple_form_inputs( 'search' );
	
	$settings_options['search_settings'] = array(
		'name'          => __( 'Search Settings', 'filter_wp_query' ),
		'options'       => array(
			'filter_wp_query_settings_filter_search' => array(
				'name'          => __( 'Filter Search Page?', 'filter_wp_query' ),
				'desc'          => __( 'Select what post type and terms to show or to exclude from the search result page archive. If multiple post types selected (in Main Query Settings) - then you can filter what terms to not display, otherwise the post type and terms will be used to what exactly to show.', 'filter_wp_query' ),
				'default'       => '0',
				'type'          => 'checkbox',
				'onchange'      => "if(jQuery(this).is(':checked')) { jQuery(this).parent().parent().children('.show-item-select-search, .show-item-search-' + jQuery('#filter_wp_query_settings_filter_search_by_post_type').val()).show(); } else { jQuery(this).parent().parent().children('.hide-item-select-search, .hide-item-search').hide(); }"
			),
			
			'filter_wp_query_settings_filter_search_by_post_type' => array(
				'name'          => __( 'Filter by post type:', 'filter_wp_query' ),
				'desc'          => __( 'Select the post type you would like to filter', 'filter_wp_query' ),
				'default'       => 'post',
				'options'       => $post_types,
				'type'          => 'select',
				'onchange'      => "jQuery(this).parent().parent().children('.hide-item-search').hide(); jQuery(this).parent().parent().children('.show-item-search-' + jQuery(this).val()).show();",
				'style'         => $search_style,
				'class'         => 'hide-item-select-search show-item-select-search',
			)

		) + $search_array
	);
	
	
	return $settings_options;
}

// Function for adding menu tab
function filter_wp_query__machine_menu( $options ) {

	$output = '<ul>';
	foreach( $options as $key => $arr ) {
		$output .= '<li class="filter_wp_query-admin-panel-menu-li">' . 
			'<a class="filter_wp_query-admin-panel-menu-link" href="#" id="filter_wp_query-admin-panel-menu-' . $key . '"><span></span>' . $arr['name'] . '</a>' . 
		'</li>' . "\n";
	}
	$output .= '</ul>';
	
	return $output;
}

// Function for generating inputs
function filter_wp_query__machine( $options ) {

	$output = '';

	foreach( $options as $key => $arr ) {
		if ( isset( $arr['options'] ) ) {

			$output .= '<div class="filter_wp_query-admin-panel-content-box" id="filter_wp_query-admin-panel-content-' . $key . '">';
		
				foreach ( $arr['options'] as $option_key => $arg ) {
					$val            = '';
					$valoare        = '';

					// Option class
					if ( isset( $arg['class'] ) ) {
						$class      = ' ' . $arg['class'];
					} else {
						$class      = '';
					}
					
					// Option style
					if ( isset( $arg['style'] ) ) {
						$style      = ' style="' . $arg['style'] . '" ';
					} else {
						$style      = '';
					}
					
					$val            = get_option( $option_key );

					$checked        = '';
					
					$select_value   = '';
					
					$output .= '<div class="filter_wp_query-option' . $class . '"' . $style . '>'; 
					$output .= '<h3 class="filter_wp_query-option-title">' . $arg['name'] . '</h3>' . "\n";
					
					if ( $arg['type'] == 'text' ) {

						$output .= '<input class="filter_wp_query-input-text" name="' . $option_key . '" id="' . $option_key . '" type="' . $arg['type'] . '" value="' . $val . '"  />';
						
					} else if ( $arg['type'] == 'checkbox' ) {

						if ( isset( $arg['onchange'] ) ) {
							$onchange = ' onchange="' . $arg['onchange'] . '" ';
						} else {
							$onchange = '';
						}
						
						if ( $val == '1' ) {
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						$output .= '<input type="checkbox" class="filter_wp_query-input-checkbox" name="' . $option_key . '" id="' . $option_key .  '" value="true" ' . $checked . $onchange . ' />';
						
					} else if ( $arg['type'] == 'select' ) {

						if ( isset( $arg['onchange'] ) ) {
							$onchange = ' onchange="' . $arg['onchange'] . '" ';
						} else {
							$onchange = '';
						}
					
						$output .= '<select class="filter_wp_query-input-select" name="' . $option_key . '" id="' . $option_key . '"' . $onchange . '>';
						foreach ( $arg['options'] as $option => $key ) {
							$selected = '';
								if ( $val == $key ) {
									$selected = ' selected="selected"'; 
								}
							$output .= '<option' . $selected . ' value="' . $key . '">' . $option . '</option>';
						}
						$output .= '</select>';
						
					} else if ( $arg['type'] == 'multiple' ) {

						$output .= '<select multiple="multiple" class="filter_wp_query-input-multiple" name="' . $option_key . '[]" id="' . $option_key . '">';
						foreach( $arg['options'] as $option => $key ) {
							$selected = '';
							$value2 = '';
							if ( is_array( $val ) ) {
								foreach( $val as $value2 ) {
									if ( $option == $value2 ) {
										$selected = ' selected="selected"'; 
									}
								}
							}
							$output .= '<option' . $selected . ' value="' . $option . '">' . $key . '</option>';
						}
						$output .= '</select>';
						
					}
					
					$output .= '<div class="clear"></div><small>' . $arg['desc'] . '</small>' . "\n";
					$output .= '</div>' . "\n";
				}
				
			$output .= '</div>';
		}
	}
	
	return $output;
}

// Function that is used with ajax to save all settings from the admin panel
function filter_wp_query__admin_save_action() {

	$function_name = $_POST['id'];
	
	check_ajax_referer( 'wp_ajax_filter_wp_query_ajax', 'filter_wp_query_nonce' );
	
	// The data
	$data = $_POST['data'];

	// The options from the ID
	$options = $function_name();

	// Parses the string into variables
	parse_str( $data, $output );
	
	foreach ( $options as $o => $arr ) {
		if ( isset( $arr['options'] ) ) {
			foreach ( $arr['options'] as $options => $opt_arr ) {

				if ( isset( $output[$options] ) ) {
					$new_value = $output[$options];
					
					// If checkbox
					if ( $opt_arr['type'] == 'checkbox' ) {
						update_option( $options, 1 );
						
					// If multiple
					} else if ( $opt_arr['type'] == 'multiple' ) {
						update_option( $options, $new_value );

					// Other
					} else {
						update_option( $options, stripslashes( $new_value ) );
					}
				} else {
				
					// If checkbox
					if ( $opt_arr['type'] == 'checkbox' ) {
						update_option( $options, 0 );
						
					// If multiple
					} else if ( $opt_arr['type'] == 'multiple' ) {
						update_option( $options, 0 );
					}					
				}

			}
		}

	}
	die( "1" );
}