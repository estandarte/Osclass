<?php
/*
 *      OSCLass – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2010 OSCLASS
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
?>

<?php defined('APP_PATH') or die(__('Invalid OSClass request.')); ?>

<script>
	$(function() {
		// Here we include specific jQuery, jQuery UI and Datatables functions.
	});
</script>
		<div id="content">
			<div id="separator"></div>	
			
			<?php include_once $absolute_path . '/include/backoffice_menu.php'; ?>

		    <div id="right_column">
				<div id="content_header" class="content_header">
					<div style="float: left;"><img src="<?php echo  $current_theme; ?>/images/back_office/settings-icon.png" /></div>
					<div id="content_header_arrow">&raquo; <?php echo __('Spam and bots'); ?></div> 
					<div style="clear: both;"></div>
				</div>
				
				<div id="content_separator"></div>
				<?php osc_showFlashMessages(); ?>
				
				<div id="settings_form" style="border: 1px solid #ccc; background: #eee; ">
					<div style="padding: 20px;">

						<form action="settings.php" method="post">
							<input type="hidden" name="action" value="spamNbots_post" />
							
						<fieldset>
							<legend><?php echo __('Akismet'); ?></legend>
							<p>
							<label for="akismetKey"><?php echo __('Akismet key (same as Wordpress.com)'); ?></label><br />
							<input type="text" name="akismetKey" id="akismetKey" value="<?php echo isset($preferences['akismetKey']) ? $preferences['akismetKey'] : ''; ?>" /><br />
							<span class="Explanation"><?php echo __('If the field is empty it is because the Akismet service is disabled'); ?>. <?php echo __('Get your free key at'); ?> <a href="http://akismet.com">http://akismet.com</a></span>.
							</p>
						</fieldset>
						
						<fieldset>
							<legend><?php echo __('Re-captcha'); ?></legend>							
							
							<p><?php echo __('If the field is empty it is because the reCAPTCHA service is disabled'); ?>. <?php echo __('Get your free keys at'); ?> <a href="http://recaptcha.net" target="_blank">http://recaptcha.net</a>.</p>
							
							<p>
							<label for="recaptchaPrivKey"><?php echo __('Re-captcha private key'); ?><br />
							<input type="text" name="recaptchaPrivKey" id="recaptchaPrivKey" value="<?php echo isset($preferences['recaptchaPrivKey']) ? $preferences['recaptchaPrivKey'] : ''; ?>" />
							</p>
							
							<p>
							<label for="recaptchaPubKey"><?php echo __('Re-captcha public key'); ?><br />
							<input type="text" name="recaptchaPubKey" id="recaptchaPubKey" value="<?php echo isset($preferences['recaptchaPubKey']) ? $preferences['recaptchaPubKey'] : ''; ?>" />
							</p>
						</fieldset>
						
						<input id="button_save" type="submit" value="<?php echo __('Update spam and bots configuration'); ?>" />
						</form>
					</div>
				</div>
			</div>
		</div>