<?php
class Transactions{
	function get($txn_id){
		global $wpdb;
		$transaction=$wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts  WHERE `post_type`='transaction' AND `post_name`=%s",$txn_id));
		return $transaction;
	}
	function exists($txn_id){
		global $wpdb;
		$transaction=$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM $wpdb->posts  WHERE `post_type`='transaction' AND `post_name`=%s",$data['txn_id']));
		return !empty($transaction);
	}
	function save($user_id,$item,$data){
		$pdate=gmdate('Y-m-d H:i:s', strtotime($data['payment_date']));
		$post = array(
		  'post_author' => $user_id, //The user ID number of the author.
		  'post_content' => serialize($data), //The full text of the post.
		  'post_date' =>  $pdate,//The time post was made.
		  'post_name' => $data['txn_id'], // The name (slug) for your post
		  'post_status' => strtolower($data['payment_status']) , //Set the status of the new post. 
		  'post_title' => $item, //The title of your post.
		  'post_type' => 'transaction' //Sometimes you want to post a page.
		);
		$oldtxn=self::get($data['txn_id']);
		if($oldtxn){
			$post_id=$oldtxn->ID;
			$post['ID']=$post_id;
			wp_update_post( $post );
		}
		else
			$post_id=wp_insert_post( $post );
			
		$buyer_information_vars=array(
								'first_name',
								'last_name',
								'address_name',
								'address_street',
								'address_city',
								'address_state',
								'address_zip',
								'address_country',
								'address_country_code',
								'address_status',
								'contact_phone',
								'payer_id',
								'payer_email',
								'payer_status',
								'payer_business_name',
								'residence_country',
								'payer_phone'
								);
		$buyer_information=array_intersect_key($data,$buyer_information_vars);
		update_post_meta($post_id,'buyer_information',$buyer_information);
		update_post_meta($post_id,'payer_email',$data['payer_email']);
		$payment_information_vars=array(
								'payment_status',
								'pending_reason',
								'payment_date',
								'option_name1',
								'option_selection1',
								'option_name2',
								'option_selection2',
								'memo',
								'shipping_method',
								'btn_id',
								'mc_gross',
								'mc_fee', 
								'mc_shipping', 
								'mc_handling', 
								'shipping_discount',
								'insurance_amount',
								'handling_amount', 
								'shipping', 
								'tax',
								'mc_currency',
								'txn_id',
								'txn_type',
								'payment_type',
								'notify_version',
								'verify_sign',
								'transaction_subject',
								'protection_eligibility',
								'ipn_status',
								'subscr_id',
								'custom',
								'reason_code',
								'item_name',
								'item_number',
								'invoice',
								'for_auction',
								'auction_buyer_id',
								'auction_closing_date',
								'auction_multi_item',
								'creation_timestamp',
								'receiver_id',
								'test_ipn',
								'invoice'
								);
		$payment_information=array_intersect_key($data,$payment_information_vars);
		update_post_meta($post_id,'payment_information',$payment_information);
		update_post_meta($post_id,'affiliate',$data['custom']);
		update_post_meta($post_id,'txn_type',$data['txn_type']);
		update_post_meta($post_id,'btn_id',$data['btn_id']);
		update_post_meta($post_id,'payment_status',$data['payment_status']);
		if(isset($data['invoice']))
			update_post_meta($post_id,'invoice',$data['invoice']);
		if(isset($data['tax']))
			update_post_meta($post_id,'tax',$data['tax']);
		if(isset($data['mc_gross']))
			update_post_meta($post_id,'mc_gross',$data['mc_gross']);
		if(isset($data['mc_gross_1']))
			update_post_meta($post_id,'mc_gross',$data['mc_gross_1']);
		if(isset($data['parent_txn_id']))
			update_post_meta($post_id,'parent_txn_id',$data['parent_txn_id']);
		if(isset($data['receipt_id']))
			update_post_meta($post_id,'receipt_id',serialize($data['receipt_id']));
		if(isset($data['reason_code']))
			update_post_meta($post_id,'reason_code',$data['reason_code']);
		if(isset($data['pending_reason']))
			update_post_meta($post_id,'pending_reason',$data['pending_reason']);
		if(isset($data['discount_codes']))
			update_post_meta($post_id,'discount_codes',$data['discount_codes']);
	}
}