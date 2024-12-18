<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * This php file render HTML header for addons dashboard page
 */
if ( ! isset( $this->main_menu_slug ) ) :
	return;
endif;

$cool_plugins_docs      = 'https://cooltimeline.com/docs/?utm_source=twae-plugin&utm_medium=inside&utm_campaign=twae-free-dashboard';
$cool_plugins_more_info = TWAE_DEMO_URL;
?>

<div id="cool-plugins-container" class="cool-plugins-timeline-addon">
	<div class="cool-header">
		<h2 style=""><?php echo esc_html( $this->dashboar_page_heading ); ?></h2>
	<a href="<?php echo esc_url( $cool_plugins_docs ); ?>" target="_docs" class="button"><?php echo esc_html__( 'Docs', 'twae' ); ?></a>
	<a href="<?php echo esc_url( $cool_plugins_more_info ); ?>" target="_info" class="button"><?php echo esc_html__( 'Demos', 'twae' ); ?></a>
</div>
