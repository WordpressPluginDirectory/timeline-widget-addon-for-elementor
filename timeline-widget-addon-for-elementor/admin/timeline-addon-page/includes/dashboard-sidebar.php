<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 *
 * Addon dashboard sidebar.
 */

if ( ! isset( $this->main_menu_slug ) ) :
	return false;
 endif;

 $cool_support_email = 'https://coolplugins.net/support/?utm_source=twae-plugin&utm_medium=inside&utm_campaign=twae-free-dashboard';
?>

 <div class="cool-body-right">
	<a href="https://coolplugins.net/?utm_source=twae-plugin&utm_medium=inside&utm_campaign=twae-free-dashboard" target="_blank"><img src="<?php echo esc_url( TWAE_URL ) . 'admin/timeline-addon-page/assets/coolplugins-logo.png'; ?>"></a>
	<ul>
	  <li><?php echo esc_html__( 'Cool Plugins develops best timeline plugins for WordPress.', 'twae' ); ?></li>
	  <li><?php printf( esc_html__( 'Our timeline plugins have %1$s50000+%2$s active installs.', 'cool-timeline' ), '<b>', '</b>' ); ?></li>
	  <li>
		<?php echo esc_html__( 'For any query or support, please contact plugin support team.', 'twae' ); ?>
		<br><br>
		<a href="<?php echo esc_url( $cool_support_email ); ?>" target="_blank" class="button button-secondary">
			<?php echo esc_html__( 'Premium Plugin Support', 'twae' ); ?>
		</a>
		<br><br>
	  </li>
	</ul>
</div>

</div><!-- End of main container-->
