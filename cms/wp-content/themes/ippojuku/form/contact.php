<?php

if( !defined( 'ABSPATH' ) )
	die( '-1' );


define( 'CONTACT_FORM_TO', 'contact@ippo-juku.com' );

mb_language( 'ja' );
session_start();

if( wp_verify_nonce( $_POST[ 'cf-nonce' ], 'contactform' ) ){
	
	function verify_form(){
		extract( array(
			'name' => trim( $_POST[ 'cf-name' ] ),
			'email' => is_email( sanitize_email( trim( $_POST[ 'cf-email' ] ) ) ),
			'message' => trim( $_POST[ 'cf-message' ] ), 
			'captcha' => strtolower( trim( $_POST[ 'cf-captcha' ] ) ),
		) );
		
		$validation = array();
		$send = false;
		$error = 0;
		
		if( empty( $name ) || strlen( $name ) < 3 ){
			$error++;
			$validation['cf-name'] = '名前を入力してください。';
		}else{
			$validation['cf-name'] = true;
		}
		
		if( !$email ){
			$error++;
			$validation['cf-email'] = '有効なメールアドレスを入力してください。';
		}else{
			$validation['cf-email'] = true;
		}
		
		if( empty( $message ) ){
			$error++;
			$validation['cf-message'] = 'メッセージを入力してください。';
		}else{
			$validation['cf-message'] = true;
		}
		
		if( empty( $captcha ) || $captcha != $_SESSION[ 'captcha' ] ){
			$error++;
			$validation['cf-captcha'] = '正しい文字を入力してください。';
		}else{
			$validation['cf-captcha'] = true;
		}
		
		/*	Mail Send
		-----------------------------------------------*/
		if( $error == 0 ){
			$message = strip_tags( $message );
			$message = wordwrap( $message, 70 );
			
			$date = date( 'Y年n月j日 l H:i:s' );
			
			$from_name = mb_encode_mimeheader( '一歩塾お問い合わせ' );
			
			$header = <<<HEADER
From: $from_name <contact@ippo-juku.com>
Reply-To: $email
HEADER;

			$body = <<<MSG
差出人: $name <$email>
送信日: $date
====================

$message

--
このメールは一歩塾のお問い合わせフォームから送信されました
MSG;
			
			$header_reply = <<<HEADER
From: $from_name <contact@ippo-juku.com>
HEADER;

			$body_reply = <<<MSG
お問い合わせありがとうございます。

＊ このメールは一歩塾のお問い合わせフォームから自動で送信されました。

----------------------------------------
お名前: $name
----------------------------------------
メールアドレス: $email
----------------------------------------
お問合わせ内容:
$message
----------------------------------------

MSG;
			
			$send = true;
			
			$send = $send && @mb_send_mail( CONTACT_FORM_TO, $name . ' 様からのお問い合わせ', $body, $header );
			$send = $send && @mb_send_mail( $email, 'お問い合わせありがとうございます', $body_reply, $header_reply );
		}
		
		if( isset( $_POST[ 'ajax_call' ] ) ){
			header( 'Content-type: application/json' );
			echo json_encode( array( 
				'mailsend' => $send,
				'validation' => $validation,
				'error' => $error
			) );
			exit;
		}
	}
	
	verify_form();
	
}

?>