<?php
/*
 Plugin Name: CrushedDirectory
 Plugin URI: http://artofwp.com/registryplugin
 Description: xxxx
 Version: 10.3
 Author: Cyonite Systems
 Author URI: http://cyonitesystems.com/
 */

if(!class_exists("AoiSOra")){
	add_action('after_plugin_row_'.plugin_basename(__FILE__),'after_aoisora_plugin_row', 10, 2 );					
	function after_aoisora_plugin_row($plugin_file, $plugin_data){
		echo '<tr class="error" style=""><td colspan="3" class="" style=""><div class="" style="padding:3px 3px 3px 3px;font-weight:bold;font-size:8pt;border:solid 1px #CC0000;background-color:#FFEBE8">Crushed Directory requires <a style="color:blue;text-decoration:underline;" href="http://artofwp.com/aoisora">PHP MVC For WordPress (AoiSora)</a></div></td></tr>';
		deactivate_plugins(plugin_basename(__FILE__));
	}
	return;
}
loadApp(plugin_dir_path(__FILE__));
class CrushedDirectory extends WpApplicationBase{
	function CrushedDirectory(){
		parent::WpApplicationBase('CrushedDirectory',__FILE__,false,false);
		add_shortcode('yourplugins', array($this,'your_plugins_shortcode'));		
	}
	
	function on_admin_menu(){
		$pages= new AdminPages($this,'Directory','Directory',8,'CDirectoryAdmin');
		$pages->addMenu();
		$pages->addSubmenu('Overview','Overview',8,'CDirectoryAdmin','overview');
		$pages->addSubmenu('Plugins','Plugins',8,'Directory','plugins');
		$pages->addSubmenu('Mail Messages','Mail Messages',8,'Mail','mail');
	}
	
	function on_init(){
//		if(is_user_logged_in())
		add_action( 'template_redirect', array($this,'update'));
		add_action( 'template_redirect', array($this,'update_file'));
		add_action( 'template_redirect', array($this,'free_update'));
		add_action( 'template_redirect', array($this,'free_update_file'));
		add_action( 'template_redirect', array($this,'downloadfiles'));
		add_action('paypal',array($this,'paypal_payment_recieved'),1000,2);	
	}
	
	function on_init_admin(){
		if(defined('DOING_AJAX') && DOING_AJAX){
			add_action('wp_ajax_nopriv_awpregister',array(&$this,'register'));
			add_action('wp_ajax_nopriv_awpunregister',array(&$this,'unregister'));
			if(is_user_logged_in()){
				add_action('wp_ajax_awpregister',array(&$this,'register'));
				add_action('wp_ajax_awpunregister',array(&$this,'unregister'));
			}
			return;
		}
		if(function_exists('is_multisite') && is_multisite())
			add_action('wpmu_delete_user',array($this, 'remove_plugin_access'));
		else
			add_action('delete_user',array($this, 'remove_plugin_access'));
		add_action('new_button_form',array($this,'paypal'));
		add_action('edit_button_form',array($this,'paypal_edit'));
		add_filter('get_button_from_post',array($this,'paypal_pre_add'),10,1);
		add_filter('pre_save_button',array($this,'paypal_pre_add'),10,1);
		add_action('new-plugin-form',array($this,'group_access_restrictions'));
		add_action('edit-plugin-form',array($this,'edit_group_access_restrictions'));
		add_action('edit-plugin-form',array($this,'edit_plugin_form_files'));
		add_action('update_plugin_meta',array($this,'update_plugin_meta'));
		add_action('update_plugin_meta',array($this,'update_plugin_files_meta'),1);
		$supports = array( 'excerpts', 'custom-fields' );
		$args = array (
		    'label' => __('Plugin'),
		    '_show' => true,
		    '_edit_link' => 'post.php?post=%d',
		    'capability_type' => 'post',
		    'hierarchical' => false,
			'publicly_queryable'=>false,
			'exclude_from_search' => true,
		'internal' => true,
		'supports'=>$supports
		);
		register_post_type( 'plugin' , $args );
		$args = array (
		    'label' => __('MailMessage'),
		    '_show' => true,
		    '_edit_link' => 'post.php?post=%d',
		    'capability_type' => 'post',
		    'hierarchical' => false,
			'publicly_queryable'=>false,
			'exclude_from_search' => true,
		'internal' => true,
		'supports'=>$supports
		);
		register_post_type( 'mailmessage' , $args );
	}
	
	function instantiate(){
		new CrushedDirectory();
	}
	
	function update_plugin_meta($id){
		$access_levels=$_POST['access_levels'];
		if(sizeof($access_levels)>0)
		update_post_meta($id,'group_access',$_POST['access_levels']);
		else
		update_post_meta($id,'group_access',0);
	}

	function on_admin_print_scripts(){
		if(isset($_GET['action']) && ($_GET['action']=='createnew' || $_GET['action']=='edit')){?>
<script type="text/javascript">
			
			function add_access_level(){
				group_id=jQuery("#group_access").val();
				group_name=jQuery('#group_access :selected').text();
				sites=jQuery("#group_register_sites").val();
				if(jQuery("#"+group_id).length!=0)
					jQuery("#"+group_id).replaceWith("<li id=\""+group_id+"\"><span>"+group_name+": </span><span>"+sites+"</span>"+
							"<input type=\"hidden\" name=\"access_levels["+group_id+"][group_id]\" value=\""+group_id+"\"/>"+
							"<input type=\"hidden\" name=\"access_levels["+group_id+"][sites]\" value=\""+sites+"\"/>"+
							"<a href=\"#\" onclick=\"removeAccesssLevel('"+group_id+"')\">(x)</a></li>");
				else
					jQuery("<li id=\""+group_id+"\"><span>"+group_name+": </span><span>"+sites+"</span>"+
							"<input type=\"hidden\" name=\"access_levels["+group_id+"][group_id]\" value=\""+group_id+"\"/>"+
							"<input type=\"hidden\" name=\"access_levels["+group_id+"][sites]\" value=\""+sites+"\"/>"+
							"<a href=\"#\" onclick=\"removeAccesssLevel('"+group_id+"')>(x)</a></li>").appendTo("#access_levels");
			}
			function removeAccesssLevel(id){
				jQuery("#"+id).remove();
			}
			</script>
		<?php 	}
	}
	
	function edit_group_access_restrictions($plugin){
		$group_access=get_post_meta($plugin->ID,'group_access');
		$group_access=$group_access[0];
		$this->group_access_restrictions($group_access);
	}
	
	function group_access_restrictions($group_access=false){
		$groups=Groups::get_groups();
		?>
<tr>
	<th style="width: 100px"><label>Access levels:</label></th>
	<td>
	<ul id="access_levels">
	<?php if($group_access):?>
	<?php foreach($group_access as $group_id => $extra):?>
	<?php $group=Groups::get_group($group_id);?>
		<li id="<?php echo $group_id?>"><span><?php echo $group['name'] ?>: </span><span><?php echo $extra['sites']?></span>
		<input type="hidden"
			name="access_levels[<?php echo $group_id?>][group_id]"
			value="<?php echo $group_id?>" /> <input type="hidden"
			name="access_levels[<?php echo $group_id?>][sites]"
			value="<?php echo $extra['sites']?>" /> <a href="#"
			onclick="removeAccesssLevel('<?php echo $group_id ?>')">(x)</a></li>
			<?php endforeach;?>
			<?php endif;?>
	</ul>
	</td>
</tr>
<tr>
	<td colspan="2"><label
		style="width: 50px; display: block; float: left; font-weight: bold;">Group:</label>
	<select id="group_access" name="group_access"
		style="float: left; margin-right: 20px">
		<?php foreach($groups as $group => $extra):?>
		<option value="<?php echo esc_attr($group)?>"><?php echo esc_html(trim($extra['name']))?></option>
		<?php endforeach;?>
	</select> <label
		style="width: 130px; display: block; float: left; font-weight: bold;">Number
	of sites:</label> <input id="group_register_sites" value="" type="text"
		size="5" style="width: 30px" /> (*=unlimited) <input type="button"
		name="add_access" class="button-secondary"
		value="<?php _e('Add access', 'crusheddirectory') ?>"
		onclick="add_access_level()" /></td>
</tr>
		<?php
	}
	
	function paypal_payment_recieved($txn,$post){
		global $membersext;
		$encoded_id=$post['item_number'];
		$button=PayPalButtons::get_encoded_button($encoded_id,false);
		if(!isset($button->plugin_access))
		return;
		$email=$post['payer_email'];
		$user=get_user_by_email($email);
		paypal_log_info('crushed-dir user:',"<pre>".print_r($user,true)."</pre>");
		
		$plugin_access=(array)get_usermeta($user->ID,'plugin_access');
		if(array_search($button->plugin_access,$plugin_access)===false)
			$plugin_access[$button->plugin_access]['key']=substr(hash('md5',$user->ID.time()),0,12);
		$plugin_access[$button->plugin_access]['sites']=array();
		ksort($plugin_access);
		update_usermeta($user->ID,'plugin_access',$plugin_access);
		if(!Plugins::has_access($button->plugin_access,$user->ID)){
			Plugins::save_plugin_user($button->plugin_access,$user->ID);
		}
		
		paypal_log_info('crushed-dir new user:',"<pre>".print_r($membersext->new_user,true)."</pre>");		
		if($membersext->new_user){			
			$user->password=$membersext->new_user['user_pass'];
			$userdata['username']=$user->user_login;
			$userdata['password']=$user->password;
			$userdata['first_name']=get_usermeta($user->ID,'first_name');
			$userdata['last_name']=get_usermeta($user->ID,'last_name');
			$userdata['email']=$email;
			$userdata['product']=$plugin->post_title;
			$userdata['to']=$email;
			$reciept['transaction']=$post['txn_id'];
			$reciept['first_name']=$userdata['first_name'];
			$reciept['last_name']=$userdata['last_name'];
			$reciept['email']=$post['payer_email'];
			$reciept['to']=$email;
			$reciept['item']=$plugin->post_title;
			$reciept['cost']=$post['mc_gross'];
			$reciept['date']=$post['payment_date'];
			ResponseMails::send_mails($userdata,'new-user');
			//			ResponseMails::send_mails($reciept,'reciept');
		}
		//		$sent=ResponseMails::send_first_plugin_bought($user->ID,$plugin,$access);
	}

	function paypal_pre_add($button){
		if($_POST['plugin_access']!='0')
		$button['plugin_access']=$_POST['plugin_access'];
		return $button;
	}
	
	function paypal_edit($button){
		$this->paypal($button->plugin_access);
	}
	
	function paypal($plugin_name){
		$plugins=query_posts('post_type=plugin');
		?>
<tr>
	<th><label>Plugin access</label></th>
	<td><select id="plugin_access" name="plugin_access">
		<option value="0"><?php _e('Select what to sell','membersext')?></option>
		<?php foreach($plugins as $plugin):?>
		<option value="<?php echo $plugin->post_name?>"
		<?php selected($plugin->post_name,$plugin_name)?>><?php _e($plugin->post_title)?></option>
		<?php endforeach;?>
	</select>

</tr>
		<?php 	}
		function remove_plugin_access($user_id){
			/*		$plugin_access=get_usermeta($user->ID,'plugin_access');
			 if(array_search($button->plugin_access,$plugin_access)===false)
			 $plugin_access[$button->plugin_access]=substr(hash('md5',time()),0,12);
			 ksort($plugin_access);
			 update_usermeta($user->ID,'plugin_access',$plugin_access);*/
		}
		function update_plugin_files_meta($id){
			if(isset($_POST['plugin-filename'])){
				$filename=$_POST['plugin-filename'];
				$fileurl=$_POST['plugin-fileurl'];
				$version=$_POST['plugin-version'];
				$fileslug = strip_tags($filename);
				$fileslug = str_replace( array( '_', ' ', '&nbsp;' ) , '-', $fileslug );
				$fileslug=strtolower($fileslug);
//				$fileslug = preg_replace('/[^A-Za-z0-9_]/', '', $fileslug );
				$fileslug = strtolower( $fileslug );
				$access_levels=is_array($_POST['access_levels'])?array_keys($_POST['access_levels']):array();
				update_post_meta($id,'file',array('slug'=>$fileslug, 'name'=>$filename,'url'=>$fileurl,'version'=>$version));
				update_option($fileslug,array('name'=>$filename,'version'=>$version,'url'=>$fileurl,'groups'=>$access_levels));
			}
		}
		function edit_plugin_form_files($plugin){
			$file=(object)array_pop(get_post_meta($plugin->ID,'file'));
			?>
<tr>
	<th><label for="plugin-filename"><strong><?php _e('Plugin Filename:', 'crusheddirectory'); ?></strong></label>
	</th>
	<td><?php _e('A label for the plugin file.', 'crusheddirectory'); ?> <br />
	<input id="plugin-filename" name="plugin-filename"
		value="<?php esc_attr_e($file->name);?>" type="text" size="30"
		class="regular-text" /></td>
</tr>
<tr>
	<th><label for="plugin-version"><strong><?php _e('Plugin Version:', 'crusheddirectory'); ?></strong></label>
	</th>
	<td><?php _e('File version number.', 'crusheddirectory'); ?> <br />
	<input id="plugin-version" name="plugin-version"
		value="<?php esc_attr_e($file->version);?>" type="text" size="30"
		class="regular-text" /></td>
</tr>
<tr>
	<th><label for="plugin-fileurl"><strong><?php _e('Link to the file:', 'crusheddirectory'); ?></strong></label>
	</th>
	<td><?php _e('The url to the zip that contains this plugin.', 'crusheddirectory'); ?>
	<br />
	<input id="plugin-fileurl" name="plugin-fileurl"
		value="<?php esc_attr_e($file->url);?>" type="text" size="30"
		class="regular-text" /></td>
</tr>
			<?php
		}
		function downloadfiles(){
			if(isset($_GET['downloadfile'])){
				$file=$_GET['downloadfile'];
				switch_to_blog(1);
				$fileOptions=get_option($file);
				if(!$fileOptions)
					wp_die('The requested file does not exist');
				global $current_user;
				$groups=$fileOptions['groups'];
				$canAccess=false;
				
				foreach($groups as $group_id)
					if(Memberships::is_member_of_group($current_user->ID,$group_id))
						$canAccess=true;
				if($canAccess){
					$filename=basename($fileOptions['url']);
					$filepath=WP_CONTENT_DIR."/uploads/plugins/$filename";				
					header('HTTP/1.1 200 OK');
					header("Content-disposition: attachment; filename=$filename");
					header('Content-Type: application/zip');
					header('Content-Length:'. filesize($filepath));
					ob_clean();
					flush();
					readfile($filepath);
					exit;
				}else{
					header('HTTP/1.1 403 Forbidden');
					wp_die('You are not allowed to download this file.');
				}
				restore_current_blog();
			}
		}
		function update_file(){
			if(isset($_GET['update_file'])){
				$id=$_GET['update_file'];
	
				$fileOptions=get_option($id);
				if(!$fileOptions)
					wp_die('The requested file does not exist');
				$user=get_user_by_email(urldecode($_GET['email']));
				if(!isset($user) || empty($user)){
					header('HTTP/1.1 403 Forbidden');
					die('You are not allowed to download this file. User does not have access '.urldecode($_GET['email']));
				}
				
				$siteid=$this->get_siteid();
				if(!isset($siteid) || empty($siteid)){
					header('HTTP/1.1 403 Forbidden');
					$domain=Http::get_request_domain();
					$ip=Http::get_IP();
					die('You are not allowed to download this file from '.$ip.' && '.$domain);
				}				
				$plugin_access=get_usermeta($user->ID,'plugin_access');
				$user_sites=$plugin_access[$id]['sites'];
				
				if(!Plugins::has_access($id,$user->ID)){
					header('HTTP/1.1 403 Forbidden');
					die('You do not have access to this file.');
				}
				if(!array_key_exists($siteid,$user_sites)){
					header('HTTP/1.1 403 Forbidden');
					$domain=Http::get_request_domain();
					$ip=Http::get_IP();
					$extra = print_r($_SERVER,true);
					$extra2=print_r($_REQUEST,true);
					die('The site are not allowed to download this file. From '.$ip.' and '.$domain.' extra '.$extra.' \n\r extra2 '.$extra2);
				}				
				$canAccess=false;
				$groups=$fileOptions['groups'];
				foreach($groups as $group_id)
					if(Memberships::is_member_of_group($user->ID,$group_id))
						$canAccess=true;
				
				if($canAccess){
					$filename=basename($fileOptions['url']);
					$filepath=WP_CONTENT_DIR."/uploads/plugins/$filename";				
					header('HTTP/1.1 200 OK');
					header("Content-disposition: attachment; filename=$filename");
					header('Content-Type: application/zip');
					header('Content-Length:'. filesize($filepath));
					ob_clean();
					flush();
					readfile($filepath);
					exit;
				}else{
					header('HTTP/1.1 403 Forbidden');
					die('You are not allowed to download this file.');
				}
			}
		}
		function free_update_file(){
			if(isset($_GET['free_update_file'])){
				$id=$_GET['free_update_file'];
	
				$fileOptions=get_option($id);
				if(!$fileOptions)
					wp_die('The requested file does not exist');
			
				$filename=basename($fileOptions['url']);
				$filepath=WP_CONTENT_DIR."/uploads/free/$filename";				
				header('HTTP/1.1 200 OK');
				header("Content-disposition: attachment; filename=$filename");
				header('Content-Type: application/zip');
				header('Content-Length:'. filesize($filepath));
				ob_clean();
				flush();
				readfile($filepath);
				exit;
			}			
		}
		function free_update(){
			if(isset($_GET['free_update']) && $_GET['free_update']=='plugin'){			
				$disable_update = false;
				if (!isset($_REQUEST['id']) || $disable_update)
					return;				
				$id=$_REQUEST['id'];
				$fileOptions=get_option($id);
				$filename=basename($fileOptions['url']);

				$version = array(
						'has_access' => true,
						'version' => $fileOptions['version'],
						'url' => "http://artofwp.com?free_update_file=$id",
						'site' =>'http://artofwp.com'
				);
				die(serialize($version));							
			}
		}
		function update(){
			if(isset($_GET['update']) && $_GET['update']=='plugin'){
				// Set this to TRUE while editing and testing this file
				$disable_update = false;
								
				if (!isset($_REQUEST['id']) || $disable_update)
					return;

				$id=$_REQUEST['id'];
				$fileOptions=get_option($id);
				$siteid=$this->get_siteid();
				$user=get_user_by_email($_REQUEST['email']);
				$plugin_access=get_usermeta($user->ID,'plugin_access');	
				$user_sites=$plugin_access[$id]['sites'];
				$has_access=true;
				if(!Plugins::has_access($id,$user->ID) && !array_key_exists($siteid,$user_sites))
					$has_access=false;
				$filename=basename($fileOptions['url']);

				$version = array(
						'has_access' => $has_access,
						'version' => $fileOptions['version'],
						'url' => "http://artofwp.com?update_file=$id&email={email}&key={key}",
						'site' =>'http://artofwp.com'
				);
				die(serialize($version));
			}
		}
		function register(){
			$id=trim($_GET['id']);
			$regkey=trim($_GET['key']);
			$user_email=trim($_GET['email']);
			$user=get_user_by_email($user_email);
			if(!$user)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'No user with this email '.$user_email)).')');
			$plugin_access=get_usermeta($user->ID,'plugin_access');
			if(!array_key_exists($id,$plugin_access))
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'This user account don\'t have access.'.print_r($plugin_access,true))).')');
			if($regkey!=$plugin_access[$id]['key'])
				die($_GET['callback'].'('.json_encode(array('status'=>'error','type'=>0, 'message'=>'The supplied registration key does not match this user account.')).')');					
					
			$domain=Http::get_request_domain();
			$siteid=$this->get_siteid();
			if(!$siteid)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'You have to supply your email and registration key.')).')');
			$group_access=Plugins::get_group_access($id);
			$memberships=Memberships::get_memberships_for_user($user->ID);
			$canHaveXNbrOfSites=false;
			foreach($group_access as $group_id => $extra)
				if(array_key_exists($group_id,$memberships))
					$canHaveXNbrOfSites=$extra['sites'];
			if(!$canHaveXNbrOfSites)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'You don\'t have the right to register this plugin with this site.')).')');
			
			$user_sites=$plugin_access[$id]['sites'];
			if(!is_array($user_sites) && empty($user_sites))
				$user_sites=array();
			if(sizeof($user_sites)>=$canHaveXNbrOfSites)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'You cannot register more sites.')).')');
			if(array_key_exists($siteid,$user_sites))
				die($_GET['callback'].'('.json_encode(array('status'=>'error','type'=>1,'message'=>'This site is already registered. If your uninstallation has failed login to your account at artofwp.com and unregister this site.')).')');

			$user_sites[$siteid]=$domain;
			$plugin_access[$id]['sites']=$user_sites;
			update_usermeta($user->ID,'plugin_access',$plugin_access);
			die($_GET['callback'].'('.json_encode(array('status'=>'ok','sites'=>$canHaveXNbrOfSites,'left'=>$canHaveXNbrOfSites-sizeof($user_sites))).')');
		}
		private function get_siteid(){
			$regkey=trim($_REQUEST['key']);
			$user_email=trim(urldecode($_REQUEST['email']));
			if(empty($regkey) || empty($user_email))
				return false;
			$domain=Http::get_request_domain();
			$siteid=$regkey.Http::get_IP().$domain;
			$siteid=hash('md5',$siteid);
			return $siteid;
		}
		function unregister(){
			$id=trim($_GET['id']);
			$regkey=trim($_GET['key']);
			$user_email=trim($_GET['email']);
			$user=get_user_by_email($user_email);
			$siteid=$this->get_siteid();
			$plugin_access=get_usermeta($user->ID,'plugin_access');			
			$user_sites=$plugin_access[$id]['sites'];
			unset($user_sites[$siteid]);
			$plugin_access[$id]['sites']=$user_sites;
			update_usermeta($user->ID,'plugin_access',$plugin_access);
			die($_GET['callback'].'('.json_encode(array('status'=>'ok','domain'=>Http::get_request_domain(),'ip'=>Http::get_IP(),'user_id'=>$user->ID,'email'=>$user_email,'key'=>$regkey)).')');
		}
		function your_plugins_shortcode( $attr ) {
			global $current_user;
			/* Set up our default attributes. */
			//$defaults = array('id' => '');
			/* Merge the input attributes and the defaults. */
			//extract( shortcode_atts( $defaults, $attr ) );
			$plugins=Plugins::plugins_for_user($current_user->ID);
			include("app/views/shortcodes/your-plugins.php");
		}
}
CrushedDirectory::instantiate();