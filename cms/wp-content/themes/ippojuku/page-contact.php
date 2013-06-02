<?php 
require_once( TEMPLATEPATH . '/form/contact.php' );

get_header();

?>
<div id="container">
	<div id="content">
 		<div class="col-Ab-A">
 			<h3>お問合わせフォーム</h3>
			<form id="contact-form" action="<?php the_permalink(); ?>" method="post" class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="cf-name">お名前 <span class="required">(必須)</span></label>
					<div class="controls">
						<input type="text" name="cf-name" id="cf-name" class="text span-4" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="cf-email">メールアドレス <span class="required">(必須)</span></label>
					<div class="controls">
						<input type="text" name="cf-email" id="cf-email" class="text span-4" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="cf-message">お問合わせ内容 <span class="required">(必須)</span></label>
					<div class="controls">
						<textarea name="cf-message" id="cf-message" class="span-5" rows="15"></textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="cf-captcha">画像認証 <span class="required">(必須)</span></label>
					<div class="controls">
						<img src="/lib/api/captcha/get.php" id="captcha-image" alt="" />
						<input type="text" name="cf-captcha" id="cf-captcha" class="text span-4" />
						<p class="help-block">上の画像に表示されている文字を入力してください。
						<br />画像をクリックすると別の文字を表示します。</p>
					</div>
				</div>
				<input type="hidden" name="cf-nonce" value="<?php echo wp_create_nonce( 'contactform' ); ?>" />
				<div class="controls">
					<button type="submit" class="btn btn-primary">送信</button>
				</div>
			</form>
			<script>
				require( 'form', '!ready' ).done(function(){
					var $form = $( '#contact-form' ),
						$captcha = $( '#captcha-image' ),
						$captcha_input = $( '#cf-captcha' ),
						validation_box = [],
						validation_help = [],
						validation_msg;
					
					function validation( target, msg ){
						var $target = $( '#' + target ),
							$ctrlg = $target.parent().parent();
						
						if( msg === true ){
							validation_box.push( $ctrlg.addClass( 'success' ) );
						}else{
							var help = $( '<span class="help-block">' + msg + '</span>' );
							validation_help.push( help );
							$target.after( help );
							validation_box.push( $ctrlg.addClass( 'error' ) );
						}
					}
					function clearValidation( $form ){
						var i, el;
						
						i = 0;
						while( el = validation_box[ i++ ] ){
							el.removeClass( 'error success' );
						}
						
						i = 0;
						while( el = validation_help[ i++ ] ){
							el.remove();
						}
						
						validation_msg && validation_msg.remove();
						
						validation_box = [];
						validation_help = [];
						validation_msg = null;
						
						refreshCaptcha();
					}
					function refreshCaptcha(){
						$captcha.attr( 'src', '/lib/api/captcha/get.php?' + Math.random() );
						$captcha_input.val( '' );
					}
					
					$form.ajaxForm({
						dataType : 'json',
						data : { 'ajax_call' : 1 },
						beforeSubmit : function( data, $form ){
							return true;
						},
						success : function( data, status, xhr, $form ){
							clearValidation( $form );
							if( data.mailsend == true ){
								validation_msg = $( '<br /><p class="alert alert-success">お問い合わせを送信しました。</p>' ).appendTo( $form );
								$form.clearForm();
								return true;
							}else{
								validation_msg = $( 
									'<br /><p class="alert alert-error">お問い合わせの送信に失敗しました。<br />'
									+ (
										data.error == 0
										? '申し訳ございませんが、しばらくたってからもう一度お試しください。'
										: 'ご入力された内容の' + data.error + '箇所に不備があります。' 
									)
									+ '</p>'
								).appendTo( $form );
								var id, v;
								for( id in data.validation ){
									validation( id, data.validation[ id ] );
								}
								return false;
								
							}
						}
					});
					
					$captcha.click(function(){
						refreshCaptcha();
						$captcha_input.focus();
					});
				});
			</script>
 		</div>
 		<div class="col-Ab-b">
<?php
		crst::the_content( 3 );
?>

 		</div>
	<!--/ #content --></div>
<!--/ #container --></div>
<?php get_footer(); ?>