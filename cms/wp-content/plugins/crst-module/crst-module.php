<?php
/**
 * Plugin Name: Creasty Module
 * Plugin URI: 
 * Description: 
 * Author: Yuuki Iwanaga
 * Author URI: http://yuuki.creasty.com/
 * Version: 1.0
 * Requires at least: 3.3
 * Tested up to: 3.4.1
 * Stable tag: 1.0
 */

if( !defined( 'ABSPATH' ) )
	die( 'You are not allowed to call this page directly.' );

require( 'shortcodes.php' );

class crst_module{
	public $library = array(
		'carousel' => array(
			'name' => 'カーセルスライダー'
		),
		'collapse' => array(
			'name' => '折りたたみコンテンツ',
			'pattern' => '|(?<!\[)\[collapse[^\]]*\]|'
		),
		'data' => array( 
			'name' => 'テーブル',
			'pattern' => '|<table[^>]*>|'
		),
		'fancybox' => array(
			'name' => '画像のズーム'
		),
		'form' => array(
			'name' => 'フォーム',
			'pattern' => '!<(form|input|textarea|select)[^>]*>!'
		),
		'slide' => array(
			'name' => 'スライダー',
			'pattern' => '|(?<!\[)\[slide[^\]]*\]|'
		),
		'syntax' => array(
			'name' => 'シンタックスハイライター'
		),
		'tab' => array(
			'name' => 'タブナビゲーション',
			'pattern' => '|(?<!\[)\[tabnav[^\]]*\]|'
		),
		'tooltip' => array(
			'name' => 'ツールチップ'
		)
	);
	
	public function __construct(){
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'save_post', array( &$this, 'save_post' ) );
	}
	
	public function admin_init(){
		add_meta_box( 'crst_headcode', 'カスタムコード', array( &$this, 'meta_box_headcode' ), 'post', 'normal', 'high' );
		add_meta_box( 'crst_headcode', 'カスタムコード', array( &$this, 'meta_box_headcode' ), 'page', 'normal', 'high' );
		
		add_meta_box( 'crst_module', 'モジュール', array( &$this, 'meta_box_module' ), 'post', 'normal', 'low' );
		add_meta_box( 'crst_module', 'モジュール', array( &$this, 'meta_box_module' ), 'page', 'normal', 'low' );
	}
	
	public function save_post( $post_id ){
		if( !wp_verify_nonce( $_POST[ 'crst_module_nonce' ], 'crst_module' ) )
			return $post_id;
			
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		
		$the_post = get_post( $post_id );
		$content = &$the_post->post_content;
		
		$module = safe_value( get_post_meta( $post_id, '_crst_module', true ), array(), 'array' );
		
		/*	$_POST
		-----------------------------------------------*/
		$post_module = &$_POST[ 'crst_module' ];
		if( safe_value( $post_module, false, 'array' ) )
			$module = array_merge( $module, array_keys( $post_module ) );
		
		/*	Pattern
		-----------------------------------------------*/
		foreach( $this->library as $key => $dt ){
			if( $dt[ 'pattern' ] ){
				if( preg_match( $dt[ 'pattern' ], $content ) ){
					$module[] = $key;
				}elseif( in_array( $key, $module ) ){
					$index = array_search( $key, $module );
					array_splice( $module, $index, 1 );
				}
			}
		}
		
		array_unique( $module );
		
		update_post_meta( $post_id, '_crst_module', $module );
		
		/*	Headcode
		-----------------------------------------------*/
		$headcode = &$_POST['crst_headcode'];
		
		if( empty( $headcode ) )
			delete_post_meta( $post_id, '_crst_headcode' );
		else
			update_post_meta( $post_id, '_crst_headcode', $headcode );
		
	}
		
	function meta_box_module(){
		global $post;
		
		$lib = &$this->library;
		$module = safe_value( get_post_meta( $post->ID, '_crst_module', true ), array(), 'array' );
		
		?>
			<input 
				type="hidden" 
				name="crst_module_nonce" 
				id="crst_module_nonce" 
				value="<?php echo wp_create_nonce( 'crst_module' ); ?>" 
			/>
		<?php 
			foreach( $lib as $key => $dt ){
				$s = in_array( $key, $module ) ? 'checked' : '';
		?>
			<div>
				<label>
					<input 
						type="checkbox" 
						name="crst_module[<?php echo $key; ?>]" 
						id="crst_module_<?php echo $key; ?>" 
						value="on"
						<?php echo $s; ?> 
					/>
					<?php echo $dt['name']; ?>
				</label>
				&nbsp;
			</div>
		<?php } ?>
			<div class="clear"></div>
		<?php
	}
	function meta_box_headcode(){
		global $post;
		
		?>
			<textarea 
				name="crst_headcode" 
				id="crst_headcode_txt" 
				rows="5" 
				cols="30" 
				style="width:100%;"
			><?php echo esc_textarea( safe_value( get_post_meta( $post->ID, '_crst_headcode' ), '', 'string' ) ); ?></textarea>
		<?php
	
	}
}

new crst_module();

?>