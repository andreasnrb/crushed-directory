<?php
class Plugins{
	static function plugins_for_user($useremail){
		global $current_user;
		global $wpdb;
		$wpdb->set_blog_id(1,1);
		$plugins=$wpdb->get_results($wpdb->prepare("SELECT `ID`,`post_title`,`post_name`,`post_excerpt` FROM $wpdb->posts,`plugin_users`  WHERE `post_type`='plugin' AND `post_name`=`plugin` AND `user`=%s",$useremail));
		foreach($plugins as $plugin){
			$files=$wpdb->get_results($wpdb->prepare("SELECT `meta_value` FROM $wpdb->postmeta WHERE post_id=%d AND meta_key='file'",$plugin->ID),ARRAY_A);
			foreach($files as $file){
				$file=maybe_unserialize($file['meta_value']);
				$plugin->files[$file['slug']]=$file;
			}
			$plugin_access=$current_user->plugin_access;
			$plugin->key=$plugin_access[$plugin->post_name]['key'];
			$plugin->sites=$plugin_access[$plugin->post_name]['sites'];			
		}
		return $plugins;
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