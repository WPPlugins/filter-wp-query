<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Plugin Activation
register_activation_hook( $plugin_file,               'filter_wp_query__activation_action'                          );

// Plugin Deactivation
register_deactivation_hook( $plugin_file,             'filter_wp_query__deactivation_action'                        );

// Filter main query
add_filter( 'pre_get_posts',                          'filter_wp_query__filter_main_query_filter'                   );
