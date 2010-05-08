<?php
class MailController extends BaseController{
	function index(){
		$this->bag['mailmessages']=ResponseMails::get_mails();
	}
	function createnew(){
		$this->bag['title']='Mail Message - Add new';
		$this->bag['current_page']=esc_url(admin_url('admin.php?page=Mail&action=createnew&new=mailmessage'));
		if(isset($_GET['new']) && $_GET['new']=="mailmessage"){
			check_admin_referer('new-mailmessage');
			global $currentuser;
			$mail['post_author']=$currentuser->ID;
			$mail['post_excerpt']=strip_tags(substr($_POST['mail-plain'],0,strlen($_POST['mail-plain'])>255?255:strlen($_POST['mail_plain'])));
			$mail['post_type']='mailmessage';
			$mail['post_content']=$_POST['mail-html'];
			$mail['post_content_filtered']=strip_tags($_POST['mail-plain']);
			$mail['post_name']=$_POST['mail-slug'];
			$mail['post_title']=$_POST['mail-subject'];
			$mail['post_date_gmt']=current_time('mysql');
			$mail['post_modified_gmt']=current_time('mysql');			
			$mail['post_status']='private';
			$mail['event']=$_POST['mail-event'];
			$mail=ResponseMails::save_mail($mail);
			//do_action('add_mail_meta',$result);
			$this->bag['message']="New mail message was added";
			$this->bag['mail']=$mail;
		}
	}
	function edit(){
		$this->bag['title']='Mail Message - Edit';
		$this->bag['current_page']=esc_url(admin_url('admin.php?page=Mail&action=edit&edit=mailmessage&mailmessage='.$_GET['mailmessage']));
		if(isset($_GET['edit']) && $_GET['edit']=="mailmessage"){
			check_admin_referer('edit-mailmessage');
			global $currentuser;
			$mail['ID']=$_POST['mail-id'];
			$mail['post_author']=$currentuser->ID;
			$mail['post_excerpt']=strip_tags(substr($_POST['mail-plain'],0,strlen($_POST['mail-plain'])>255?255:strlen($_POST['mail_plain'])));
			$mail['post_type']='mailmessage';
			$mail['post_content']=$_POST['mail-html'];
			$mail['post_content_filtered']=strip_tags($_POST['mail-plain']);
			$mail['post_name']=$_POST['mail-slug'];
			$mail['post_title']=$_POST['mail-subject'];
			$mail['post_modified_gmt']=current_time('mysql');			
			$mail['post_status']='private';
			$mail['event']=$_POST['mail-event'];
			ResponseMails::save_mail($mail);
			//do_action('add_mail_meta',$result);
		}
		$this->bag['mail']=ResponseMails::get_mail($_GET['mailmessage']);
	}
}