<div class="wrap">
<div id="icon-edit" class="icon32"></div>
<h2><?php _e('Mail Messages','crusheddirectory')?><?php HtmlHelper::a('Add new',admin_url('admin.php?page=Mail&action=createnew'),'button-secondary');?></h2>
<div>
<p class="search-box">
	<label class="screen-reader-text" for="post-search-input">Search messages:</label>
	<input id="post-search-input" name="s" value="" type="text">
	<input value="Search messages" class="button" type="submit">
</p>

</div>
<div class="posstuff">
		<form id="mailmessages" action="<?php echo $current_page; ?>" method="post">

			<?php wp_nonce_field('edit-messages'); ?>

			<ul class="subsubsub">
				<li><a class="current" href="<?php echo admin_url( 'admin.php?page=Mail' ); ?>"><?php _e('All', 'crusheddirectory'); ?> <span class="count">(<span id="all_count"><?php echo sizeof($mailmessages); ?></span>)</span></a></li>
			</ul><!-- .subsubsub -->

			<div class="tablenav">

				<div class="alignleft actions">
					<select name="action">
						<option value="" selected="selected"><?php _e('Bulk Actions', 'crusheddirectory'); ?></option>
						<option value="delete"><?php _e('Delete', 'crusheddirectory')?></option>
					</select>
					<input type="submit" value="<?php _e('Apply', 'crusheddirectory'); ?>" name="doaction" id="doaction" class="button-secondary action" />
				</div><!-- .alignleft .actions -->

				<br class="clear" />

			</div><!-- .tablenav -->
			<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th class='check-column'><input type='checkbox' /></th>
						<th class='name-column'><?php _e('Name', 'crusheddirectory'); ?></th>
						<th><?php _e('Slug Name', 'crusheddirectory'); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th class='check-column'><input type='checkbox' /></th>
						<th class='name-column'><?php _e('Name', 'crusheddirectory'); ?></th>
						<th><?php _e('Slug Name', 'crusheddirectory'); ?></th>
					</tr>
				</tfoot>
<tbody>
<?php foreach($mailmessages as $mailmessage):?>
<tr>
	<th class="manage-column column-cb check-column">
	<input name="mailmessages[<?php echo $mailmessage->ID; ?>]" id="<?php echo $mailmessage->ID; ?>" type="checkbox" value="<?php echo $mailmessage->ID; ?>" />
	</th><!-- .manage-column .column-cb .check-column -->
<td class='mailmessage-title'>
<?php $edit_link = admin_url( wp_nonce_url( "admin.php?page=Mail&amp;action=edit&amp;mailmessage={$mailmessage->post_name}", members_get_nonce( 'edit-groups' ) ) ); ?> 
<a href="<?php echo $edit_link; ?>" title="<?php printf( __('Edit the %1$s mailmessage', 'crusheddirectory'), $mailmessage->post_title ); ?>"><strong><?php echo $mailmessage->post_title; ?></strong></a>
<div class="row-actions">
								<a href="<?php echo $edit_link; ?>" title="<?php printf( __('Edit the %1$s group', 'crusheddirectory'), $mailmessage->post_title ); ?>"><? _e('Edit', 'crusheddirectory'); ?></a> 
								<?php /* Delete group link. */
									$delete_link = admin_url( wp_nonce_url( "admin.php?page=Mail&amp;edit=mailmessage&amp;mailmessage={$mailmessage->ID}", 'edit-mailmessages') ); ?>
									| <a href="<?php echo $delete_link; ?>" title="<?php printf( __('Delete the %1$s mailmessage', 'crusheddirectory'), $mailmessage->post_title); ?>"><?php _e('Delete', 'crusheddirectory'); ?></a>
							</div><!-- .row-actions -->
</td>
<td><?php _e($mailmessage->post_name)?></td>
<td>0</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
</form>
</div>
</div>