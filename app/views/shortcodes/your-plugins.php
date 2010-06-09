<p>Your registration key: <?php echo $key ?></p>
<?php foreach($plugins as $plugin):
$file=array_pop($plugin->files);
?>
<h2><a href="http://members.artofwp.com/plugins/<?php echo $plugin->post_name?>"><?php esc_html_e($plugin->post_title)?></a></h2>
<p>
<?php if(sizeof($plugin->sites)): ?>
Used on <?php echo sizeof($plugin->sites) ?> sites: <?php echo implode(',',$plugin->sites) ?><br />
<?php else: ?>
Currently not used on any sites. <br />
<?php endif; ?>
Version <?php echo $file['version'] ?><br />
<a class="download" style="font-weight:bold;color:blue;text-decoration:underline" href="http://members.artofwp.com/?downloadfile=<?php esc_attr_e($file['slug'])?>">Download</a></p>
<p>Description: <?php echo $plugin->post_excerpt ?></p>
<p><a href="http://members.artofwp.com/plugins/<?php echo $plugin->post_name?>">Instructions & Requirements</a></p>
<?php endforeach; ?>