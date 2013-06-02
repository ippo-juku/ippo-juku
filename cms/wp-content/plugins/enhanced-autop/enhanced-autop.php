<?php
/**
 * Plugin Name: Enhanced Autop
 * Plugin URI: 
 * Description: Revolutional improvement of autop
 * Author: Yuuki Iwanaga
 * Author URI: http://yuuki.creasty.com/
 * Version: 1.0
 * Requires at least: 3.3
 * Tested up to: 3.4.1
 * Stable tag: 1.0
 */

if( !defined( 'ABSPATH' ) )
	die( 'You are not allowed to call this page directly.' );


class enhanced_autop{
	public $lib;
	
	public $tag_blocks;
	public $tag_depends;
	public $allblocks;
	
	private $wp_protect = array();
	private $wp_protect_counter = 0;
	private $wp_protect_group;
	
	public function __construct(){
		$this->lib = plugins_url( '/lib/', __FILE__ );
		
		$this->tag_blocks = 'address|article|aside|blockquote|caption|col|colgroup|details|div|dl|embed|fieldset|figcaption|figure|footer|form|header|hgroup|input|legend|map|math|menu|nav|object|ol|object|param|pre|section|select|style|summary|table|tbody|tfoot|thead|tr|ul|wp_protect|wp_noautop';
		$this->tag_depends = 'a|audio|canvas|dd|del|dt|ins|label|li|map|noscript|script|style|td|th|video';
		$this->tag_all = $this->tag_blocks . '|area|br|button|dd|dt|h[1-6]|hr|label|li|option|p|td|textarea|th|wp_br|wp_p';
		
		add_action( 'init', array( &$this, 'init' ) );
		add_filter( 'tiny_mce_before_init', array( &$this, 'tiny_mce_before_init' ) );
		add_action( 'before_wp_tiny_mce', array( &$this, 'before_wp_tiny_mce' ) );
		add_action( 'media_buttons', array( &$this, 'remove_wp_richedit_pre' ) );
		add_action( 'media_buttons', array( &$this, 'add_new_richedit_pre' ), 9 );
	}
	
	public function init(){
		global $wp_filter;
		$filters = array( 'enhanced_autop', 'the_content', 'term_description', 'the_excerpt', 'comment_text' );
		
		foreach( $filters as $filter ){
			remove_filter( $filter, 'shortcode_unautop' );
			remove_filter( $filter, 'wpautop' );
			
			add_filter( $filter, array( &$this, 'before_wpautop' ), -9998 );
			add_filter( $filter, array( &$this, 'wpautop' ), 20, 2 );
			add_filter( $filter, array( &$this, 'after_wpautop' ), 9999, 2 );
		}
		
		/*	Shortcodes before `before_wpautop()`
		-----------------------------------------------*/
		remove_filter( 'the_content', 'do_shortcode', 11 );
		add_filter( 'the_content', 'do_shortcode', -9999 );
	}
	public function before_wp_tiny_mce(){
		echo '<script src="' . $this->lib . 'editor.js"></script>';
	}
	
	public function tiny_mce_before_init( $settings ){
		$settings[ 'remove_linebreaks' ] = false;
		$settings[ 'fix_list_elements' ] = true;
		$settings[ 'verify_css_classes' ] = false;
		$settings[ 'convert_newlines_to_brs' ] = false;
		$settings[ 'apply_source_formatting' ] = false;
		$settings[ 'force_br_newlines' ] = false;
		$settings[ 'forced_root_block' ] = '';
		$settings[ 'element_format' ] = '';
		
		$settings[ 'extended_valid_elements' ] =
			( $settings[ 'extended_valid_elements' ] ? $settings[ 'extended_valid_elements' ] . ',' : '' )
			. 'wp_protect[*],wp_noautop[*],wp_noindent[*]';
		
		$settings[ 'setupcontent_callback' ] = 'switchEditors.setupcontent_callback';
		
		return $settings;
	}
	
	public function remove_wp_richedit_pre(){
		remove_filter( 'the_editor_content', 'wp_richedit_pre' );
	}
	public function add_new_richedit_pre(){
		global $wp_filter;
		
		if( isset( $wp_filter[ 'the_editor_content' ][10][ 'wp_richedit_pre' ] ) )
			add_filter( 'the_editor_content', array( &$this, 'new_richedit_pre' ) );
	}
	public function new_richedit_pre( $text ){
		if( empty( $text ) )
			return apply_filters( 'richedit_pre', '' );
			
		$output = convert_chars( $text );
		$output = htmlspecialchars( $output, ENT_NOQUOTES );
		
		return apply_filters( 'richedit_pre', $output );
	}
	
	public function before_wpautop( $pee ){
		$this->protect_init();
		
		$pee = $this->protect( $pee, 'pre|wp_noindent', 0 );
		$pee = $this->protect( $pee, 'script|style', 1 );
		
		$pee = preg_replace( '/<!--(.*?)-->/s', '', $pee ); // remove comments
		
		return $pee;
	}
	
	public function wpautop( $pee, $indent = 'enhanced_autop_indent' ){
		$TK = &$this->tag_blocks;
		$TD = &$this->tag_depends;
		$TA = &$this->tag_all;
		
		if( trim( $pee ) === '' ) return '';
		
		$pee = str_replace( array( "\r\n", "\r" ), "\n", $pee ); // cross-platform newlines
		
		$pee = preg_replace( '!\s*<(/?(?:' . $TK . ')[^>]*)>\s*!', "\n\n<$1>\n\n", $pee );
		$pee = preg_replace( '!(</(?:' . $TA . ')>)!', "$1\n\n", $pee );
		$pee = preg_replace( '|(<p[^>]*>.*?</p>)|s', "\n\n$1\n\n", $pee );
		$pee = preg_replace( '|<br />\s*<br />|', "\n\n", $pee );
		
		$pee = preg_replace( '!\n\s*\n!u', '<wp_dnl />', $pee );
		$pee = str_replace( "\n", '<wp_snl />', $pee );
		$pee = str_replace( '<wp_dnl />', "\n\n", $pee );
		
		$pee = preg_replace_callback( '!<((' . $TD . ')[^>]*)>([^\n]*?)</\2>!u', function( $m ){
			if( preg_match( '!^\s*<wp_snl />!', $m[3] ) && preg_match( '!<wp_snl />\s*$!', $m[3] ) )
				return $m[0];
			
			$m[3] = preg_replace( '!<(/?)((' . $TD . ')[^>]*)>!', '<$1wp_ni_$2>', $m[3] );
			return "<wp_ni_{$m[1]}>{$m[3]}</wp_ni_{$m[2]}>";
		}, $pee );
		
		$pee = preg_replace( '!<(/?(' . $TD . ')[^>]*)>!', "\n\n<$1>\n\n", $pee );
		$pee = preg_replace( '|<(/?)wp_ni_([^>]+)>|', '<$1$2>', $pee );
		$pee = str_replace( '<wp_snl />', "\n", $pee );
		$pee = preg_replace( "/\n\n+/", "\n\n", $pee ); // take care of duplicates
		
		$pee = $this->indent( $pee, $indent );
		
		$pees = preg_split( '/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY ); // make paragraphs, including one at the end
		$pee = '';
		
		foreach( $pees as $tinkle ){
			$pee .= '<wp_p>' . trim( $tinkle, "\n" ) . "</wp_p>\n";
		}
		
		$pee = preg_replace( '|<wp_p>\s*</wp_p>\n*|', '', $pee );
		$pee = preg_replace( '|<wp_p>(\s+)|', '$1<wp_p>', $pee );
		$pee = preg_replace( '|<wp_p>(<li.+?)</wp_p>|', '$1', $pee ); // problem with nested lists
		$pee = preg_replace( '!<wp_p>(</?(?:' . $TA . ')[^>]*>)!', '$1', $pee );
		$pee = preg_replace( '!(</?(?:' . $TA . ')[^>]*>)\s*</wp_p>!', '$1', $pee );
		
		$pee = preg_replace( '|(?<!<br />)\n(\s*)(\S)|', "\n$1<wp_br />$2", $pee );
		$pee = preg_replace( '!<wp_br />(\s*</?(?:' . $TA . ')[^>]*>)!', '$1', $pee );
		
		$pee = preg_replace( '!<wp_p>(</?(?:' . $TD . ')[^>]*>)</wp_p>!', '$1', $pee );
		$pee = preg_replace( '!(<(?:' . $TD . ')[^>]*>)</wp_p>!', '$1', $pee );
		$pee = preg_replace( '!(<\w+[^>]*>)</wp_p>!', '$1', $pee );
		$pee = preg_replace( '!<wp_p>(</\w+[^>]*>)!', '$1', $pee );
		$pee = preg_replace( '!<wp_p>(<(?:' . $TD . ')[^>]*>)$!m', '$1', $pee );
		$pee = preg_replace( '!<wp_br />(\s*</(?:' . $TD . ')[^>]*>)(</wp_p>)?$!m', '$1', $pee );
		$pee = preg_replace( '!(<(\w+)[^>]*>\s*)<wp_p>(<(?:img)[^>]*>)</wp_p>(\s*</\2>)!', '$1$3$4', $pee );
		$pee = preg_replace_callback( '!\t*<wp_noautop>\n*(.+?)\t*</wp_noautop>\n*!s', function( $m ){
			$m[1] = preg_replace( '!<(/?)wp_(p|br)( /)?>!', '', $m[1] );
			return $m[1];
		}, $pee );
		
		$pee = preg_replace( '!<(/?)wp_(p|br)( /)?>!', '<$1$2$3>', $pee );
		
		return $pee;
	}
	
	public function after_wpautop( $pee, $indent = 'enhanced_autop_indent' ){
		$pee = $this->protect_done( $pee, 1 );
		
		if( $indent == 'enhanced_autop_indent' )
			$indent = apply_filters( $indent, $indent );
		
		if( is_numeric( $indent ) && $indent >= 0 )
			$pee = preg_replace_callback( '!(\t+)(<(script|style)[^>]*>.+?</\3>)!s', function( $m ){
				preg_match_all( '|^\t*|mu', $m[0], $tab );
				$min = min( $tab[0] );
				if( $m[1] = substr( $m[1], 0, strlen( $m[1] ) - strlen( $min ) ) )
					return $min . preg_replace( '|^|m', $m[1], $m[2] );
				return $m[0];
			}, $pee );
			
		$pee = $this->protect_done( $pee, 0 );
		
		$this->protect_init();
		
		$pee = preg_replace( '|<((\w+)[^>]*)>\s*</\2>|', '<$1></$2>', $pee );
		$pee = preg_replace( '!\t*</?wp_noindent>\n*!', '', $pee );
		
		return $pee;
	}
	
	public function indent( $content, $indent ){
		if( $indent == 'enhanced_autop_indent' )
			$indent = apply_filters( $indent, $indent );
		
		if( !is_numeric( $indent ) || $indent < 0 )
			return $content;
		
		$lines = explode( "\n", $content );
		$content = '';
		
		foreach( $lines as $tinkle ){
			$tinkle = trim( $tinkle );
			$block = preg_match( '!^<(/?)(\w+)[^>]*?(/?)>$!', $tinkle, $m );
			
			if( $block ){
				if( in_array( $m[2], array( 'br', 'hr', 'img', 'input', 'wp_noautop' ) ) ) // do not change indention level
					$m[3] = true;
				elseif( $m[1] && !$m[3] )
					$indent--;
			}
			
			if( $indent > 0 )
				$content .= str_repeat( "\t", $indent );
			
			$content .= $tinkle . "\n";
			
			if( $block && !$m[1] && !$m[3] )
				$indent++;
		}
		
		return $content;
	}
	
	private function _protect_add( $m ){
		$id = $this->wp_protect_counter++;
		$tmp = "<wp_protect id-$id />";
		$this->wp_protect[ $this->wp_protect_group ][ $tmp ] = $m[0];
		return $tmp;
	}
	private function protect_init(){
		$this->wp_protect = array();
		$this->wp_protect_counter = 0;
		$this->wp_protect_group = 0;
	}
	private function protect( $content, $tag, $group = 0 ){
		$this->wp_protect_group = $group;
		$this->wp_protect[ $group ] = array();
		return preg_replace_callback( '!<(' . $tag . ')[^>]*>.*?</\\1>!is', array( &$this, '_protect_add' ), $content );
	}
	private function protect_done( $content, $group = 0 ){
		$tmps = &$this->wp_protect[ $group ];
		return str_replace( array_keys( $tmps ), array_values( $tmps ), $content );
	}
	
}

new enhanced_autop();


function enhanced_the_content( $more_link_text = null, $stripteaser = false, $indent = false ){
	$content = get_the_content( $more_link_text, $stripteaser );
	$content = apply_filters( 'the_content', $content, $indent );
	$content = str_replace( ']]>', ']]&gt;', $content );
	echo $content;
}


?>