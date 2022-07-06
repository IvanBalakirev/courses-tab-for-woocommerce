<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

/**
 * Plugin Name: Woocommerce Courses Tab
 * Description: Plugin that adds courses tab to WooCommerce my account page
 * Version:     1.0.0
 * Author:      Ivan Balakirev
 * Author URI:  https://ganapati-art.com
 */


/**
 * Init the plugin
 */
if ( ! function_exists( 'init_courses_tab_for_woocommerce' ) ) {
	add_action( 'init', 'init_courses_tab_for_woocommerce' );
	function init_courses_tab_for_woocommerce() {
		if ( class_exists( 'Courses_Tab_For_Woocommerce' ) ) {
			new Courses_Tab_For_Woocommerce;
		}

		// Flush rewrite rules to make WooCommerce endpoints work
		if ( ! get_option( 'courses_tab_for_woocommerce_permalinks_flushed' ) ) {
			flush_rewrite_rules();
			update_option( 'courses_tab_for_woocommerce_permalinks_flushed', TRUE );
		}
	}
}

if ( ! class_exists( 'Courses_Tab_For_Woocommerce' ) ) {
	class Courses_Tab_For_Woocommerce {
		public function __construct() {

			// Add Courses Endpoint with content if the Learndash enabled
			if ( function_exists( 'ld_get_mycourses' ) ) {
				// Add Courses Link to My Account menu
				add_filter( 'woocommerce_account_menu_items', array( $this, 'courses_menu' ), 40 );

				// Add Courses Permalink Endpoint
				add_action( 'init', array( $this, 'courses_endpoint' ), 100 );

				// Add Content to Courses Endpoint
				add_action( 'woocommerce_account_my-courses_endpoint', array( $this, 'courses_endpoint_content' ) );
			}
		}

		public function courses_endpoint_content() {
			$user_courses = ld_get_mycourses( get_current_user_ID() );

			if ( is_array( $user_courses ) AND count( $user_courses ) > 0 ) {
				$output = '<table class="shop_table shop_table_responsive">';
				$output .= '<thead><tr>';
				$output .= '<th>Course</th>';
				$output .= '<th>Actions</th>';
				$output .= '</tr></thead>';

				$output .= '<tbody>';
				foreach ( $user_courses as $user_course ) {
					$course_meta = get_post_meta( $user_course, '_sfwd-courses', TRUE );

					$output .= '<tr>';
					$output .= '<td>';
					$output .= get_the_title( $user_course );
					$output .= '</td>';

					$output .= '<td>';
					$output .= '<a class="woocommerce-Button button" href="' . get_the_permalink( $user_course ) . '">View</a>';
					$output .= '</td>';

					$output .= '</tr>';
				}
				$output .= '</tbody>';
				$output .= '</table>';
				echo $output;
			} else {
				echo 'No courses found';
			}
		}

		public function courses_endpoint() {
			// Don't forget to re-save permalinks manually to make it work
			add_rewrite_endpoint( 'my-courses', EP_PERMALINK | EP_PAGES );
		}

		public function courses_menu( $menu_links ) {
			$menu_links = array_slice( $menu_links, 0, 1, TRUE )
				+ array( 'my-courses' => 'Courses' )
				+ array_slice( $menu_links, 1, NULL, TRUE );

			return $menu_links;
		}
	}
}

if ( ! function_exists( 'courses_tab_for_woocommerce_activation_hook' ) ) {
	register_activation_hook( __FILE__, 'courses_tab_for_woocommerce_activation_hook' );
	function courses_tab_for_woocommerce_activation_hook() {
		update_option( 'courses_tab_for_woocommerce_permalinks_flushed', FALSE );
	}
}