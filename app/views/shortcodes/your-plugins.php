<p>Your registration key: <?php echo $key ?></p>
<?php foreach($plugins as $plugin):?>
<h2><a href="http://members.artofwp.com/products/<?php echo $plugin->post_name?>"><?php esc_html_e($plugin->post_title)?></a></h2>
<?php if(sizeof($plugin->sites)): ?>
<form action="http://members.artofwp.com/your-plugins" method="POST">
<p>
Used on <?php echo sizeof($plugin->sites) ?> sites: </p>
<div style="width:100%;height:50px;border:solid 1px #000;padding-left:10px;"><p>
<?php 
$sites=$plugin->sites;
foreach($sites as $site):?>
<label><?php echo esc_html($site) ?></label><input id="site[]" name="site[]" value="<?php echo esc_attr($site)?>" type="checkbox" /><br />
<?php endforeach;?>
</div>
<input name="plugin" value="<?php echo esc_attr($plugin->post_name) ?>" type="hidden" />
<input id="unregister" name="unregister" type="submit" value="Unregister"/>
</p>
</form>
<?php else: ?>
<p>Currently not used on any sites. </p>
<?php endif; ?>

<p>
Version <?php echo $plugin->version ?><br />
<a class="download" style="font-weight:bold;color:blue;text-decoration:underline" href="http://members.artofwp.com/?downloadfile=<?php esc_attr_e($plugin->post_name)?>">Download</a></p>
<p>Description: <?php echo $plugin->post_excerpt ?></p>
<p><a href="http://members.artofwp.com/products/<?php echo $plugin->post_name?>">Instructions & Requirements</a></p>
<?php endforeach; ?>