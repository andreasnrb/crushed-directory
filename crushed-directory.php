<?php
/*
 Plugin Name: CrushedDirectory
 Plugin URI: http://artofwp.com/registryplugin
 Description: xxxx
 Version: 10.9.1
 Author: Cyonite Systems
 Author URI: http://cyonitesystems.com/
 */

if(!class_exists("AoiSora")){
	if(is_admin()){
	add_action('after_plugin_row_'.plugin_basename(__FILE__),'after_cr_plugin_row', 10, 2 );					
	function after_cr_plugin_row($plugin_file, $plugin_data){
		echo '<tr class="error" style=""><td colspan="3" class="" style=""><div class="" style="padding:3px 3px 3px 3px;font-weight:bold;font-size:8pt;border:solid 1px #CC0000;background-color:#FFEBE8">Crushed Directory requires <a style="color:blue;text-decoration:underline;" href="http://artofwp.com/aoisora">PHP MVC For WordPress (AoiSora)</a></div></td></tr>';
		//deactivate_plugins(plugin_basename(__FILE__));
	}
	}
	return;
}
loadApp(plugin_dir_path(__FILE__));
class CrushedDirectory extends WpApplicationBase{
	function CrushedDirectory(){
		parent::WpApplicationBase('CrushedDirectory',__FILE__,false,false);
		add_shortcode('yourplugins', array(&$this,'your_plugins_shortcode'));
		add_shortcode('plugin', array(&$this,'plugin_file_shortcode'));	
		add_shortcode('your_profile', array(&$this,'your_profile_shortcode'));
		wp_register_style('crushed-directory',plugins_url('crushed-directory/css/style.css'));
		wp_enqueue_style('crushed-directory');
		add_action('init',array(&$this,'on_init'));
	}
	
	function on_admin_menu(){
		add_menu_page( 'Overview', 'CrushedDir', 'activate_plugins', 'crusheddirectory', array($this,'view_overview'));
		add_submenu_page( 'crusheddirectory', 'Overview', 'Overview','activate_plugins', 'crusheddirectory', array($this,'view_overview'));		
	}
	function view_overview(){
		include('app/views/CDirectoryAdmin/overview.php');
	}
	function on_init(){
		if(current_user_can('activate_plugins')){
			add_action('admin_head', array(&$this,'remove_menu'),20000000000);		
			add_action('admin_head', array(&$this,'remove_submenu'),10000000000);
		}		
//		if(is_user_logged_in())
		if(defined('DOING_AJAX') && DOING_AJAX){
			add_action('wp_ajax_nopriv_awpregister',array(&$this,'register'));
			add_action('wp_ajax_nopriv_awpunregister',array(&$this,'unregister'));
			if(is_user_logged_in()){
				add_action('wp_ajax_awpregister',array(&$this,'register'));
				add_action('wp_ajax_awpunregister',array(&$this,'unregister'));
			}
			return;
		}
		add_action( 'template_redirect', array(&$this,'update'));
		add_action( 'template_redirect', array(&$this,'update_file'));
		add_action( 'template_redirect', array(&$this,'free_update'));
		add_action( 'template_redirect', array(&$this,'free_update_file'));
		add_action( 'template_redirect', array(&$this,'downloadfiles'));
		add_action('paypal',array($this,'paypal_log_transaction'),1,2);
		add_action('paypal-completed',array(&$this,'paypal_payment_recieved'),1000,2);
		add_action('paypal-pending',array(&$this,'paypal_payment_pending'),10,2);
		add_filter('paypal_checkout',array(&$this,'user_has_purchased'));
		add_filter('paypal_cancelled',array(&$this,'user_has_abandon_purchase'));
		$args = array (
			'labels' => array(
				'name' => __( 'Products' ),
				'singular_name' => __( 'Product' ),
				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add New Product' ),
				'edit' => __( 'Edit' ),
				'edit_item' => __( 'Edit Product' ),
				'new_item' => __( 'New Product' ),
				'view' => __( 'View Product' ),
				'view_item' => __( 'View Product' ),
				'search_items' => __( 'Search Products' ),
				'not_found' => __( 'No products found' ),
				'not_found_in_trash' => __( 'No products found in Trash' ),
				'parent' => __( 'Parent Product' ),
			),
			'public'=>true,
			'show_ui' => true,			
		    'hierarchical' => false,
			'publicly_queryable'=>true,
			'exclude_from_search' => true,
			'supports'=>array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'=> array('slug' => 'products'),
			'register_meta_box_cb' => array(&$this,'setup_product_meta_boxes')		
		);
		register_post_type( 'product' , $args );
		$args = array (
			'labels' => array(
				'name' => __( 'Messages' ),
				'singular_name' => __( 'Message' ),
				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add New Message' ),
				'edit' => __( 'Edit' ),
				'edit_item' => __( 'Edit Message' ),
				'new_item' => __( 'New Message' ),
				'view' => __( 'View Message' ),
				'view_item' => __( 'View Message' ),
				'search_items' => __( 'Search Messages' ),
				'not_found' => __( 'No messages found' ),
				'not_found_in_trash' => __( 'No messages found in Trash' ),
				'parent' => __( 'Parent Message' ),
				),		
			'public'=>false,
			'show_ui' => true,
		    'capability_type' => 'post',
		    'hierarchical' => false,
			'publicly_queryable'=>false,
			'exclude_from_search' => true,
			'supports'=>array( 'title', 'editor'),
			'rewrite'=> array('slug' => 'messages'),
			'register_meta_box_cb' => array(&$this,'setup_message_meta_boxes')				
		);
		register_post_type( 'message' , $args );
		
		$args = array (
			'labels' => array(
				'name' => __( 'Transactions' ),
				'singular_name' => __( 'Transaction' ),
/*				'add_new' => __( 'Add New' ),
				'add_new_item' => __( 'Add New Transaction' ),
				'edit' => __( 'Edit' ),
				'edit_item' => __( 'Edit Transaction' ),
				'new_item' => __( 'New Transaction' ),*/
				'view' => __( 'View Transaction' ),
				'view_item' => __( 'View Transaction' ),
				'search_items' => __( 'Search Transaction' ),
				'not_found' => __( 'No transactions found' ),
				'not_found_in_trash' => __( 'No transactions found in Trash' ),
				'parent' => __( 'Parent Transaction' ),
				),
			'public'=>false,
			'show_ui' => true,
		    'capability_type' => 'post',
		    'hierarchical' => false,
			'publicly_queryable'=>false,
			'exclude_from_search' => true,
		'supports'=>array( 'title', 'editor','custom-fields','author'),
		'rewrite'=> array('slug' => 'transactions')	
		);
		
		register_post_type( 'transaction' , $args );
		$payment_status=array('Canceled_Reversal','Completed','Created','Denied','Expired','Failed','Pending','Refunded','Reversed','Processed','Voided');
		foreach($payment_status as $status)
			register_post_status( strtolower($status), array(
				'label'       => _x( $status, 'transaction' ),
				'public'      => true,
				'label_count' => _n_noop( $status.'<span class="count">(%s)</span>', $status.' <span class="count">(%s)</span>')
				));

		register_taxonomy(
			'products',
			array( 'product' ),
			array(
				'public' => true,
				'labels' => array( 'name' => 'Tags', 'singular_name' => 'Tag' )
			)
		);
		add_action('manage_posts_custom_column', array($this,'manage_posts_custom_column'),10,2);		
	}
	
	function on_init_admin(){
		if(function_exists('is_multisite') && is_multisite())
			add_action('wpmu_delete_user',array($this, 'remove_plugin_access'));
		else
			add_action('delete_user',array($this, 'remove_plugin_access'));
		add_action('new_button_form',array($this,'paypal'));
		add_action('edit_button_form',array($this,'paypal_edit'));
		add_filter('get_button_from_post',array($this,'paypal_pre_add'),10,1);
		add_filter('pre_save_button',array($this,'paypal_pre_add'),10,1);
		add_action( "admin_print_scripts", array(&$this,'on_admin_print_scripts' ));
		add_action( "admin_print_styles", array(&$this,'on_admin_print_styles' ));		
		add_action('save_post', array($this,'save_file_meta'));
		add_action('save_post', array($this,'save_event_meta'));
		add_action('save_post', array($this,'save_group_access'));
		add_filter('cd_events',array($this,'add_events'));
		add_filter('manage_transaction_posts_columns',array($this,'edit_transaction_columns'));
		add_filter('post_row_actions',array($this,'transaction_row_actions'),10,2);
		add_filter('the_excerpt',array($this,'transaction_excerpt'),10,1);
	}
	function edit_transaction_columns($columns){
		return array('cb'=>'','title'=>'Item','status'=>'Status','transaction_id'=>'Transaction Id','author'=>'Customer','payment_date'=>'Date');
	}
	function transaction_excerpt($the_excerpt){
		global $post;
		if($post->post_type!='transaction')
			return $the_excerpt;
		$data=unserialize($post->post_content);
		$the_excerpt="<table>";
		foreach($data as $key => $value)
			$the_excerpt.="<tr><td>$key</td><td>$value</td></tr>";
		$the_excerpt.="</table>";
		return $the_excerpt;
	}
	function transaction_row_actions($actions,$post){
		if(get_post_type($post)!='transaction')
			return $actions;
		unset($actions['edit']);
		unset($actions['inline hide-if-no-js']);
		$actions['view']='<a href="'.admin_url("post.php?post=$post_ID&action=edit")."\">View</a>";
		return $actions;
	}
	function manage_posts_custom_column($column_name, $post_ID){
		global $post;
		if($post->post_type!='transaction')
			return;
		switch($column_name){
/*			case 'item':
				echo '<a href="'.admin_url("post.php?post=$post_ID&action=edit")."\">$post->post_title</a>";
				break;*/
			case 'transaction_id':
				echo $post->post_name;
				break;
			case 'status':
				echo $post->post_status;
				break;				
			case 'payment_date':
				echo $post->post_date;
				break;								
		}
	}
	function remove_submenu() {
		global $submenu;
	    $submenu['crusheddirectory'] += Array
	        (5  => Array('Products','edit_posts','edit.php?post_type=product'),
//	         10 => Array('Add New Product','edit_posts','post-new.php?post_type=product'),
	         10 => Array('Tags','manage_categories','edit-tags.php?taxonomy=products&post_type=product'));
	    $submenu['crusheddirectory'] += Array
	        (20  => Array('Messages','edit_posts','edit.php?post_type=message'));
//	         25 => Array(0=>'Add New Message',1=>'edit_posts',2=>'post-new.php?post_type=message'));
	    $submenu['crusheddirectory'] += Array
	        (30 => Array('Transactions','edit_posts','edit.php?post_type=transaction'));/**/
	}
	
	function remove_menu() {
		global $menu;
		//remove post top level menu
		foreach($menu as $key =>$menu_item )
			if(in_array($menu_item[0],array('Transactions','Messages','Products')))
				unset($menu[$key]);
	}

	
	function setup_product_meta_boxes(){
		add_meta_box('groupaccess_meta_box', 'Group access', array(&$this,'groupaccess_meta_box'), 'product', 'normal', 'high');
		add_meta_box('file_meta_box', 'Filename', array(&$this,'file_meta_box'), 'product', 'normal', 'high');		
	}
	function setup_message_meta_boxes(){
		add_meta_box('event_meta_box', 'Event', array(&$this,'event_meta_box'), 'message', 'normal', 'high');		
	}
	function add_events($events){
		$events['new-user']=__('New User');
		$events['receipt']=__('Visitor purchased an item');
		$events['purchase-finished-completed']=__('Visitor purchase completed and on finished page');
		$events['purchase-finished-pending']=__('Visitor purchase pending and on on finished page');
		$events['purchase-cancelled']=__('Visitor abandon purchase');	
		return $events;
	}
	function groupaccess_meta_box($product){
		$this->edit_group_access_restrictions($product);
	}
	function instantiate(){
		new CrushedDirectory();
	}
	function save_file_meta($product_id){
		if(isset($_POST['filename']))
			update_post_meta($product_id,'filename',$_POST['filename']);
		if(isset($_POST['version']))
			update_post_meta($product_id,'version',$_POST['version']);				
	}
	function save_event_meta($message_id){
		if(isset($_POST['event']))
			update_post_meta($message_id,'event',$_POST['event']);
	}
	function save_group_access($id){
		if(!isset($_POST['access_levels']))
			return;
		$access_levels=$_POST['access_levels'];
		if(sizeof($access_levels)>0)
			update_post_meta($id,'group_access',$_POST['access_levels']);
		else
			update_post_meta($id,'group_access',array());
	}
	function on_admin_print_styles(){
		if((isset($_GET['post_type']) && $_GET['post_type']=='transaction') || get_post_type()=='transaction'){?>
			<style>
			#side-info-column,a.add-new-h2{display:none}
			</style>
		<?php 
		}
	}
	function on_admin_print_scripts(){
		if(get_post_type()=='product'){?>
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
		<?php 	
		}
	}
	
	function edit_group_access_restrictions($plugin){
		if(!class_exists('Groups'))
			return;
		$group_access=get_post_meta($plugin->ID,'group_access',true);
		$this->group_access_restrictions($group_access);
	}
	function file_meta_box($product){
		$filename=get_post_meta($product->ID,'filename',true);
		$version=get_post_meta($product->ID,'version',true);
	?>
<div>
<table style="width:600px">
<tr>
	<td><label>Filename:</label></td>
	<td colspan="4">
	<input id="filename" name="filename" value="<?php echo $filename?>"/>
	</td>
</tr>
<tr>
	<td><label>Version:</label></td>
	<td colspan="4">
	<input id="version" name="version" value="<?php echo $version?>"/>
	</td>
</tr>
</table>
</div>		
<?php 
	}
	function event_meta_box($message){
		$event=get_post_meta($message->ID,'event',true);
		$events=apply_filters('cd_events',array());
	?>
<div>
<table style="width:600px">
<tr>
	<td><label>Event:</label></td>
	<td colspan="4">
	<?php echo $event ?>
	<?php HtmlHelper::selectSimple('event',$events,$event)?>
	</td>
</tr>
</table>
</div>		
<?php 
	}			
		
	function group_access_restrictions($group_access=false){
		$groups=Groups::get_groups();
		?>
<div>
<table style="width:600px">
<tr>
	<td><label>Access levels:</label></td>
	<td colspan="4">
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
	<td><label
		style="width: 50px; display: block; float: left; font-weight: bold;">Group:</label>
		</td>
	<td>
	<select id="group_access" name="group_access"
		style="float: left; margin-right: 20px">
		<?php foreach($groups as $group => $extra):?>
		<option value="<?php echo esc_attr($group)?>"><?php echo esc_html(trim($extra['name']))?></option>
		<?php endforeach;?>
	</select> 
	</td>
	<td>
	<label style="width: 130px; display: block; float: left; font-weight: bold;">Number
	of sites:</label> 
	</td>
	<td>
	<input id="group_register_sites" value="" type="text"
		size="5" style="width: 30px" /> (*=unlimited)
	</td><td> <input type="button"
		name="add_access" class="button-secondary"
		value="<?php _e('Add access', 'crusheddirectory') ?>"
		onclick="add_access_level()" /></td>
</tr>
</table>
</div>
		<?php
	}
	function user_has_abandon_purchase($item_name){
		$message=array_pop(ResponseMails::get_mails_for_event('purchase-cancelled'));
		return nl2br(str_replace('{item}',$item_name,$message->post_content));
	}	
	function user_has_purchased($item_name){
		$message=array_pop(ResponseMails::get_mails_for_event('purchase-finished-'.strtolower($_REQUEST['payment_status'])));
		$trans=unserialize(Transactions::get($_REQUEST['txn_id'])->post_content);
		$userdata=get_transient('new-user-'.$_REQUEST['txn_id']);
		if($userdata)
			$data=array('{txn_id}'=>$_REQUEST['txn_id'], '{username}'=>$userdata['user_login'],'{password}'=>$userdata['user_pass']);
		$data['{item}']=$item_name;
		$text=$message->post_content;
		foreach($data as $key => $value)
			$text=str_replace($key,$value,$text);
		return $text;
	}
	function paypal_log_transaction($txn,$post){
		$encoded_id=$post['item_number'];
		$button=PayPalButtons::get_encoded_button($encoded_id,false);		
		$email=$post['payer_email'];
		$user=get_user_by_email($email);
		$user_id=0;
		if($user)
			$user_id=$user->ID;
		
		Transactions::save($user_id,$button->name,$post);
	}
	function paypal_payment_pending($txn,$post){	
		$encoded_id=$post['item_number'];
		$button=PayPalButtons::get_encoded_button($encoded_id,false);		
		wp_mail('andreas@cyonitesystems.com', 
					"PayPal Pending Payment Recieved ".urldecode($post['payer_email']),
"Item: $button->name
Name: ".$post['first_name'].$post['last_name']."
Transaction".$post['txn_id']."
Pending reason:".$post['pending_reason'],
					'From: Art Of WP Shop <shop@artofwp.com>' . "\r\n\\");		
	}
	function paypal_payment_recieved($txn,$post){
		global $membersext;
		$encoded_id=$post['item_number'];
		$button=PayPalButtons::get_encoded_button($encoded_id,false);		
		if(paypal_debugging())
		wp_mail('andreas@cyonitesystems.com',"CR PayPal Payment Recieved",
					'TXN TYPE:'.$txn. "\n\nButton:".print_r($button,true)."\n\nPost:" . print_r($post,true),
					'From: Andreas Nurbo <andreas@cyonitesystems.com>' . "\r\n\\");
		if(!isset($button->plugin_access))
			return;
		wp_mail('andreas@cyonitesystems.com', 
					"PayPal Payment Recieved ".urldecode($post['payer_email']),
"Item: $button->name
Name: ".$post['first_name'].$post['last_name']."
Transaction".$post['txn_id'],'From: Art Of WP Shop <shop@artofwp.com>' . "\r\n\\");		
		
		$email=urldecode($post['payer_email']);
		$user=get_user_by_email($email);
		
		$plugin_access=(array)get_usermeta($user->ID,'plugin_access');
		if(array_search($button->plugin_access,$plugin_access)===false)
			$plugin_access['key']=substr(hash('md5',$user->ID.time()),0,12);
		$plugin_access[$button->plugin_access]['sites']=array();
		ksort($plugin_access);
		update_usermeta($user->ID,'plugin_access',$plugin_access);
		if(!Plugins::has_access($button->plugin_access,$user->ID)){
			Plugins::save_plugin_user($button->plugin_access,$user->ID);
		}
		$plugin=Plugins::get_plugin($button->plugin_access);
		Transactions::save($user->ID,$button->name,$post);
		if($membersext->new_user){
			$user->password=$membersext->new_user['user_pass'];
			$userdata['username']=$user->user_login;
			$userdata['password']=$user->password;
			$userdata['first_name']=get_usermeta($user->ID,'first_name');
			$userdata['last_name']=get_usermeta($user->ID,'last_name');
			$userdata['email']=$email;
			$userdata['item']=$plugin->post_title;
			$userdata['to']=$email;
			ResponseMails::send_mails($userdata,'new-user');
		}
		$receipt['transaction']=$post['txn_id'];
		$receipt['first_name']=get_usermeta($user->ID,'first_name');
		$receipt['last_name']=get_usermeta($user->ID,'last_name');
		$receipt['email']=$post['payer_email'];
		$receipt['to']=$email;
		$receipt['item']=$plugin->post_title;
		$receipt['cost']=$post['mc_gross'];
		$receipt['date']=$post['payment_date'];
		ResponseMails::send_mails($receipt,'receipt');
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
		$plugins=query_posts('post_type=product');
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
		
		function licensetext(){
			if(isset($_GET['licensetext']) && $_GET['licensetext']=='plugin'){
				$filename='plugins.html';//.$fileOptions['version'];
				$filepath=WP_CONTENT_DIR."/uploads/licenses/$filename";				
				header('HTTP/1.1 200 OK');
				header('Content-Type: text/html');
				header('Content-Length:'. filesize($filepath));
				ob_clean();
				flush();
				readfile($filepath);
				exit;
			}
		}
		
		function downloadfiles(){
			if(isset($_GET['downloadfile'])){
				$id=$_GET['downloadfile'];
				global $wpdb;
				$product_ID=$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM $wpdb->posts  WHERE `post_type`='product' AND `post_name`=%s",$id));
				if(!$product_ID)
					wp_die('The requested file does not exist');

				global $current_user;
				$canAccess=false;
				
				$memberships=Memberships::get_memberships_for_user($current_user->ID);
				$group_access=get_post_meta($product_ID,'group_access',true);
				foreach($group_access as $group_id => $extra)
					if(array_key_exists($group_id,$memberships))
						$canAccess=true;
						
				if($canAccess){
					$filename=get_post_meta($product_ID,'filename',true);
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
			}
		}
		function update_file(){
			if(isset($_GET['update_file'])){
				$id=$_GET['update_file'];
				global $wpdb;
				$product_ID=$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM $wpdb->posts  WHERE `post_type`='product' AND `post_name`=%s",$id));
				if(!$product_ID)
					wp_die('The requested file does not exist');
				$groups=get_post_meta($product_ID,'group_access',true);
				$user=get_user_by_email(urldecode($_REQUEST['email']));
				if(!isset($user) || empty($user)){
					header('HTTP/1.1 403 Forbidden');
					die('You are not allowed to download this file. User does not have access '.$_GET['email']);
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
				
				if(!array_key_exists($siteid,$user_sites)){
					header('HTTP/1.1 403 Forbidden');
					$domain=Http::get_request_domain();
					$ip=Http::get_IP();
					die('The site is not allowed to download this file. From '.$ip.' and '.$domain);
				}
							if(!class_exists('Groups'))
				include(plugin_dir_path(__FILE__).'../MembersExtended/components/groups/models/groups.php');			
			if(!class_exists('Memberships'))
				include(plugin_dir_path(__FILE__).'../MembersExtended/components/groups/models/memberships.php');
				$canAccess=false;
				$memberships=Memberships::get_memberships_for_user($user->ID);
				$group_access=get_post_meta($product_ID,'group_access',true);				
				foreach($group_access as $group_id => $extra)
					if(array_key_exists($group_id,$memberships))
						$canAccess=true;
				
				if($canAccess){
					$filename=get_post_meta($product_ID,'filename',true);
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
				global $wpdb;
				$product_ID=$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM $wpdb->posts  WHERE `post_type`='product' AND `post_name`=%s",$id));
				if(!$product_ID)
					wp_die('The requested file does not exist');
				$filename=get_post_meta($product_ID,'filename',true);
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
				global $wpdb;
				$product_ID=$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM $wpdb->posts  WHERE `post_type`='product' AND `post_name`=%s",$id));
				if(!$product_ID)
					wp_die('The requested file does not exist');
				$version = array(
						'has_access' => true,
						'version' => get_post_meta($product_ID,'version',true),
						'url' => "http://api.artofwp.com?free_update_file=$id",
						'site' =>'http://artofwp.com'
				);
				die(serialize($version));				
			}
		}
		function update(){
			if(isset($_GET['update']) && $_GET['update']=='plugin_information'){
				if (!isset($_REQUEST['id']) || $disable_update)
					return;
				$id=$_REQUEST['id'];
				$filename=$id.'.txt';
				$filepath=WP_CONTENT_DIR."/uploads/versions/$filename";				
				header('HTTP/1.1 200 OK');
				header('Content-Type: text/plain');
				header('Content-Length:'. filesize($filepath));
				ob_clean();
				flush();
				readfile($filepath);
				exit;
			}else if(isset($_GET['update']) && $_GET['update']=='plugin'){
				if (!isset($_REQUEST['id']))
					return;
			if(!class_exists('Groups'))
				include(plugin_dir_path(__FILE__).'../MembersExtended/components/groups/models/groups.php');			
			if(!class_exists('Memberships'))
				include(plugin_dir_path(__FILE__).'../MembersExtended/components/groups/models/memberships.php');
					
				$id=$_REQUEST['id'];
				global $wpdb;
				$product_ID=$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM $wpdb->posts  WHERE `post_type`='product' AND `post_name`=%s",$id));
				if(!$product_ID)
					wp_die('The requested file does not exist');

				$siteid=$this->get_siteid();
				if(!$siteid)
					wp_die('The request is missing registration key and/or email.');
				
				$user=get_user_by_email($_REQUEST['email']);
				$plugin_access=get_usermeta($user->ID,'plugin_access');	
				$user_sites=$plugin_access[$id]['sites'];
				$user_sites=is_array($user_sites)?$user_sites:array();
				$has_access=false;
				$memberships=Memberships::get_memberships_for_user($user->ID);
				$group_access=get_post_meta($product_ID,'group_access',true);				
				foreach($group_access as $group_id => $extra)
					if(array_key_exists($group_id,$memberships))
						$has_access=true;

						
				if(!array_key_exists($siteid,$user_sites)){
					$has_access=false;
				}
				$filename=get_post_meta($product_ID,'filename',true);

				$version = array(
						'has_access' => $has_access,
						'version' => get_post_meta($product_ID,'version',true),
						'url' => "http://api.artofwp.com?update_file=$id&email={email}&key={key}",
						'site' =>'http://artofwp.com'/*,
						'sites' => $plugin_access,
						'siteid'=>$siteid,
						'memberships'=> $memberships,
						'groupaccess'=> $group_access*/
						);
				die(serialize($version));
			}
		}
		function register(){			
			$id=trim($_GET['id']);
			$regkey=trim($_GET['key']);
			$user_email=trim($_GET['email']);
			$user=get_user_by_email($user_email);
			$domain=Http::get_request_domain();
			$siteid=$this->get_siteid();
			if(!$siteid)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'You have to supply your email and registration key.')).')');
			if(!$user)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'No user with this email '.$user_email)).')');
			global $wpdb;
			$product_ID=$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM $wpdb->posts  WHERE `post_type`='product' AND `post_name`=%s",$id));
			if(!$product_ID)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'No plugin or theme with this Id:'.$id)).')');			
			$group_access=get_post_meta($product_ID,'group_access',true);
			if(!class_exists('Groups'))
				include(plugin_dir_path(__FILE__).'../MembersExtended/components/groups/models/groups.php');			
			if(!class_exists('Memberships'))
				include(plugin_dir_path(__FILE__).'../MembersExtended/components/groups/models/memberships.php');
			$memberships=Memberships::get_memberships_for_user($user->ID);
			$canHaveXNbrOfSites=false;
			foreach($group_access as $group_id => $extra)
				if(array_key_exists($group_id,$memberships))
					$canHaveXNbrOfSites=$extra['sites'];
			if(!$canHaveXNbrOfSites)
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'You don\'t have the right to register this plugin with this site.')).')');
				
			$plugin_access=get_usermeta($user->ID,'plugin_access');

/*			if(!array_key_exists($id,$plugin_access))
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'This user account don\'t have access.')).')');
*/
			if($regkey!=$plugin_access['key'])
				die($_GET['callback'].'('.json_encode(array('status'=>'error','type'=>0, 'message'=>'The supplied registration key does not match this user account.')).')');


			
			$user_sites=$plugin_access[$id]['sites'];
			if(!is_array($user_sites) && empty($user_sites))
				$user_sites=array();
			if(sizeof($user_sites)>=$canHaveXNbrOfSites && $canHaveXNbrOfSites!=='*')
				die($_GET['callback'].'('.json_encode(array('status'=>'error','message'=>'You cannot register more sites.','sites'=>$canHaveXNbrOfSites,'left'=>$canHaveXNbrOfSites-sizeof($user_sites))).')');
			if(array_key_exists($siteid,$user_sites))
				die($_GET['callback'].'('.json_encode(array('status'=>'error','type'=>1,'message'=>'This site is already registered. If your uninstallation has failed login to your account at artofwp.com and unregister this site.','sites'=>$canHaveXNbrOfSites,'left'=>$canHaveXNbrOfSites-sizeof($user_sites))).')');

			$user_sites[$siteid]=$domain;
			$plugin_access[$id]['sites']=$user_sites;
			update_usermeta($user->ID,'plugin_access',$plugin_access);
			if($canHaveXNbrOfSites!=='*')
				die($_GET['callback'].'('.json_encode(array('status'=>'ok','sites'=>$canHaveXNbrOfSites,'left'=>$canHaveXNbrOfSites-sizeof($user_sites))).')');
			else
				die($_GET['callback'].'('.json_encode(array('status'=>'ok','sites'=>$canHaveXNbrOfSites,'left'=>$canHaveXNbrOfSites)).')');				
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
			$id=trim($_REQUEST['id']);
			$regkey=trim($_REQUEST['key']);
			$user_email=trim($_REQUEST['email']);
			$user=get_user_by_email($user_email);
			$siteid=$this->get_siteid();
			$plugin_access=get_usermeta($user->ID,'plugin_access');
			$user_sites=$plugin_access[$id]['sites'];
			unset($user_sites[$siteid]);
			$plugin_access[$id]['sites']=$user_sites;
			update_usermeta($user->ID,'plugin_access',$plugin_access);
			die($_GET['callback'].'('.json_encode(array('status'=>'ok','domain'=>Http::get_request_domain(),'ip'=>Http::get_IP(),'user_id'=>$user->ID,'email'=>$user_email,'key'=>$regkey)).')');
		}
		function your_profile_shortcode( $attr){		
			include("app/views/shortcodes/your-profile.php");
		}
		function your_plugins_shortcode( $attr ) {
			global $current_user;
			if(isset($_POST['unregister'])){
				$plugin_access=get_usermeta($current_user->ID,'plugin_access');
				$id=$_POST['plugin'];
				$user_sites=$plugin_access[$id]['sites'];
				$sites=$_POST['site'];
				foreach($sites as $site){
					$key=array_search($site,$user_sites);
					if($key)
						unset($user_sites[$key]);
				}
				$plugin_access[$id]['sites']=$user_sites;
				update_usermeta($current_user->ID,'plugin_access',$plugin_access);
			}
			
			
			$plugin_access=get_usermeta($current_user->ID,'plugin_access');			
			$key=$plugin_access['key'];
			$plugins=Plugins::plugins_for_current_user();
			include("app/views/shortcodes/your-plugins.php");
		}
		function plugin_file_shortcode($atts){
			global $current_user;			
			$secure=true;
			extract($atts);
			$plugin=Plugins::get_plugin($id);
			$file=array_pop($plugin->files);
			$plugin_access=get_usermeta($current_user->ID,'plugin_access');	
			$user_sites=$plugin_access[$id]['sites'];

			$has_access=false;
			$fileOptions=get_option($id);			
			$groups=$fileOptions['groups'];
			foreach($groups as $group_id)
				if(Memberships::is_member_of_group($user->ID,$group_id))
					$plugin->has_access=true;						
			include("app/views/shortcodes/plugin.php");			
		}
}
/*# BEGIN PHPMVC
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_METHOD} !GET
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)/(.+)$ /wp-content/plugins/AoiSora/preroute.php?controller=$1&action=$2 [L]
</IfModule>
# END PHPMVC

*/
CrushedDirectory::instantiate();