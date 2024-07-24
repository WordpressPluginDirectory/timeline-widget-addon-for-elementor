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
	  <li>Cool Plugins develops best timeline plugins for WordPress.</li>
	  <li>Our timeline plugins have <b>50000+</b> active installs.</li>
	  <li>For any query or support, please contact plugin support team.
	  <br><br>
	  <a href="<?php echo esc_attr( $cool_support_email ); ?>" target="_blank" class="button button-secondary">Premium Plugin Support</a>
	  <br><br>
	  </li>
   </ul>
	</div>

</div><!-- End of main container-->
