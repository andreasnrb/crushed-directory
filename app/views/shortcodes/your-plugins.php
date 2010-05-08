<?php foreach($plugins as $plugin):
$file=array_pop($plugin->files);
?>
<h2><?php esc_html_e($plugin->post_title)?></h2>
<p>
<?php if(sizeof($plugin->sites)): ?>
Used on: <?php echo sizeof($plugin->sites) ?> sites<br />
<?php else: ?>
Currently not used on any sites. <br />
<?php endif; ?>
Registration key: <strong><?php _e($plugin->key) ?></strong><br />
Version <?php echo $file['version'] ?><br />
<a class="download" style="font-weight:bold;color:blue;text-decoration:underline" href="http://members.artofwp.com/?downloadfile=<?php esc_attr_e($file['slug'])?>">Download</a></p>
<p>Description: <?php esc_html_e($plugin->post_excerpt) ?></p>
<?php endforeach; ?>