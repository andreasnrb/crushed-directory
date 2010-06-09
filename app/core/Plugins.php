<?php
class Plugins{
	static function plugins_for_current_user(){
		global $current_user;
		global $wpdb;
		$wpdb->set_blog_id(1,1);
		$plugins=$wpdb->get_results($wpdb->prepare("SELECT `ID`,`post_title`,`post_name`,`post_excerpt`,`post_content` FROM $wpdb->posts  WHERE `post_type`='plugin'"));// AND `user`=%s",$current_user->ID));
		$memberships=Memberships::get_memberships_for_user($current_user->ID);		
		foreach($plugins as $i => $plugin){
			$files=$wpdb->get_results($wpdb->prepare("SELECT `meta_value` FROM $wpdb->postmeta WHERE post_id=%d AND meta_key='file'",$plugin->ID),ARRAY_A);
			foreach($files as $file){
				$file=maybe_unserialize($file['meta_value']);
				$plugin->files[$file['slug']]=$file;
				$file_info=get_option($file['slug']);
				$file_info['groups'];
			}
			$group_access=array_pop(get_post_meta($plugin->ID,'group_access'));			
			$has_access=false;
			if(is_array($group_access))
			foreach($group_access as $group_id => $extra)
				if(array_key_exists($group_id,$memberships))
					$has_access=true;
			$plugin_access=$current_user->plugin_access;

			if(isset($plugin_access[$plugin->post_name])){
				$plugin->key=$plugin_access[$plugin->post_name]['key'];
				$plugin->sites=$plugin_access[$plugin->post_name]['sites'];
			}else if(!$has_access)
				unset($plugins[$i]);
		}
		return $plugins;
	}
	static function get_plugin($slug){
		global $current_user;		
		global $wpdb;
		$wpdb->set_blog_id(1,1);		
		$plugin=$wpdb->get_row($wpdb->prepare("SELECT `ID`,`post_title`,`post_name`,`post_excerpt`,`post_content` FROM $wpdb->posts  WHERE `post_type`='plugin' AND `post_name`=%s",$slug));
		$files=$wpdb->get_results($wpdb->prepare("SELECT `meta_value` FROM $wpdb->postmeta WHERE post_id=%d AND meta_key='file'",$plugin->ID),ARRAY_A);
		foreach($files as $file){
			$file=maybe_unserialize($file['meta_value']);
			$plugin->files[$file['slug']]=$file;
		}
		if(is_user_logged_in()){
			$plugin_access=$current_user->plugin_access;
			$plugin->key=$plugin_access[$plugin->post_name]['key'];
			$plugin->sites=$plugin_access[$plugin->post_name]['sites'];
		}
		$plugin->have_access=isset($plugin_access[$plugin->post_name]);
		return $plugin;
	}
	static function get_group_access($slug){
		global $wpdb;
		$id=$wpdb->get_var($wpdb->prepare("SELECT `id` FROM $wpdb->posts WHERE `post_name`=%s AND `post_type`='plugin'",$slug));
		return array_pop(get_post_meta($id,'group_access'));
	}
	static function has_access($plugin,$user_id){
		global $wpdb;
		$user=$wpdb->get_var($wpdb->prepare("SELECT `user` FROM `plugin_users` WHERE `plugin`=%s AND `user`=%s",$plugin,$user_id));		
		if($user)
			return true;
		return false;
	}
	static function get_plugin_users($plugin){
		global $wpdb;
		$plugins_users=$wpdb->get_results($wpdb->prepare("SELECT `user` FROM `plugin_users` WHERE `plugin`=%s",$plugin));
		return $plugin_users;
	}
	static function save_plugin_user($plugin,$user){
		global $wpdb;
		$wpdb->insert('plugin_users',array('plugin'=>$plugin,'user'=>$user),array('%s','%s'));
	}
}