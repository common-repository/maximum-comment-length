<?php
/*
Plugin Name: Maximum Comment Length
Version: 0.1.1
Description: Check the comment for a set Maximum length and disapprove it if it's too long.
Author: Jan Janssen
Author URI: http://jan-janssen.com
Plugin URI: http://jan-janssen.com
Copyright 2009 Jan Janssen (email: mail@jan-janssen.com)

Based on the Minimum Comment Length Plugin v0.6
Plugin URI: http://yoast.com/wordpress/minimum-comment-length/
Author: Joost de Valk
Author URI: http://yoast.com/
Copyright 2008 Joost de Valk (email: joost@yoast.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists( 'MaxComLength_admin' ) ) {

	class MaxComLength_admin {
		
		function add_config_page() {
			global $wpdb;
			if ( function_exists('add_submenu_page') ) {
				add_options_page('Max Comment Length Configuration', 'Max Comment Length', 10, basename(__FILE__), array('MaxComLength_admin','config_page'));
				add_filter( 'plugin_action_links', array( 'MaxComLength_Admin', 'filter_plugin_actions'), 10, 2 );
				add_filter( 'ozh_adminmenu_icon', array( 'MaxComLength_Admin', 'add_ozh_adminmenu_icon' ) );								
			}
		}

		function add_ozh_adminmenu_icon( $hook ) {
			static $mclicon;
			if (!$mclicon) {
				$mclicon = WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)). '/comment_edit.png';
			}
			if ($hook == 'Maximum-comment-length.php') return $mclicon;
			return $hook;
		}

		function filter_plugin_actions( $links, $file ){
			//Static so we don't call plugin_basename on every plugin row.
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
			
			if ( $file == $this_plugin ){
				$settings_link = '<a href="options-general.php?page=Maximum-comment-length.php">' . __('Settings') . '</a>';
				array_unshift( $links, $settings_link ); // before other links
			}
			return $links;
		}
		
		function config_page() {
			// Set some defaults if no settings are set yet
			$options['Maxcomlength'] = 500;
			$options['Maxcomlengtherror'] = "Error: Your comment is too long. Please try to sum it up.";
			add_option('MaxComLengthOptions', $options);
			
			// Overwrite defaults with saved settings
			if ( isset($_POST['submit']) ) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Maximum Comment Length options.'));
				check_admin_referer('Maxcomlength-config');

				if (isset($_POST['Maxcomlength']) && $_POST['Maxcomlength'] != "" && is_numeric($options['Maxcomlength'])) 
					$options['Maxcomlength'] = $_POST['Maxcomlength'];

				if (isset($_POST['Maxcomlengtherror']) && $_POST['Maxcomlengtherror'] != "") 
					$options['Maxcomlengtherror'] = $_POST['Maxcomlengtherror'];

				update_option('MaxComLengthOptions', $options);
			}
			
			$options = get_option('MaxComLengthOptions');			
			?>
			<div class="wrap">
				<h2>Maximum Comment Length options</h2>
				<form action="" method="post" id="Maxcomlength-conf">
					<?php
					if ( function_exists('wp_nonce_field') )
						wp_nonce_field('Maxcomlength-config');
					?>
					<table class="form-table" style="width: 100%;">
						<tr valign="top">
							<th scrope="row">
								<label for="Maxcomlength">Maximum comment length:</label>
							</th>
							<td>
								<input type="text" value="<?php echo $options['Maxcomlength']; ?>" name="Maxcomlength" id="Maxcomlength" size="4"/>
							</td>
						</tr>
						<tr valign="top">
							<th scrope="row">
								<label for="Maxcomlengtherror">Error message:</label>
							</th>
							<td>
								<input type="text" value="<?php echo $options['Maxcomlengtherror']; ?>" name="Maxcomlengtherror" id="Maxcomlengtherror" size="50"/>
							</td>
						</tr>

					</table>
					<p class="submit"><input type="submit" name="submit" value="Update Settings &raquo;" /></p>
				</form>
			</div>
<?php		}	
	}
}

function check_comment_length($commentdata) {
	$options = get_option('Maxcomlength');
	if (!isset($options['Maxcomlength']) || $options['Maxcomlength'] == "" || !is_numeric($options['Maxcomlength']))
		$options['Maxcomlength'] = 500;

	if (!isset($options['Maxcomlengtherror']) || $options['Maxcomlengtherror'] == "")
		$options['Maxcomlengtherror'] = "Error: Your comment is too short. Please try to say something useful.";
	
	if (strlen(trim($commentdata['comment_content'])) > $options['Maxcomlength']) {
		wp_die( __($options['Maxcomlengtherror']) );
	} else {
		return $commentdata;
	}
}

add_filter('preprocess_comment','check_comment_length');
add_action('admin_menu', array('MaxComLength_admin','add_config_page'));
?>
