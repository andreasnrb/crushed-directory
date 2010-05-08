<div class="wrap">
	<h2><?php echo $title; ?></h2>
	<div id="poststuff">
		<form name="form0" method="post" action="<?php echo $current_page ?>"
		style="border: none; background: transparent;"><?php wp_nonce_field( 'edit-mailmessage'); ?>
									<input id="mail-id" name="mail-id" value="<?php esc_attr_e($mail->ID);?>" type="hidden" />
		<div class="postbox open">
			<h3><?php _e('Add a new mail message', 'crusheddirectory'); ?></h3>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th style="width: 100px;"><strong><?php _e('About:', 'crusheddirectory'); ?></strong>
						</th>
						<td><?php _e('Here you can add a mail message that is sent out when certain events occur.', 'crusheddirectory'); ?>
						</td>
					</tr>
					<tr>
						<th><label for="mail-slug"><strong><?php _e('Mail Message ID:', 'crusheddirectory'); ?></strong></label></th>
						<td><?php _e('<strong>Required:</strong> Enter the ID of the message.  This is a unique key that should only contain numbers, letters, and underscores.  Please don\'t add spaces or other odd characters.', 'crusheddirectory'); ?>
							<br />
							<input id="mail-slug" name="mail-slug" value="<?php esc_attr_e($mail->slug);?>" type="text" size="30" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label for="mail-subject"><strong><?php _e('Message subject:', 'crusheddirectory'); ?></strong></label>
						</th>
						<td><?php _e('<strong>Required:</strong> Enter a subject for the mail.  This will be the subject of the mail.', 'crusheddirectory'); ?>
						<br />
						<input id="mail-subject" name="mail-subject" value="<?php esc_attr_e($mail->subject);?>" type="text" size="30" class="regular-text" /></td>
					</tr>
					<tr>
						<th colspan="2"><label for="mail_plain"><strong><?php _e('Mail plain','crusheddirectory')?></strong></label></th>
					</tr>
					<tr>
						<td colspan="2"><textarea id="mail_plain" name="mail-plain" cols="100"><?php echo stripslashes($mail->message_plain)?></textarea><br />
						<?php _e('<strong>Required:</strong> The mail message in plain text.', 'crusheddirectory'); ?>
						</td>
					</tr>
					<tr>
						<th colspan="2"><label for="mail_html"><strong><?php _e('Mail HTML','crusheddirectory')?></strong></label></th>
					</tr>
					<tr>
						<td colspan="2"><textarea id="mail_html" name="mail-html" cols="100"><?php echo stripslashes($mail->message_html)?></textarea><br />
						<?php _e('<strong>Required:</strong> The mail message in HTML.', 'crusheddirectory'); ?>
						</td>
					</tr>
					<tr>
						<th><label for="mail-event"><strong><?php _e('Send when event occurs:', 'crusheddirectory'); ?></strong></label>
						</th>
						<td><?php _e('If you want this email to be sent on a special event then write the event name below.', 'crusheddirectory'); ?>
						<br />
						<input id="mail-event" name="mail-event" value="<?php esc_attr_e($mail->event);?>" type="text" size="30" class="regular-text" /></td>
					</tr>					
					<?php do_action('new-mail-form');?>
				</table>
			</div>
		</div>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Add mail', 'crusheddirectory') ?>" />
		</p>
		</form>
	</div>
</div>