<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Add submenu page
add_action( 'admin_menu',                             'filter_wp_query__add_submenu_page_action'                    );

// For registering styles and scripts for admin pages
add_action( 'admin_init',                             'filter_wp_query__register_scripts_and_styles_admin_action'   );

// For printing registered scripts in the footer
add_action( 'admin_print_footer_scripts',             'filter_wp_query__admin_print_footer_scripts_action', 1       );

// For printing registerred styles in the header
add_action( 'admin_print_styles',                     'filter_wp_query__admin_print_styles_action'                  );

// Add ajax function for saving all the settings from the admin panels
add_action( 'wp_ajax_filter_wp_query_admin_save',     'filter_wp_query__admin_save_action'                          );