<?php

/*=== Admin Init
==============================================================================================*/
add_action( 'admin_init', function(){
	
});


/*=== Hide Update Notification
==============================================================================================*/
if( !is_super_admin() )
	add_filter( 'pre_site_transient_update_core', '__return_zero' );

/*=== JPEG Quality Hack
==============================================================================================*/
add_filter( 'jpeg_quality', 'crst_hook_jpeg_quality' );
function crst_hook_jpeg_quality( $arg ){
	return 100;
}


/*=== Admin Head
==============================================================================================*/
add_action( 'admin_head', 'crst_admin_head', -1, 20 );
function crst_admin_head(){
	echo '<style type="text/css">#contextual-help-link-wrap{ display: none !important; }</style>';
}


/*=== エディタ
==============================================================================================*/
add_action( 'admin_head', 'editor_stylesheet' );
function editor_stylesheet(){
	global $current_screen;
	//$current_screen->post_type
	add_editor_style( 'editor/editor-style-all.acc.php' );
}

add_filter( 'tiny_mce_before_init', 'custom_editor_settings' );
function custom_editor_settings( $initArray ){
	$initArray['body_class'] = 'content';
	$initArray['relative_urls'] = true;
	$initArray['keep_styles'] = true;
	$initArray['extended_valid_elements'] = (
		( $initArray['extended_valid_elements'] ? $initArray['extended_valid_elements'] . ',' : '' )
		. 'iframe[*],br[*]'
	);
	$initArray['theme_advanced_blockformats'] = 'p,h3,h4,h5,h6,address,div';
	$initArray['theme_advanced_buttons2_add_before'] = 'styleselect';
	$initArray['plugins'] = preg_replace( '|[,]+tabfocus|i', '', $initArray['plugins'] );

	$style_formats = array(
		array(
			'title' => 'マーカー',
			'inline' => 'strong',
			'classes' => 'red'
		),
		array(
			'title' => '補足',
			'inline' => 'span',
			'classes' => 'note'
		),
		array(
			'title' => '大きな文字',
			'inline' => 'span',
			'classes' => 'txt-big'
		),
		array(
			'title' => '小さな文字',
			'inline' => 'span',
			'classes' => 'txt-small'
		),
		array(
			'title' => 'ボックス',
			'block' => 'p',
			'classes' => 'alert alert-info'
		),
		array(
			'title' => '詳細リンク',
			'inline' => 'a',
			'classes' => 'btn'
		),
		array(
			'title' => '詳細リンク(大)',
			'inline' => 'a',
			'classes' => 'btn cta'
		),
	);
	$initArray[ 'style_formats' ] = json_encode( $style_formats );
	return $initArray;
}

/*=== メニューバー
==============================================================================================*/
/*	メニューバーの項目を非表示にする
-----------------------------------------------*/
add_action( 'admin_menu', 'remove_admin_menu_links' );
function remove_admin_menu_links(){
	if( is_super_admin() )
		return;
	
	//remove_menu_page('index.php'); // ダッシュボード
	//remove_menu_page('edit.php'); // 記事投稿
	//remove_menu_page('upload.php'); // メディア
	//remove_menu_page('link-manager.php'); // リンク
	//remove_menu_page('edit.php?post_type=page'); // 固定ページ
	remove_menu_page('edit-comments.php'); // コメント
	//remove_menu_page( 'themes.php' ); // 外観
	remove_menu_page( 'plugins.php' ); // プラグイン
	//remove_menu_page( 'users.php' ); // ユーザー
	//remove_menu_page('tools.php'); // ツール
	//remove_menu_page('options-general.php'); // 設定
}


/*=== フッターのテキストを変える
==============================================================================================*/
add_filter( 'admin_footer_text', 'custom_admin_footer' );
function custom_admin_footer(){
	echo '';
}


/*=== ユーザ権限
==============================================================================================*/
/*	投稿権限ユーザにカテゴリ管理権限を与える
-----------------------------------------------*/
get_role( 'author' )->add_cap( 'manage_categories' );
get_role( 'editor' )->add_cap( 'edit_theme_options' );


/*=== Dashbord
==============================================================================================*/
/*	ダッシュボードにカスタム投稿の数を表示する
-----------------------------------------------*/
add_action('right_now_content_table_end', 'custom_post_dashboard');
function custom_post_dashboard(){
	 
	$dashboard_custom_post_types= get_post_types( array(
		'public' => true,
		'_builtin' => false	
	) );
	 
	global $wp_post_types;
	foreach($dashboard_custom_post_types as $custom_post_type) {
		$num_post_type = wp_count_posts($custom_post_type);
		$num = number_format_i18n($num_post_type->publish);
		$text = _n( $wp_post_types[$custom_post_type]->labels->singular_name, $wp_post_types[$custom_post_type]->labels->name, $num_post_type->publish );
		$capability = $wp_post_types[$custom_post_type]->cap->edit_posts;
		 
		if (current_user_can($capability)) {
			$num = "<a href='edit.php?post_type=$custom_post_type'>$num</a>";
			$text = "<a href='edit.php?post_type=$custom_post_type'>$text</a>";
		}
		 
		echo "<tr><td class='first b b_$custom_post_type'>$num</td><td class='t $custom_post_type'>$text</td></tr>";
	}
}

/*	ウィジェットを追加する
-----------------------------------------------*/
//add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
function my_custom_dashboard_widgets() {
	global $wp_meta_boxes;
	
	wp_add_dashboard_widget('custom_help_widget', '今日のお知らせ', 'dashboard_text');
}
function dashboard_text() {
	echo '<p>foo様、こんにちは！今日のお知らせです。<br />今日はお問い合わせが3件ありました。ご返信をお願い致します。</p>';
}


/*=== 投稿一覧にサムネイルの列を表示する
==============================================================================================*/
add_filter( 'manage_posts_columns', 'manage_posts_columns' );
add_action( 'manage_posts_custom_column', 'add_column', 10, 2 );
add_filter( 'manage_pages_columns', 'manage_posts_columns' );
add_action( 'manage_pages_custom_column', 'add_column', 10, 2 );
function manage_posts_columns($columns) {
	$columns['thumbnail'] = __('Thumbnail');
	return $columns;
}
function add_column( $column_name, $post_id ){
	if( 'thumbnail' == $column_name ){
		$thum = get_the_post_thumbnail( $post_id, array( 50, 100 ), 'thumbnail' );
	}
	if( isset( $thum ) && $thum ){
		echo $thum;
	}else{
		echo __( 'None' );
	}
}


/*=== アイキャッチの説明文
==============================================================================================*/
add_filter( 'admin_post_thumbnail_html', 'add_featured_image_instruction');
function add_featured_image_instruction( $content ) {
    return $content . '<p>アイキャッチ画像として画像を追加するとサムネイルが表示されるようになります。</p>';
}


/*=== 管理画面にページを追加する
==============================================================================================*/
add_action( 'admin_menu', 'crst_add_admin_menu' );
function crst_add_admin_menu(){
    add_menu_page( 'ヘルプ', 'ヘルプ', 'read', __FILE__, 'menu_page_help' );
}
function menu_page_help(){
    $siteurl = get_option( 'siteurl' );
	?>
		<div class="wrap">
		<h2>ヘルプです</h2>
		<p>コンテンツ</p>
		</div>
	<?php
}


/*=== 設定項目
==============================================================================================*/
/*	項目を追加する
-----------------------------------------------*/
add_action( 'admin_init', function(){
	add_settings_field( 'new_days', '新着期間設定', 'crst_setting_new_days', 'reading' );
	add_settings_field( 'modified_days', '更新期間設定', 'crst_setting_modified_days', 'reading' );
	
	add_settings_section( 'social', 'ソーシャルサービスアカウント', '__return_false', 'general' );
		add_settings_field( 'facebook', 'Facebook', 'crst_setting_facebook', 'general', 'social' );
		add_settings_field( 'twitter', 'Twitter', 'crst_setting_twitter', 'general', 'social' );
});

/*	設定で保存できる項目を追加する
-----------------------------------------------*/
add_filter( 'whitelist_options', 'crst_whitelist_options' );
function crst_whitelist_options( $whitelist_options ) {
	$whitelist_options['reading'][] = 'new_days';
	$whitelist_options['reading'][] = 'modified_days';
	$whitelist_options['general'][] = 'facebook';
	$whitelist_options['general'][] = 'facebook_id';
	$whitelist_options['general'][] = 'twitter';
	
	return $whitelist_options;
}

/*	SNS: Facebook
-----------------------------------------------*/
function crst_setting_facebook(){
    $facebook = safe_value( get_option( 'facebook', null ), '' );
    $facebook_id = safe_value( get_option( 'facebook_id', null ), '' );
    
	?>
		<style scope="true">
			.crst-size-10ex{
				display: inline-block;
				width: 10ex;
			}
			.crst-txt-right{
				text-align: right;
			}
		</style>
		<div>
			<span class="crst-size-10ex">URL: </span>
			<input 
				type="text" 
				name="facebook" 
				id="facebook" 
				class="regular-text"
				value="<?php echo esc_attr( $facebook ); ?>" 
			/>
		</div>
		<div>
			<span class="crst-size-10ex">ID: </span>
			<input 
				type="text" 
				name="facebook_id" 
				id="facebook_id" 
				class="regular-text"
				value="<?php echo esc_attr( $facebook_id ); ?>" 
			/>
		</div>
		<p class="description">URL は http://www.facebook.com/ で始まるようにしてください。
		<br />ID は OpenGraph タグを生成するときに必要です。</p>
	<?php

}

function crst_setting_twitter(){
    $twitter = safe_value( get_option( 'twitter', null ), '' );
    
	?>
		<style scope="true">
			.crst-size-10ex{
				display: inline-block;
				width: 10ex;
			}
			.crst-txt-right{
				text-align: right;
			}
		</style>
		<div>
			<span class="crst-size-10ex">@ </span>
			<input 
				type="text" 
				name="twitter" 
				id="twitter" 
				class="regular-text"
				value="<?php echo esc_attr( $twitter ); ?>" 
			/>
		</div>
		<p class="description">URL ではなく、ユーザー名を @ なしで入力してください。</p>
	<?php

}

/*	新着表示日数の設定項目を表示する
-----------------------------------------------*/
function crst_setting_new_days(){
    $new_days = absint( get_option( 'new_days', 7 ) );
    
	?>
	    <input 
	    	type="text" 
	    	name="new_days" 
	    	id="new_days" 
	    	size="1" 
	    	value="<?php echo esc_attr( $new_days ); ?>" 
	    />
		日間
	<?php

}
 
/*	更新表示日数の設定項目を表示する
-----------------------------------------------*/
function crst_setting_modified_days() {
    $modified_days = absint( get_option( 'modified_days', 7 ) );
    
	?>
	    <input 
	    	type="text" 
	    	name="modified_days" 
	    	id="modified_days" 
	    	size="1" 
	    	value="<?php echo esc_attr( $modified_days ); ?>" 
	    />
		日間
	<?php

}



?>