<div class="plugin">
<?php if($title):?>
<h2><?php esc_html_e($plugin->post_title)?></h2>
<?php endif;?>
<p>Latest version: <?php echo $file['version'] ?></p>
<h3>Description</h3>
<?php echo $plugin->post_content ?>
<p>
<?php if($plugin->has_access && $secure):?>
<a class="download" style="font-weight:bold;color:blue;text-decoration:underline" href="http://members.artofwp.com/?downloadfile=<?php esc_attr_e($file['slug'])?>">Download</a>
<?php elseif(!$secure):?>
<a class="download" style="font-weight:bold;color:blue;text-decoration:underline" href="http://artofwp.com/?free_update_file=<?php esc_attr_e($file['slug'])?>">Download</a>
<?php else: ?>
<span class="noaccess">You don't have access to this plugin.</span>
<?php endif;?>
</p>
</div>