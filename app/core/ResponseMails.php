<?php
class ResponseMails{
	
	static function send_first_plugin_bought($user_id,$plugin,$access){
		$from=(object)array('name'=>'Shop Art Of WordPress', 'email'=>'andreas@itn.se');
		$user=get_userdata($user_id);
		$to=$user->user_email;
		$subject="$plugin->name receipt";
		$message="Hi\n\r";
		$message.="Thank you for purchasing $plugin->name \n\r";
		$message.="You have to login to your account to download the plugin. \n\r";
		$message.="Instructions on how you install and configure it will be available there.\n\r";
		$result= self::send_mail($from,$to,$subject,$message);
				
		return $result;
	}
	static function send_mail($from,$to,$subject,$message){
		$headers = array("From: $from->name <$from->email>","Content-Type: text/html"
		);
		$sent=wp_mail($to, $subject, $message, $headers);
		return $sent;
	}
	static function send_mails($data,$event){
		$mails=self::get_mails_for_event($event);
		paypal_log_info('mails',print_r($mails,true));
		$from=(object)array('name'=>'Shop Art Of WordPress', 'email'=>'support@artofwp.com');
		foreach($mails as $mail){
			$subject=$mail->post_title;			
			$message_plain=$mail->post_content;
			foreach($data as $key=>$value){
				$key='{'.$key.'}';
				$subject=str_replace($key,$value,$subject);
				$message_plain=str_replace($key,$value,$message_plain);				
			}
			paypal_log_info('mailsubject',$subject);
			paypal_log_info('mailmessage',$message_plain);						
			$result=self::send_mail($from,$data['to'],$subject,$message_plain);
			paypal_log_info('aftersend_mail',$result);
		}
	}
	static function merge_mail($data,$event){
		$mails=self::get_mails_for_event($event);		
		foreach($mails as $mail){
			foreach($data as $key=>$value){
				$key='{'.$key.'}';
				$mail->post_title=str_replace($key,$value,$mail->post_title);
				$mail->post_content=str_replace($key,$value,$mail->post_content);				
			}
		}
		return $mails;		
	}
	static function save_mail($mail){
		$mail['post_type']='mailmessage';
		$mail_id=wp_insert_post($mail);
		if(isset($mail['ID'])){
			update_post_meta($mail['ID'],'from',$mail['from']);
			update_post_meta($mail['ID'],'event',$mail['event']);
		}else{
			add_post_meta($mail_id,'from',$mail['from']);
			add_post_meta($mail_id,'event',$mail['event']);			
		}
	}
	static function get_mails_for_event($event){
		$mails=query_posts('post_type=message&meta_key=event&meta_value='.$event);
		wp_reset_query();
		return $mails;
	}
	static function get_mail($slug){
		$mails=query_posts('post_type=message&postname='.$slug);
		wp_reset_query();
		$mail=$mails[0];
		$temp->ID=$mail->ID;
		$temp->slug=$mail->post_name;
		$temp->message_plain=$mail->post_content_filtered;
		$temp->message_html=$mail->post_content;
		$temp->subject=$mail->post_title;
		$temp->from=get_post_meta($mail->ID,'from');
		$temp->event=array_pop(get_post_meta($mail->ID,'event'));
		return $temp;
	}
	static function get_mails(){
		$mails=query_posts('post_type=message');
		wp_reset_query();
		return $mails;
	}
}