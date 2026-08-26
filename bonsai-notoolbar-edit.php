<?php
/**
 * Plugin Name: Bonsai No Toolbar Edit
 * Plugin URI:  https://bonsaidigitalcollective.co.uk/
 * Description: Hides the WordPress admin toolbar on the front end and replaces it with two fixed icon links: WP Dashboard and Edit Page. Placement is configurable under Settings → No Toolbar Edit.
 * Version:     1.1.1
 * Author:      The Bonsai Digital Collective
 * Author URI:  https://bonsaidigitalcollective.co.uk/
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: bonsai-notoolbar-edit
 */

defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| Plugin Update Checker (via Composer)
|--------------------------------------------------------------------------
*/
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$bne_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/Bonsai-Systems/bonsai-notoolbar-edit',
	__FILE__,
	'bonsai-notoolbar-edit',
	6
);

$bne_update_checker->setBranch( 'main' );
$bne_update_checker->getVcsApi()->enableReleaseAssets();

define( 'BNE_VERSION', '1.1.1' );
define( 'BNE_OPTION_GROUP', 'bne_settings_group' );
define( 'BNE_PAGE_SLUG', 'bonsai-notoolbar-edit' );
define( 'BNE_CAPABILITY', apply_filters( 'bonsai_notoolbar_edit_capability', 'edit_posts' ) );
define( 'BNE_SETTINGS_CAPABILITY', apply_filters( 'bonsai_notoolbar_edit_settings_capability', 'manage_options' ) );

// ---------------------------------------------------------------------------
// Settings registration
// ---------------------------------------------------------------------------

add_action( 'admin_init', 'bne_register_settings' );
function bne_register_settings() {
	register_setting(
		BNE_OPTION_GROUP,
		'bne_placement',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'bne_sanitize_placement',
			'default'           => 'top-right',
		)
	);

	add_settings_section(
		'bne_main_section',
		'',
		'__return_false',
		BNE_PAGE_SLUG
	);

	add_settings_field(
		'bne_placement',
		__( 'Link Placement', 'bonsai-notoolbar-edit' ),
		'bne_render_placement_field',
		BNE_PAGE_SLUG,
		'bne_main_section'
	);
}

function bne_get_placements() {
	return array(
		'top-right'    => __( 'Top right', 'bonsai-notoolbar-edit' ),
		'top-left'     => __( 'Top left', 'bonsai-notoolbar-edit' ),
		'bottom-right' => __( 'Bottom right', 'bonsai-notoolbar-edit' ),
		'bottom-left'  => __( 'Bottom left', 'bonsai-notoolbar-edit' ),
	);
}

function bne_sanitize_placement( $value ) {
	$placements = bne_get_placements();

	if ( ! is_string( $value ) || ! array_key_exists( $value, $placements ) ) {
		return 'top-right';
	}

	return $value;
}

// ---------------------------------------------------------------------------
// Admin menu + page
// ---------------------------------------------------------------------------

add_action( 'admin_menu', 'bne_add_settings_page' );
function bne_add_settings_page() {
	add_options_page(
		__( 'Bonsai No Toolbar Edit', 'bonsai-notoolbar-edit' ),
		__( 'No Toolbar Edit', 'bonsai-notoolbar-edit' ),
		BNE_SETTINGS_CAPABILITY,
		BNE_PAGE_SLUG,
		'bne_render_settings_page'
	);
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bne_add_settings_link' );
function bne_add_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=' . BNE_PAGE_SLUG ) ) . '">' . esc_html__( 'Settings', 'bonsai-notoolbar-edit' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

function bne_render_placement_field() {
	$value      = get_option( 'bne_placement', 'top-right' );
	$placements = bne_get_placements();
	?>
	<fieldset>
		<legend class="screen-reader-text"><?php esc_html_e( 'Link Placement', 'bonsai-notoolbar-edit' ); ?></legend>
		<?php foreach ( $placements as $key => $label ) : ?>
			<label style="display:block;margin-bottom:4px;">
				<input
					type="radio"
					name="bne_placement"
					value="<?php echo esc_attr( $key ); ?>"
					<?php checked( $value, $key ); ?>
				/>
				<?php echo esc_html( $label ); ?>
			</label>
		<?php endforeach; ?>
	</fieldset>
	<p class="description">
		<?php esc_html_e( 'Where the WP Dashboard and Edit Page icons appear on the front end.', 'bonsai-notoolbar-edit' ); ?>
	</p>
	<?php
}

function bne_render_settings_page() {
	if ( ! current_user_can( BNE_SETTINGS_CAPABILITY ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bonsai-notoolbar-edit' ) );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Bonsai No Toolbar Edit', 'bonsai-notoolbar-edit' ); ?></h1>
		<p><?php esc_html_e( 'Hides the default admin toolbar on the front end for editors and shows fixed WP Dashboard / Edit Page icon links instead.', 'bonsai-notoolbar-edit' ); ?></p>
		<form method="post" action="options.php">
			<?php
			settings_fields( BNE_OPTION_GROUP );
			do_settings_sections( BNE_PAGE_SLUG );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Hide the front-end admin toolbar
// ---------------------------------------------------------------------------

add_filter( 'show_admin_bar', 'bne_maybe_hide_admin_bar' );
function bne_maybe_hide_admin_bar( $show ) {
	if ( is_admin() ) {
		return $show;
	}

	if ( current_user_can( BNE_CAPABILITY ) ) {
		return false;
	}

	return $show;
}

// ---------------------------------------------------------------------------
// Fixed Dashboard / Edit Page icon links (front end only)
// ---------------------------------------------------------------------------

/**
 * Inline SVG icons. Kept here rather than loading an icon font so the
 * links always render, regardless of whether the active theme dequeues
 * dashicons on the front end.
 */
function bne_get_icon_svg( $icon ) {
	$icons = array(
		'dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>',
		'edit'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>',
	);

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : '';
}

add_action( 'wp_footer', 'bne_render_fixed_links' );
function bne_render_fixed_links() {
	if ( is_admin() || ! current_user_can( BNE_CAPABILITY ) ) {
		return;
	}

	$dashboard_url = admin_url();
	$edit_url      = is_singular() ? get_edit_post_link( get_queried_object_id(), '' ) : '';
	$placement     = get_option( 'bne_placement', 'top-right' );

	$position_css = array(
		'top-right'    => 'top:0;right:0;',
		'top-left'     => 'top:0;left:0;',
		'bottom-right' => 'bottom:0;right:0;',
		'bottom-left'  => 'bottom:0;left:0;',
	);
	$position     = isset( $position_css[ $placement ] ) ? $position_css[ $placement ] : $position_css['top-right'];
	?>
	<div id="bne-fixed-links">
		<a href="<?php echo esc_url( $dashboard_url ); ?>" class="bne-fixed-links__link" title="<?php esc_attr_e( 'WP Dashboard', 'bonsai-notoolbar-edit' ); ?>">
			<?php echo bne_get_icon_svg( 'dashboard' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, hardcoded SVG markup; no user input. ?>
			<span class="screen-reader-text"><?php esc_html_e( 'WP Dashboard', 'bonsai-notoolbar-edit' ); ?></span>
		</a>
		<?php if ( $edit_url ) : ?>
			<a href="<?php echo esc_url( $edit_url ); ?>" class="bne-fixed-links__link" title="<?php esc_attr_e( 'Edit Page', 'bonsai-notoolbar-edit' ); ?>">
				<?php echo bne_get_icon_svg( 'edit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, hardcoded SVG markup; no user input. ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Edit Page', 'bonsai-notoolbar-edit' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
	<style>
		#bne-fixed-links {
			position: fixed;
			<?php echo esc_html( $position ); ?>
			z-index: 99999;
			display: flex;
			gap: 8px;
			padding: 8px;
		}
		#bne-fixed-links .bne-fixed-links__link {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 32px;
			height: 32px;
			border-radius: 3px;
			background: #1d2327;
			color: #fff;
			text-decoration: none;
		}
		#bne-fixed-links .bne-fixed-links__link:hover,
		#bne-fixed-links .bne-fixed-links__link:focus {
			background: #ee4367;
			color: #fff;
		}
		#bne-fixed-links .bne-fixed-links__link svg {
			display: block;
			width: 18px;
			height: 18px;
		}
	</style>
	<?php
}

// ---------------------------------------------------------------------------
// Uninstall cleanup
// ---------------------------------------------------------------------------

register_uninstall_hook( __FILE__, 'bne_uninstall' );
function bne_uninstall() {
	delete_option( 'bne_placement' );
}
