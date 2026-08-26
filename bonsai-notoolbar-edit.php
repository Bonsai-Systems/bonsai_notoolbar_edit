<?php
/**
 * Plugin Name: Bonsai No Toolbar Edit
 * Plugin URI:  https://bonsaidigitalcollective.co.uk/
 * Description: Hides the WordPress admin toolbar on the front end and replaces it with two fixed top-right links: WP Dashboard and Edit Page.
 * Version:     1.0.0
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

define( 'BNE_VERSION', '1.0.0' );
define( 'BNE_CAPABILITY', apply_filters( 'bonsai_notoolbar_edit_capability', 'edit_posts' ) );

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
// Fixed Dashboard / Edit Page links (front end only)
// ---------------------------------------------------------------------------

add_action( 'wp_footer', 'bne_render_fixed_links' );
function bne_render_fixed_links() {
	if ( is_admin() || ! current_user_can( BNE_CAPABILITY ) ) {
		return;
	}

	$dashboard_url = admin_url();
	$edit_url      = is_singular() ? get_edit_post_link( get_queried_object_id(), '' ) : '';
	?>
	<div id="bne-fixed-links">
		<a href="<?php echo esc_url( $dashboard_url ); ?>" class="bne-fixed-links__link">
			<?php esc_html_e( 'WP Dashboard', 'bonsai-notoolbar-edit' ); ?>
		</a>
		<?php if ( $edit_url ) : ?>
			<a href="<?php echo esc_url( $edit_url ); ?>" class="bne-fixed-links__link">
				<?php esc_html_e( 'Edit Page', 'bonsai-notoolbar-edit' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<style>
		#bne-fixed-links {
			position: fixed;
			top: 0;
			right: 0;
			z-index: 99999;
			display: flex;
			gap: 8px;
			padding: 8px 12px;
			background: #1d2327;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		}
		#bne-fixed-links .bne-fixed-links__link {
			display: inline-block;
			padding: 4px 10px;
			border-radius: 3px;
			background: #2c3338;
			color: #fff;
			font-size: 13px;
			line-height: 1.4;
			text-decoration: none;
		}
		#bne-fixed-links .bne-fixed-links__link:hover,
		#bne-fixed-links .bne-fixed-links__link:focus {
			background: #ee4367;
			color: #fff;
		}
	</style>
	<?php
}
