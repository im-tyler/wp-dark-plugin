<?php
/**
 * Plugin Name: WP Dark Plugin
 * Description: Always-dark post editor. Darkens the admin chrome and the writing canvas when editing blog posts (block editor only). Colours configurable under Settings.
 * Version: 1.4.5
 * Requires at least: 6.3
 * Author: Tyler
 * License: MIT
 */

defined( 'ABSPATH' ) || exit;

function wpdp_defaults() {
	return array(
		'background' => '#1d1d1f',
		'accent'     => '#8ab4f8',
		'everywhere' => '0',
	);
}

function wpdp_colors() {
	return wp_parse_args( (array) get_option( 'wpdp_colors', array() ), wpdp_defaults() );
}

function wpdp_hex_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	return array_map( 'hexdec', str_split( $hex, 2 ) );
}

function wpdp_mix( $from, $to, $amount ) {
	$a = wpdp_hex_rgb( $from );
	$b = wpdp_hex_rgb( $to );
	$out = '#';

	foreach ( array( 0, 1, 2 ) as $i ) {
		$v = (int) round( $a[ $i ] + ( $b[ $i ] - $a[ $i ] ) * $amount );
		$out .= str_pad( dechex( max( 0, min( 255, $v ) ) ), 2, '0', STR_PAD_LEFT );
	}

	return $out;
}

function wpdp_luminance( $hex ) {
	$rgb = wpdp_hex_rgb( $hex );
	return ( 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2] ) / 255;
}

function wpdp_palette() {
	$colors = wpdp_colors();
	$bg     = $colors['background'];
	$light  = wpdp_luminance( $bg ) > 0.55;

	return array(
		'bg'          => $bg,
		'surface'     => wpdp_mix( $bg, '#ffffff', 0.05 ),
		'surface2'    => wpdp_mix( $bg, '#ffffff', 0.10 ),
		'border'      => wpdp_mix( $bg, '#ffffff', 0.18 ),
		'input'       => wpdp_mix( $bg, '#000000', 0.10 ),
		'code'        => wpdp_mix( $bg, '#000000', 0.18 ),
		'text'        => $light ? '#1c1c1e' : '#e8e8e8',
		'text_strong' => $light ? '#000000' : '#f2f2f2',
		'text_muted'  => $light ? '#55555c' : '#a8a8a8',
		'accent'      => $colors['accent'],
		'scheme'      => $light ? 'light' : 'dark',
	);
}

function wpdp_vars_css( $selector ) {
	$p = wpdp_palette();

	return $selector . ' {'
		. '--wpdp-bg:' . $p['bg'] . ';'
		. '--wpdp-surface:' . $p['surface'] . ';'
		. '--wpdp-surface-2:' . $p['surface2'] . ';'
		. '--wpdp-border:' . $p['border'] . ';'
		. '--wpdp-input:' . $p['input'] . ';'
		. '--wpdp-code:' . $p['code'] . ';'
		. '--wpdp-text:' . $p['text'] . ';'
		. '--wpdp-text-strong:' . $p['text_strong'] . ';'
		. '--wpdp-text-muted:' . $p['text_muted'] . ';'
		. '--wpdp-accent:' . $p['accent'] . ';'
		. '--wpdp-scheme:' . $p['scheme'] . ';'
		. '}';
}

function wpdp_settings_version() {
	return substr( md5( wp_json_encode( wpdp_colors() ) ), 0, 8 );
}

function wpdp_canvas_scope() {
	$colors = wpdp_colors();
	return '1' === $colors['everywhere'] ? 'body.editor-styles-wrapper' : 'body.post-type-post';
}

function wpdp_canvas_css() {
	$css = <<<'CSS'
{scope} {
	background: var( --wpdp-bg );
	color: var( --wpdp-text );
}

{scope} .editor-post-title__input {
	color: var( --wpdp-text-strong );
}

{scope} .editor-post-title__input::placeholder {
	color: var( --wpdp-text-muted );
}

{scope} a {
	color: var( --wpdp-accent );
}

{scope} h1,
{scope} h2,
{scope} h3,
{scope} h4,
{scope} h5,
{scope} h6 {
	color: var( --wpdp-text-strong );
}

{scope} blockquote {
	border-color: var( --wpdp-border );
}

{scope} blockquote cite,
{scope} .wp-block-quote__citation {
	color: var( --wpdp-text-muted );
}

{scope} pre,
{scope} .wp-block-code code {
	background: var( --wpdp-code );
	color: var( --wpdp-text );
}

{scope} table,
{scope} .wp-block-table th,
{scope} .wp-block-table td {
	border-color: var( --wpdp-border );
}

{scope} hr,
{scope} .wp-block-separator {
	border-color: var( --wpdp-border );
	background-color: var( --wpdp-border );
}

{scope} figcaption {
	color: var( --wpdp-text-muted );
}

{scope} ::selection {
	background: var( --wpdp-accent );
	color: #ffffff;
}
CSS;

	return wpdp_vars_css( ':root' ) . '@media (min-width: 500px) {' . str_replace( '{scope}', wpdp_canvas_scope(), $css ) . '}';
}

function wpdp_admin_styles( $hook_suffix ) {
	$colors = wpdp_colors();

	if ( '1' === $colors['everywhere'] ) {
		wp_enqueue_style( 'wpdp-editor-chrome', plugin_dir_url( __FILE__ ) . 'admin-editor.css', array(), (string) filemtime( plugin_dir_path( __FILE__ ) . 'admin-editor.css' ) );
		wp_enqueue_style( 'wpdp-admin-global', plugin_dir_url( __FILE__ ) . 'admin-global.css', array( 'wpdp-editor-chrome' ), (string) filemtime( plugin_dir_path( __FILE__ ) . 'admin-global.css' ) );
		wp_add_inline_style( 'wpdp-admin-global', wpdp_vars_css( ':root' ) );
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->post_type ) {
		return;
	}

	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	wp_enqueue_style( 'wpdp-editor-chrome', plugin_dir_url( __FILE__ ) . 'admin-editor.css', array(), (string) filemtime( plugin_dir_path( __FILE__ ) . 'admin-editor.css' ) );
	wp_add_inline_style( 'wpdp-editor-chrome', wpdp_vars_css( ':root' ) );
}
add_action( 'admin_enqueue_scripts', 'wpdp_admin_styles' );

function wpdp_editor_settings( $settings ) {
	if ( isset( $settings['__unstableResolvedAssets']['styles'] ) ) {
		$settings['__unstableResolvedAssets']['styles'] .= '<style id="wpdp-canvas">' . wpdp_canvas_css() . '</style>';
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'wpdp_editor_settings' );

function wpdp_register_settings() {
	register_setting( 'wpdp_settings', 'wpdp_colors', array( 'sanitize_callback' => 'wpdp_sanitize' ) );
}
add_action( 'admin_init', 'wpdp_register_settings' );

function wpdp_sanitize( $input ) {
	$defaults = wpdp_defaults();
	$input    = is_array( $input ) ? $input : array();

	$background = isset( $input['background'] ) ? sanitize_hex_color( $input['background'] ) : '';
	$accent     = isset( $input['accent'] ) ? sanitize_hex_color( $input['accent'] ) : '';

	return array(
		'background' => $background ? $background : $defaults['background'],
		'accent'     => $accent ? $accent : $defaults['accent'],
		'everywhere' => empty( $input['everywhere'] ) ? '0' : '1',
	);
}

function wpdp_menu() {
	add_options_page( 'Dark Editor', 'Dark Editor', 'manage_options', 'wp-dark-plugin', 'wpdp_settings_page' );
}
add_action( 'admin_menu', 'wpdp_menu' );

function wpdp_settings_page() {
	$colors = wpdp_colors();
	?>
	<div class="wrap">
		<h1>Dark Editor</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'wpdp_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="wpdp-background">Background</label></th>
					<td><input type="color" id="wpdp-background" name="wpdp_colors[background]" value="<?php echo esc_attr( $colors['background'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="wpdp-accent">Accent</label></th>
					<td>
						<input type="color" id="wpdp-accent" name="wpdp_colors[accent]" value="<?php echo esc_attr( $colors['accent'] ); ?>">
						<p class="description">Accent is used for links and text selection in the editor canvas. Text colour is chosen automatically based on the background.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Everywhere</th>
					<td>
						<label for="wpdp-everywhere">
							<input type="checkbox" id="wpdp-everywhere" name="wpdp_colors[everywhere]" value="1" <?php checked( $colors['everywhere'], '1' ); ?>>
							Apply the dark theme to the entire WordPress admin
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpdp_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=wp-dark-plugin' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">Settings</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpdp_action_links' );

function wpdp_preview_fix() {
	$screen = get_current_screen();
	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}
	?>
	<script>
	(function() {
		function clean() {
			var frames = document.querySelectorAll('.components-modal__frame iframe, [class*="editor-start-page"] iframe');
			Array.prototype.forEach.call(frames, function(f) {
				if (f.getBoundingClientRect().width > 700) return;
				try {
					var doc = f.contentDocument;
					if (!doc) return;
					var s = doc.getElementById('wpdp-canvas');
					if (s && s.parentNode) s.parentNode.removeChild(s);
				} catch (e) {}
			});
		}
		var t = null;
		var mo = new MutationObserver(function() {
			if (t) clearTimeout(t);
			t = setTimeout(clean, 120);
		});
		function start() {
			clean();
			mo.observe(document.body, { childList: true, subtree: true });
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', start);
		} else {
			start();
		}
	})();
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts', 'wpdp_preview_fix' );
