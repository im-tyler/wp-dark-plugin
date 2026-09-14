<?php
/**
 * Plugin Name: WP Dark Plugin
 * Description: Always-dark post editor. Darkens the admin chrome and the writing canvas when editing blog posts (block editor only).
 * Version: 1.0.0
 * Requires at least: 6.3
 * Author: Tyler
 * License: MIT
 */

defined( 'ABSPATH' ) || exit;

function wpdp_admin_styles( $hook_suffix ) {
	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->post_type ) {
		return;
	}

	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	wp_enqueue_style( 'wpdp-editor-chrome', plugin_dir_url( __FILE__ ) . 'admin-editor.css', array(), '1.0.0' );
}
add_action( 'admin_enqueue_scripts', 'wpdp_admin_styles' );

function wpdp_canvas_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( plugins_url( 'canvas.css?ver=1.0.0', __FILE__ ) );
}
add_action( 'after_setup_theme', 'wpdp_canvas_styles' );
