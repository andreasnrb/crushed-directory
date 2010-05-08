<?php
class DirectoryController extends BaseController{
	function index(){
		//$this->RenderToAction('overview');
		$this->bag['current_page']=esc_url(admin_url('admin.php?page=Directory&action=createnew&edit=plugins'));
		if(isset($_GET['edit'])){
			check_admin_referer('edit-plugins');
			if($_GET['edit']=="plugins"){
				$delete_plugins= $_POST['plugins'];
				foreach($delete_plugins as $id)
					wp_delete_post($id,true);
			}else if($_GET['edit']=='plugin')
				wp_delete_post($_GET['plugin'],true);			
		}
		$this->bag['plugins']=query_posts('post_type=plugin');
	}
	function createnew(){
		$this->bag['title']="Directory - Add new";
		$this->bag['currentpage']=esc_url(admin_url('admin.php?page=Directory&action=createnew&new=plugin'));
		if(isset($_GET['new']) && $_GET['new']=="plugin"){
			check_admin_referer('new-plugin');
			global $currentuser;
			$post['post_author']=$currentuser->ID;
			$post['post_excerpt']=esc_html($_POST['plugin_excerpt']);
			$post['post_type']='plugin';
			$post['post_content']=$_POST['plugin_excerpt'];
			$post['post_name']=$_POST['plugin-id'];
			$post['post_title']=$_POST['plugin-name'];
			$post['post_date_gmt']=current_time('mysql');
			$post['post_modified_gmt']=current_time('mysql');			
			$post['post_status']='private';
			$result=wp_insert_post($post);
			$this->bag['error']=$this->bag['error']."\nAn ".print_r($result,true);
			do_action('add_plugin_meta',$result);
		}
	}
	function edit(){
		global $wpdb;
		$this->bag['title']="Directory - Edit";
		$this->bag['currentpage']=admin_url('admin.php?page=Directory&action=edit&edit=plugin&plugin='.$_GET['plugin']);
		if(isset($_GET['edit']) && $_GET['edit']=="plugin"){
			check_admin_referer('edit-plugin');
			global $currentuser;
			$post['ID']=$wpdb->get_var($wpdb->prepare("SELECT `id` FROM $wpdb->posts WHERE `post_name`=%s AND `post_type`='plugin'",$_GET['plugin']));
			$post['post_author']=$currentuser->ID;
			$post['post_excerpt']=esc_html($_POST['plugin_excerpt']);
			$post['post_type']='plugin';
			$post['post_content']=$_POST['plugin_excerpt'];
			$post['post_name']=$_GET['plugin'];
			$post['post_title']=$_POST['plugin-name'];
			$post['post_date_gmt']=current_time('mysql');
			$post['post_modified_gmt']=current_time('mysql');			
			$post['post_status']='private';
			$result=wp_update_post($post);
			$this->bag['error']=$this->bag['error']."\nAn ".print_r($result,true);
			do_action('update_plugin_meta',$result);
		}
		$plugin=$wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE `post_name`=%s AND `post_type`='plugin'",$_GET['plugin']));
		$this->bag['plugin']=$plugin;
	}
}