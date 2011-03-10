<p>Your registration key: <?php echo $key ?></p>
<ul>
<?php foreach($plugins as $plugin):?>
<li><a href="#<?php echo $plugin->post_name ?>"><?php esc_html_e($plugin->post_title)?></a> - <a href="http://members.artofwp.com/?downloadfile=<?php esc_attr_e($plugin->post_name)?>">Download</a></li>
<?php endforeach; ?>
</ul>
<hr style="border-bottom:dashed #000 4px"/>
<?php foreach($plugins as $plugin):?>
<h2 id="<?php echo $plugin->post_name?>"><a href="http://members.artofwp.com/products/<?php echo $plugin->post_name?>"><?php esc_html_e($plugin->post_title)?></a></h2>
<?php if(sizeof($plugin->sites)): ?>
<form action="http://members.artofwp.com/your-plugins" method="POST">
<p>Used on <?php echo sizeof($plugin->sites) ?> sites.<br />(To unregister a site click the box next to the domain and click unregister)</p>
<div style="width:100%;height:50px;border:solid 1px #000;padding-left:10px;overflow:auto;">
<ul style="list-style: none;margin:0;padding:0;">
<?php 
$sites=$plugin->sites;
sort($sites);
foreach($sites as $site):?>
<li style="float:left;list-style: none;margin-right:10px;"><label><?php echo esc_html($site) ?></label><input id="site[]" name="site[]" value="<?php echo esc_attr($site)?>" type="checkbox" /></li>
<?php endforeach;?>
</ul>
<div style="clear:both"></div>
</div>
<input name="plugin" value="<?php echo esc_attr($plugin->post_name) ?>" type="hidden" />
<p>
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
<hr style="border-bottom:dashed #000 4px"/>
<?php endforeach; ?>