<?php
/**
 * Plugin Name: Bonsai No Toolbar Edit
 * Plugin URI:  https://bonsaidigitalcollective.co.uk/
 * Description: Hides the WordPress admin toolbar on the front end and replaces it with two fixed icon links: WP Dashboard and Edit Page. Placement and hover colour are configurable under Bonsai → No Toolbar Edit.
 * Version:     1.4.0
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
	'https://github.com/Bonsai-Systems/bonsai_notoolbar_edit',
	__FILE__,
	'bonsai-notoolbar-edit',
	6
);

$bne_update_checker->setBranch( 'main' );
$bne_update_checker->getVcsApi()->enableReleaseAssets();

define( 'BNE_VERSION', '1.4.0' );
define( 'BNE_OPTION_GROUP', 'bne_settings_group' );
define( 'BNE_PAGE_SLUG', 'bonsai-notoolbar-edit' );
define( 'BNE_DEFAULT_HOVER_COLOR', '#ee4367' );
define( 'BNE_CAPABILITY', apply_filters( 'bonsai_notoolbar_edit_capability', 'edit_posts' ) );
define( 'BNE_SETTINGS_CAPABILITY', apply_filters( 'bonsai_notoolbar_edit_settings_capability', 'manage_options' ) );
define( 'BNE_URL', plugin_dir_url( __FILE__ ) );

// Shared Bonsai admin menu, page shell and suite installer. Bundled copy of
// the bonsai-hub repo; update it with bonsai-hub/bin/sync.sh, not by hand.
require_once plugin_dir_path( __FILE__ ) . 'lib/bonsai-hub/bonsai-hub.php';

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

	register_setting(
		BNE_OPTION_GROUP,
		'bne_hover_color',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'bne_sanitize_hover_color',
			'default'           => BNE_DEFAULT_HOVER_COLOR,
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

	add_settings_field(
		'bne_hover_color',
		__( 'Link Hover Colour', 'bonsai-notoolbar-edit' ),
		'bne_render_hover_color_field',
		BNE_PAGE_SLUG,
		'bne_main_section',
		array( 'label_for' => 'bne_hover_color' )
	);
}

/*
 * options.php checks manage_options for every option group unless told
 * otherwise, so a filtered BNE_SETTINGS_CAPABILITY could see the page but
 * not save.
 */
add_filter( 'option_page_capability_' . BNE_OPTION_GROUP, 'bne_option_page_capability' );
function bne_option_page_capability() {
	return BNE_SETTINGS_CAPABILITY;
}

/**
 * Sanitises the hover colour option, falling back to the brand default.
 *
 * @param mixed $value Raw option value.
 * @return string A valid hex colour.
 */
function bne_sanitize_hover_color( $value ) {
	$color = sanitize_hex_color( is_string( $value ) ? $value : '' );

	return $color ? $color : BNE_DEFAULT_HOVER_COLOR;
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

add_filter( 'bonsai_hub_modules', 'bne_register_hub_module' );
/**
 * Registers the settings screen under the shared Bonsai menu. Old
 * options-general.php?page=bonsai-notoolbar-edit links are redirected
 * here by the hub.
 *
 * @param array $modules Modules registered so far.
 * @return array
 */
function bne_register_hub_module( $modules ) {
	$modules[ BNE_PAGE_SLUG ] = array(
		'label'       => __( 'No Toolbar Edit', 'bonsai-notoolbar-edit' ),
		'title'       => __( 'Bonsai No Toolbar Edit', 'bonsai-notoolbar-edit' ),
		'description' => __( 'Hides the default admin toolbar on the front end for editors and shows fixed WP Dashboard / Edit Page icon links instead.', 'bonsai-notoolbar-edit' ),
		'version'     => BNE_VERSION,
		'repo'        => 'https://github.com/Bonsai-Systems/bonsai_notoolbar_edit',
		'capability'  => BNE_SETTINGS_CAPABILITY,
		'render'      => 'bne_render_settings_page',
	);
	return $modules;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bne_add_settings_link' );
function bne_add_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=' . BNE_PAGE_SLUG ) ) . '">' . esc_html__( 'Settings', 'bonsai-notoolbar-edit' ) . '</a>';
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
			<label>
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

function bne_render_hover_color_field() {
	$value = get_option( 'bne_hover_color', BNE_DEFAULT_HOVER_COLOR );
	?>
	<input
		type="color"
		id="bne_hover_color"
		name="bne_hover_color"
		value="<?php echo esc_attr( $value ); ?>"
	/>
	<code><?php echo esc_html( $value ); ?></code>
	<p class="description">
		<?php
		printf(
			/* translators: %s: default hex colour. */
			esc_html__( 'Background colour of the icon links on hover and keyboard focus. Defaults to %s.', 'bonsai-notoolbar-edit' ),
			esc_html( BNE_DEFAULT_HOVER_COLOR )
		);
		?>
	</p>
	<?php
}

/**
 * Settings form. The hub prints the page wrap, header, notices and nav
 * around it, loads the Bonsai styles, and has already checked
 * BNE_SETTINGS_CAPABILITY.
 *
 * @return void
 */
function bne_render_settings_page() {
	?>
	<form method="post" action="options.php">
		<section class="bonsai-ui-card" aria-labelledby="bne-links-title">
			<h2 class="bonsai-ui-card__title" id="bne-links-title"><?php esc_html_e( 'Icon links', 'bonsai-notoolbar-edit' ); ?></h2>
			<?php
			settings_fields( BNE_OPTION_GROUP );
			do_settings_sections( BNE_PAGE_SLUG );
			?>
		</section>
		<?php submit_button(); ?>
	</form>
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
	$hover_color  = bne_sanitize_hover_color( get_option( 'bne_hover_color', BNE_DEFAULT_HOVER_COLOR ) );
	?>
	<div id="bne-fixed-links">
		<a href="<?php echo esc_url( $dashboard_url ); ?>" class="bne-fixed-links__link" title="<?php esc_attr_e( 'WP Dashboard', 'bonsai-notoolbar-edit' ); ?>">
			<?php echo bne_get_icon_svg( 'dashboard' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, hardcoded SVG markup; no user input. ?>
			<span class="bne-fixed-links__label"><?php esc_html_e( 'WP Dashboard', 'bonsai-notoolbar-edit' ); ?></span>
		</a>
		<?php if ( $edit_url ) : ?>
			<a href="<?php echo esc_url( $edit_url ); ?>" class="bne-fixed-links__link" title="<?php esc_attr_e( 'Edit Page', 'bonsai-notoolbar-edit' ); ?>">
				<?php echo bne_get_icon_svg( 'edit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, hardcoded SVG markup; no user input. ?>
				<span class="bne-fixed-links__label"><?php esc_html_e( 'Edit Page', 'bonsai-notoolbar-edit' ); ?></span>
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
			line-height: 1;
		}
		#bne-fixed-links,
		#bne-fixed-links * {
			box-sizing: border-box;
		}
		#bne-fixed-links .bne-fixed-links__link {
			display: flex;
			flex: none;
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
			background: <?php echo esc_html( $hover_color ); ?>;
			color: #fff;
		}
		#bne-fixed-links .bne-fixed-links__link svg {
			display: block;
			flex: none;
			width: 18px !important;
			height: 18px !important;
			max-width: none;
			fill: none;
			stroke: currentColor;
		}
		/* Visually hidden, not relying on the theme to provide .screen-reader-text */
		#bne-fixed-links .bne-fixed-links__label {
			position: absolute !important;
			width: 1px !important;
			height: 1px !important;
			padding: 0 !important;
			margin: -1px !important;
			overflow: hidden !important;
			clip: rect(0, 0, 0, 0) !important;
			white-space: nowrap !important;
			border: 0 !important;
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
	delete_option( 'bne_hover_color' );
}
