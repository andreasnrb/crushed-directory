<div class="wrap">

	<h2><?php echo $title; ?></h2>

	<div id="poststuff">
<pre>Error:<?php echo $error; ?></pre>
		<form name="form0" method="post" action="<?php echo $currentpage ?>" style="border:none;background:transparent;">

			<?php wp_nonce_field( 'new-plugin'); ?>

			<div class="postbox open">

				<h3><?php _e('Add a new plugin', 'crusheddirectory'); ?></h3>

				<div class="inside">

					<table class="form-table">
					<tr>
						<th style="width:100px;">
							<strong><?php _e('About:', 'crusheddirectory'); ?></strong>
						</th>
						<td>
							<?php _e('Here you can add a plugin for updating and registration support ', 'crusheddirectory'); ?>
						</td>
					</tr>
					<tr>
						<th>
							<label for="plugin-id"><strong><?php _e('Plugin ID:', 'crusheddirectory'); ?></strong></label>
						</th>
						<td>
							<?php _e('<strong>Required:</strong> Enter the ID of the plugin.  This is a unique key that should only contain numbers, letters, and underscores.  Please don\'t add spaces or other odd characters.', 'crusheddirectory'); ?>
							<br />
							<input id="plugin-id" name="plugin-id" value="<?php esc_attr_e($plugin->id);?>" type="text" size="30" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th>
							<label for="plugin-name"><strong><?php _e('Plugin Label:', 'crusheddirectory'); ?></strong></label>
						</th>
						<td>
							<?php _e('<strong>Required:</strong> Enter a label for the plugin.  This will be the title that is displayed in most cases.', 'crusheddirectory'); ?>
							<br />
							<input id="plugin-name" name="plugin-name" value="<?php esc_attr_e($plugin->name);?>" type="text" size="30" class="regular-text"/>
						</td>
					</tr>
					<tr>
					<th colspan="2"><label for="plugin_excerpt"><strong><?php _e('Plugin excerpt','crusheddirectory')?></strong></label></th>
					</tr>
					<tr>
					<td colspan="2">
					<textarea id="plugin_excerpt" name="plugin_excerpt" cols="100"><?php echo stripslashes($plugin->excerpt)?></textarea><br />
					<?php _e('<strong>Required:</strong> Enter a non-HTML shortdescription for the plugin.', 'crusheddirectory'); ?>
					</td>
					</tr>
					<tr>
					<td colspan="2">
					<textarea id="plugin_description" name="plugin_description" cols="100"><?php echo stripslashes($plugin->description)?></textarea><br />
					<?php _e('<strong>Required:</strong> HTML description for the plugin.', 'crusheddirectory'); ?>
					</td>
					</tr>					
					<?php do_action('new-plugin-form');?>
					</table><!-- .form-table -->

				</div><!-- .inside -->

			</div><!-- .postbox -->

			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php _e('Add plugin', 'crusheddirectory') ?>" />
			</p><!-- .submit -->

		</form>

	</div><!-- #poststuff -->

</div><!-- .poststuff -->