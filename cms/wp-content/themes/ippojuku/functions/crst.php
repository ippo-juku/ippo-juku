<?php

/*=== CONSTANT
==============================================================================================*/
define( 'NL', "\n" );


/*=== Common Utils
==============================================================================================*/
function safe_value( $val, $fallback = false, $option = 'none' ){
	$op = true;
	
	switch( $option ){
		case 'array':
			$op = is_array( $val );
			break;
		case 'number':
			$op = is_numeric( $val );
			break;
		case 'bool':
			$op = is_bool( $val );
			break;
	}
	
	if( !isset( $val ) || empty( $val ) || !$op )
		return $fallback;
	
	return $val;
}

function array2attr( $array ){
	$buffer = '';
	
	foreach( $array as $key => $val ){
		if( is_string( $key ) && $val ){
			$key = sanitize_key( $key );
			$val = esc_attr( $val );
			
			$buffer .= " $key=\"$val\"";
		}
	}
	
	return $buffer;
}

/*	Admin User Detection
-----------------------------------------------*/
function is_admin_user(){
	return is_user_logged_in() && is_user_member_of_blog() && is_super_admin();
}

/*	Checked, selected, and disabled
-----------------------------------------------*/
function _checked( $checked, $current = true, $echo = true ){
	return __crst__checked_selected_helper( $checked, $current, $echo, 'checked' );
}
function _selected( $selected, $current = true, $echo = true ){
	return __crst__checked_selected_helper( $selected, $current, $echo, 'selected' );
}
function _disabled( $disabled, $current = true, $echo = true ){
	return __crst__checked_selected_helper( $disabled, $current, $echo, 'disabled' );
}
function __crst__checked_selected_helper( $helper, $current, $echo, $type ){
	if( (string) $helper === (string) $current )
		$result = " $type=\"$type\"";
	else
		$result = '';
	
	if( $echo )
		echo $result;
	
	return $result;
}


/*=== Code Indention
==============================================================================================*/
function code_indent( $code, $indent = 0, $relative = false ){
	$min = 0;
	
	if( $relative ){
		preg_match_all( '|^\t*|mu', $code, $m );
		$min = strlen( min( $m[0] ) );
	}
	
	if( $indent > 0 )
		$code = preg_replace( '|^|mu', str_repeat( "\t", $indent ), $code );
	
	if( $indent <= 0 )
		$code = preg_replace( '|^\t{0,' . abs( $min - $indent ) . '}|mu', '', $code );
	
	return $code;
}


/*=== Relative / Absolute URL
==============================================================================================*/
function relative_url( $url ){
	$base = preg_quote( $_SERVER['SERVER_NAME'] );
	$url = preg_replace( '!https?://' . $base . '(/|$)!', '/', $url );
	
	if( empty( $url ) )
		$url = '/';
	
	return $url;
}
function absolute_url( $url ){
	if( !preg_match( '|^(https?:)?//|', $url ) ){
		$protocol = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
		$url = preg_replace( '!^/?!', $protocol . $_SERVER['SERVER_NAME'] . '/', $url );
	}
	return $url;
}


/*=== File Version Query
==============================================================================================*/
function file_ver( $path, $echo = true, $html = false ){
	if( preg_match( '|^(https?:)?//([^/]*)|', $path, $m ) ){
		if( strpos( $m[2], $_SERVER['SERVER_NAME'] ) !== false )
			$path = relative_url( $path );
		else
			return;
	}
	
	$mtime = @filemtime( WWWROOT . $path );
	
	if( strpos( $path, '?' ) !== false )
		$path .= $html ? '&amp;' : '&';
	else
		$path .= '?';
	
	$path .= 'ver=' . date( 'Ymd', $mtime );
	
	if( $echo )
		echo $path;
	else 
		return $path;
}


/*=== Template Tags
==============================================================================================*/
class crst{

	function body_class(){
		global $wp_query;
		
		$cls = func_get_args();
		$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );
		
		$cls[] = 'tpl-' . get_tpl_param( 'template' );
		
		if( !empty( $post_type ) )
			$cls[] = 'type-' . $post_type;
		
		if( is_front_page() )
			$cls[] = 'page-home';
		if( is_home() )
			$cls[] = 'page-blog';
		
		if( is_single() )
			$cls[] = 'page-single';
		if( is_page() )
			$cls[] = 'page-normal';
		
		if( is_archive() )
			$cls[] = 'page-archive';
		if( is_date() )
			$cls[] = 'page-date';
		if( is_category() )
			$cls[] = 'page-category';
		if( is_tag() )
			$cls[] = 'page-tag';
		if( is_tax() )
			$cls[] = 'page-tax';
		
		if( is_search() )
			$cls[] = 'page-search';
		
		if( is_404() )
			$cls[] = 'page-404';
		
		if( $wp_query->max_num_pages > 0 )
			$cls[] = 'page-entries';
		
		echo implode( ' ', $cls );
	}
	
	function post_class( $common ='' ){
		echo self::get_post_class( null, $common );
	}
	function get_post_class( $post = null, $common = '' ){
		if( !$post )
			$post = &$GLOBALS['post'];
		
		if( !empty( $common ) )
			$cls = explode( ' ', $common );
		else
			$cls = array();
		
		if( self::is_new( $post ) ){
			$cls[] = 'post-new';
		}
		if( self::is_updated( $post ) ){
			$cls[] = 'post-updated';
		}
		if( !empty( $post->post_password ) ){
			$cls[] = 'post-protected';
		}
		if( isset( $post->post_status ) && 'private' == $post->post_status ){
			$cls[] = 'post-private';
		}
		
		$cat = get_the_category();
		$cat = $cat[0];
		$cls[] = 'category-' . sanitize_html_class( $cat->category_nicename );
		
		return implode( ' ', $cls );
	}

	function the_date( $format = '' ){
		echo self::get_the_date( null, $format );
	}
	function get_the_date( $post = null, $format = '' ) {
		if( !$post )
			$post = &$GLOBALS['post'];
		
		if( empty( $format ) )
			$format = get_option( 'date_format' );
		
		$date = mysql2date( $format, $post->post_date );
		
		return $date;
	}
	
	function the_content( $indent = 0 ){
		echo self::get_the_content( null, $indent );
	}
	function get_the_content( $post = null, $indent = 0 ){
		if( !$post )
			$post = &$GLOBALS['post'];
		
		$content = $post->post_content;
		$content = apply_filters( 'the_content', $content, $indent );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = rtrim( $content );
		return $content;
	}
	
	function the_excerpt( $len = 140, $forcelen = false, $ellipsis = true ){
		echo self::get_the_excerpt( null, $len, $forcelen, $ellipsis );
	}
	function get_the_excerpt( $post = null, $len = 140, $forcelen = false, $ellipsis = true ){
		if( !$post )
			$post = &$GLOBALS['post'];
		
		if( empty( $post->post_excerpt ) ){
			$content = $post->post_content;
		}else{
			$content = $post->post_excerpt;
			$ellipsis = false;
		}
		
		if( $moretag = strpos( $content, '<!--more-->' ) ){
			$content = crstUtil::format_content_short( $content, 0 );
			if( $forcelen ) $content = mb_substr( $content, 0, $moretag );
			$ellipsis = false;
		}else{
			$content = crstUtil::format_content_short( $content, $len );
		}
		
		if( $ellipsis ) $content .= '…';
		
		return $content;
	}
		
	function is_new( &$post ){
		$post_date = $post->post_date;
		$days = absint( get_option( 'new_days', 7 ) );
		return self::_is_widthin_days( $post_date, $days );
	}
	function is_updated( &$post ){
		$post_date = $post->post_modified;
		$days = absint( get_option( 'modified_days', 7 ) );
		return self::_is_widthin_days( $post_date, $days );
	}
	function _is_widthin_days( $post_date, $days = 7 ){
		if( in_array( strtotime( $post_date ), array( false, -1 ) ) ){
			return false;
		}
		$limit = current_time( 'timestamp' ) - ( $days - 1 ) * 24 * 3600;
		if( mysql2date( 'Y-m-d', $post_date ) >= date( 'Y-m-d', $limit ) ){
			return true;
		}
		return false;
	}
	
	function related_posts( $limit = 5 ){
		global $post;
		
		//$catID = wp_get_post_categories( $post->ID, array( 'fields' => 'ids' ) );
		//$catID = $catID[0];
		
		$tags = wp_get_post_tags( $post->ID );
    	
		if( !$tags ) return;
		
	    $tagIDs = array();
        for( $i = 0, $count = sizeof( $tags ); $i < $count; $i++ ){
            $tagIDs[$i] = $tags[$i]->term_id;
        }
		
		return query_posts( array(
			//'cat' => $catID,
			'tag__in' => $tagIDs,
			'post__not_in' => array( $post->ID ),
			'showposts'=> $limit,
			'caller_get_posts'=> 1
		) );
	}
	
	function password_form( $id ){
		global $post;
		$label = 'pwbox-'.( empty( $id ) ? rand() : $id );
		$output = '<form action="' . get_option('siteurl') . '/wp-pass.php" method="post">
<p>' . __("This post is password protected. To view it please enter your password below:") . '</p>
<p><label for="' . $label . '">' . __("Password:") . ' <input name="post_password" id="' . $label . '" type="password" size="20" /></label> <input type="submit" name="Submit" value="' . esc_attr__("Submit") . '" /></p>
</form>';
		return apply_filters( 'the_password_form', $output);
	}
	
	function nav_menu( $menu_name = '' ){
		if( empty( $menu_name ) && is_page() ){
			global $post;
			
			$prefix = '$';	
			$pance = get_post_ancestors( $post );
			
			if( empty( $pance ) ){
				$menu_name = $prefix . $post->post_name;
			}else{
				$menu_name = $prefix . get_post( array_pop( $pance ) )->post_name;
			}
		}
		
		$menu = wp_get_nav_menu_object( $menu_name );
		
		if( $menu && !is_wp_error( $menu ) && !isset( $menu_items ) )
			$menu_items = wp_get_nav_menu_items( $menu->term_id );
		
		if( !$menu || is_wp_error( $menu ) )
			return false;
		
		$nav_menu = array();
		
		foreach( (array) $menu_items as $key => $item ){
			$nav_menu[ $item->menu_order ] = array(
				'title' => $item->title,
				'description' => $item->description,
				'link' => relative_url( $item->url ),
				'attr_title' => $item->attr_title,
				'target' => $item->target,
				'classes' => implode( ' ', $item->classes )
			);
		}
		
		unset( $menu_items );
		
		return $nav_menu;
	}
	
}


/*=== Utilities
==============================================================================================*/
class crstUtil{
	function get_template_info(){
		global $post;
		
		$tpl = get_post_meta( $post->ID, '_wp_page_template', true );
		$tpl = safe_value( $tpl, 'default', 'string' );
		
		preg_match( '!\-?tpl\-([\w+\-]+)\.php$!', $tpl, $m );
		
		return $m ? $m[ 1 ] : 'default';
	}
	
	function css(){
		global $post;
		
		$root_path = get_template_directory() . '/css/';
		$root_uri = wp_make_link_relative( get_template_directory_uri() ) . '/css/';
		
		$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );
		
		switch( true ){
			case is_front_page():
				$ss = 'home';
				break;
			case is_home():
				$ss = 'post';
				break;
			case is_search():
				$ss = 'search';
				break;
			case is_404():
				$ss = '404';
				break;
		}
		
		if( !$ss && !empty( $post_type ) ){
			$ss = $post_type;
			$path = $post_type . '-' . $post->ID . '.css';
			
			if( ( is_singular() || is_tax() ) && file_exists( $root_path . $path ) )
				return $root_uri . $path;
		}
		
		$path = $ss . '.css';
		
		if( $ss && file_exists( $root_path . $path ) )
			return $root_uri . $path;
		else
			return false;
		
	}
	
	function title_array(){
		global $wp_query, $cat, $tag_id;
		
		$title = array();
		$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );
		$post_type_name = get_post_type_object( $post_type )->labels->name;
		
		if( is_single() || is_search() ){
			if( $post_type == 'post' )
				$title[ 'depth' ] = 'ブログ';
			elseif( $post_type != 'page' )
				$title[ 'depth' ] = $post_type_name;
		}
		
		if( is_front_page() ){
			$title[ 'after' ] = get_bloginfo( 'description' );
			$title[ 'doc' ] = get_bloginfo( 'description' );
		}elseif( is_home() ){
			$title[ 'main' ] = 'ブログ';
		}elseif( is_single() ){
			$title[ 'main' ] = get_the_title();
		}elseif( is_page() ){
			$title[ 'main' ] = get_the_title();
		}elseif( is_archive() ){
			if( is_category() ){
				$cat_name = get_category( $cat )->name;
				$title[ 'main' ] = array( 'カテゴリー', $cat_name );
			}elseif( is_tag() ){
				$tag_name = get_tag( $tag_id )->name;
				$title[ 'main' ] = array( 'タグ', $tag_name );
			}elseif( is_date() ){
				$date = get_query_var( 'year' ) . '年';
				
				if( get_query_var( 'monthnum' ) > 0 )
					$date .= get_query_var( 'monthnum' ) . '月';
				if( get_query_var( 'day' ) > 0 )
					$date .= get_query_var( 'day' ) . '日';
				
				$title[ 'main' ] = $date;
				$title[ 'doc' ] = $date . 'の記事';
			}elseif( is_tax() ){
				$taxonomy = get_query_var( 'taxonomy' );
				$term = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy );
				
				$title[ 'main' ] = array( 'カテゴリー', $term->name );
			}elseif( $post_type != 'post' && $post_type != 'page' ){
				$title[ 'main' ] = $post_type_name;
			}
		}elseif( is_404() ){
			$title[ 'main' ] = 'ページが見つかりません';
			$title[ 'doc' ] = '何かお探しですか？';
		}
		
		if( is_search() ){
			$searchparam = esc_html( get_query_var( 's' ) );
			
			$title[ 'main' ] = array( 'サイト内検索' );
			$title[ 'doc' ] = '検索';
			
			if( !empty( $searchparam ) ){
				$title[ 'main' ][1] = '&ldquo;' . $searchparam . '&rdquo;';
				$title[ 'doc' ] .= ': &ldquo;' . $searchparam . '&rdquo;';
			}
		}
		
		if( is_paged() )
			$title[ 'sub' ] = get_query_var( 'paged' ) . 'ページ';
		
		$title = apply_filters( 'title_array', $title );
		
		if( !isset( $title[ 'doc' ] ) ){
			if( is_string( $title[ 'main' ] ) )
				$title[ 'doc' ] = $title[ 'main' ];
			elseif( is_array( $title[ 'main' ] ) )
				$title[ 'doc' ] = implode( ': ', $title[ 'main' ] );
		}
		
		return $title;
	}
	
	function title_from_array( $ary ){
		/*	Structure
			
			main-1     main-2      sub    depth      site name     after
			Aaaaaaaaa: Bbbbbbbbb - Cccc | Dddddddd | Eeeeeeeeeee * Ffffffffff
		*/
		
		if( !is_array( $ary ) )
			return $ary;
		
		extract( array_merge( array(
			'main' => '',
			'sub' => '',
			'depth' => '',
			'after' => ''
		), $ary ) );
		
		$title = array();
		
		if( isset( $main ) && !empty( $main ) ){
			if( is_string( $main ) )
				$title[] = $main;
			else
				$title[] = implode( ': ', $main );
		}
		
		if( isset( $sub ) && !empty( $sub ) ){
			$title[] = '-';
			$title[] = $sub;
		}
		
		if( isset( $depth ) && !empty( $depth ) ){
			$title[] = '|';
			$title[] = $depth;
		}
		
		if( sizeof( $title ) > 0 )
			$title[] = '|';
		
		$title[] = get_bloginfo( 'name' );
		
		if( isset( $after ) && !empty( $after ) ){
			$title[] = '&#8226;';
			$title[] = $after;
		}
		
		return implode( ' ', $title );
	}
	
	function canonical_rel( $post ){
		global $wp_rewrite, $cat;
		
		$url = '';
		$post_type = get_post_type() ? get_post_type() : get_query_var( 'post_type' );
		$custom_post = ( $post_type != 'post' ) && ( $post != 'page' );
		
		if( is_front_page() ){
			$url = home_url( user_trailingslashit( $wp_rewrite->root ) );
		}elseif( is_home() ){
			$url = home_url( user_trailingslashit( $wp_rewrite->front ) );
		}elseif( is_singular() ){
			$url = get_permalink();
		}elseif( is_category() ){
			$url = get_category_link( $cat );
		}elseif( is_tag() ){
			$id = get_term_by( 'slug', get_query_var( 'tag' ), 'post_tag' )->term_id;
			$url = get_tag_link( $id );
		}elseif( is_archive() ){
			if( is_date() ){
				$year = get_query_var( 'year' );
				
				if( get_query_var( 'monthnum' ) > 0 )
					$month = get_query_var( 'monthnum' );
				
				if( get_query_var( 'day' ) > 0 )
					$day = get_query_var( 'day' );
				
				$url = get_day_link( $year, $month, $day );
			}elseif( is_tax() ){
				$taxonomy = get_query_var( 'taxonomy' );
				$term = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy );
				$url = get_term_link( $term );
			}elseif( $custom_post ){
				$url = get_post_type_archive_link( $post_type );
			}
		}
		
		$url = absolute_url( $url );
			
		return $url;
	}
	
	function format_content_short( $content, $len = 140 ){
		$content = preg_replace( '|(?:\[/?)[^/\]]+/?\]|s', '', $content );
		$content = strip_tags( $content );
		$content = wptexturize( $content );
		$content = trim( $content );
		$content = preg_replace( '|\s+|u' ,' ', $content );
		$content = mb_substr( $content, 0, $len );
		return $content;
	}
	
	function wp_list_bookmarks($args = '') {
		$defaults = array(
			'orderby' => 'name', 'order' => 'ASC',
			'limit' => -1, 'category' => '', 'exclude_category' => '',
			'category_name' => '', 'hide_invisible' => 1,
			'show_updated' => 0, 'echo' => 1,
			'categorize' => 1, 'title_li' => __('Bookmarks'),
			'title_before' => '<h2>', 'title_after' => '</h2>',
			'category_orderby' => 'name', 'category_order' => 'ASC',
			'class' => 'linkcat', 'category_before' => '<li id="%id" class="%class">',
			'category_after' => '</li>',
			'indent' => 0
		);
	
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
	
		$output = '';
	
		if ( $categorize ) {
			//Split the bookmarks into ul's for each category
			$cats = get_terms('link_category', array('name__like' => $category_name, 'include' => $category, 'exclude' => $exclude_category, 'orderby' => $category_orderby, 'order' => $category_order, 'hierarchical' => 0));
	
			foreach ( (array) $cats as $cat ) {
				$params = array_merge($r, array('category'=>$cat->term_id));
				$bookmarks = get_bookmarks($params);
				if ( empty($bookmarks) )
					continue;
				$output .= str_replace(array('%id', '%class'), array("linkcat-$cat->term_id", $class), $category_before);
				$catname = apply_filters( "link_category", $cat->name );
				$output .= "$title_before$catname$title_after\n\t<ul>\n";
				$output .= _walk_bookmarks($bookmarks, $r);
				$output .= "\n\t</ul>\n$category_after\n";
			}
		} else {
			//output one single list using title_li for the title
			$bookmarks = get_bookmarks($r);
	
			if ( !empty($bookmarks) ) {
				if ( !empty( $title_li ) ){
					$output .= str_replace(array('%id', '%class'), array("linkcat-$category", $class), $category_before);
					$output .= "$title_before$title_li$title_after\n\t<ul>\n";
					$output .= _walk_bookmarks($bookmarks, $r);
					$output .= "\n\t</ul>\n$category_after\n";
				} else {
					$output .= _walk_bookmarks($bookmarks, $r);
				}
			}
		}
	
		$output = apply_filters( 'wp_list_bookmarks', $output );
		
		$output = apply_filters( 'enhanced_autop', "<wp_noautop>$output</wp_noautop>", $indent );
		if ( !$echo )
			return $output;
		echo $output;
	}
	
}


class crstSearch{	
	function the_content( $len = 300 ){
		global $post;
		
		$s = preg_quote( get_query_var( 's' ) );
		$s = str_replace( ' ', '|', $s );
		
		$content = $post->post_content;
		
		$content = strip_tags( $content );
		$content = trim( $content );
		$content = preg_replace( '|\s+|u' ,' ', $content );
		
		$search = $content;
		$search = preg_replace( '/(' . $s . ')/ui', '<find />', $content, 1 );
		
		$pos = (int) mb_strpos( $search, '<find />' );
		$pos = max( 0, floor( $pos - $len * 0.1 ) );
		
		$top = '';
		if( $pos > 0 ){
			$top_len = min( $len * 0.1, $pos );
			$top = mb_substr( $content, 0, $top_len );
			if( $top_len != $pos )
				$top .= '… ';
			$len *= 0.9;
		}
		
		$content = mb_substr( $content, $pos, $len );
		
		$content = preg_replace( '/(' . $s . ')/ui', '<i class="label label-red">$1</i>', $content );
		
		$content .= '…';
		
		echo $top . $content;
	}
}


/*=== Breadcrumb
==============================================================================================*/
class Breadcrumb{
	static $args = array(
        'home_label'        => 'ホーム',
        'year_label'        => '%s年',
        'month_label'       => '%s月',
        'day_label'         => '%s日'
    );
    
	public function get_array(){
		global $post;
		
		$args = self::$args;
		
		$depth = array();
		$depth[] = array(
			'title' => $args['home_label'],
			'link' => get_bloginfo( 'url' )
		);
		self::_posts_array( $depth );
		
		if( is_tax() ){
			$taxonomy = get_query_var( 'taxonomy' );
			$term = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy );
			$tax_obj = get_taxonomies( array( 'name' => $taxonomy ), 'objects' );
			$post_type = $tax_obj[$taxonomy]->object_type[0];

			if( $post_type != 'post' && $post_type != 'page' ){
				$post_type_name = get_post_type_object( $post_type );
				$depth[] = array(
					'title' => $post_type_name->labels->name,
					'link' => get_post_type_archive_link( $post_type )
				);
			}
			
			if( is_taxonomy_hierarchical( $taxonomy ) && $term->parent != 0 ){
				$ancestors = array_reverse( get_ancestors( $term->term_id, $taxonomy ) );
				foreach( $ancestors as $ancestor_id ){
					$ancestor = get_term( $ancestor_id, $taxonomy );
					$depth[] = array(
						'title' => $ancestor->name,
						'link' => get_term_link( $ancestor, $term->slug )
					);
				}
			}
		}elseif ( is_attachment() ){
			if( $post->post_parent ){
				if( $parent_post = get_post( $post->post_parent ) ){
					self::_singular_array( $depth, $parent_post );
				}
			}
			$depth[] = array( 'title' => $parent_post->post_title, 'link' => get_permalink( $parent_post->ID ) );
		}elseif( is_singular() && !is_front_page() ){
			self::_singular_array( $depth, $post );
		}elseif( is_category() ){
			global $cat;
			$category = get_category( $cat );
			if( $category->parent != 0 ){
				$ancestors = array_reverse( get_ancestors( $category->term_id, 'category' ) );
				foreach( $ancestors as $ancestor_id ){
					$ancestor = get_category( $ancestor_id );
					$depth[] = array( 'title' => $ancestor->name, 'link' => get_category_link( $ancestor->term_id ) );
				}
			}
		}elseif( is_date() ){
			$year = get_query_var( 'year' );
			$month = get_query_var( 'monthnum' );
			$day = get_query_var( 'day' );
			
			if( $year > 0 )
				$depth[] = array(
					'title' => sprintf( $args['year_label'], $year ), 
					'link' => get_year_link( $year )
				);
			if( $month > 0 )
				$depth[] = array(
					'title' => sprintf( $args['month_label'], $month ), 
					'link' => get_month_link( $year, $month )
				);
			if( $day > 0 )
				$depth[] = array( 
					'title' => sprintf( $args['day_label'], $day ), 
					'link' => get_day_link( $year, $month, $day )
				);
		}
		
	    return $depth;
	}
	 
	private function _singular_array( &$depth, $post ){
		$post_type = $post->post_type;
		if( is_post_type_hierarchical( $post_type ) ){
			$ancestors = array_reverse( get_post_ancestors( $post ) );
			
			if( !count( $ancestors ) )
				return;
				
			$ancestor_posts = get_posts( 'post_type=' . $post_type . '&include=' . implode( ',', $ancestors ) );
			foreach( $ancestors as $ancestor ){
				foreach( $ancestor_posts as $ancestor_post ){
					if( $ancestor == $ancestor_post->ID ){
						$depth[] = array(
							'title' => $ancestor_post->post_title, 
							'link' => get_permalink( $ancestor_post->ID )
						);
					}
				}
			}
	    }else{
			$post_type_taxonomies = get_object_taxonomies( $post_type, false );
			
			if( $post_type != 'post' && $post_type != 'page' ){
				$post_type_name = get_post_type_object( $post_type );
				$depth[] = array(
					'title' => $post_type_name->labels->name,
					'link' => get_post_type_archive_link( $post_type )
				);
			}
			
			if( !is_array( $post_type_taxonomies ) || !count( $post_type_taxonomies ) )
				return;
			
			foreach( $post_type_taxonomies as $tax_slug => $taxonomy ){
				if( !$taxonomy->hierarchical )
					continue;
					
				$terms = get_the_terms( $post->ID, $tax_slug );
				if( !$terms )
					continue;
					
				$term = array_shift( $terms );
				
				if( $term->parent == 0 ){
					$ancestors = array_reverse( get_ancestors( $term->term_id, $tax_slug ) );
					foreach( $ancestors as $ancestor_id ){
						$ancestor = get_term( $ancestor_id, $tax_slug );
						$depth[] = array(
							'title' => $ancestor->name, 
							'link' => get_term_link( $ancestor, $tax_slug )
						);
					}
					
					$depth[] = array(
						'title' => $term->name, 
						'link' => get_term_link( $term, $tax_slug )
					);
					
					break;
				
				}
				
			}
			
		}
	}
	
	private function _posts_array( &$depth ){
		if( is_page() || is_front_page() || is_404() ){
			return;
		}elseif( is_tax() ){
			$tax = get_taxonomy( get_query_var( 'taxonomy' ) );
			if( count( $tax->object_type ) != 1 || $tax->object_type[0] != 'post' )
				return;
		}elseif( is_home() && !get_query_var( 'pagename' ) ){
			return;
		}elseif( !is_category() && !is_tag() ){
			$post_type = get_query_var( 'post_type' ) ? get_query_var( 'post_type' ) : 'post';
			if( $post_type != 'post' )
				return;
		}
		
		if( !is_home() && get_option( 'show_on_front' ) == 'page' && $posts_page_id = get_option( 'page_for_posts' ) ){
			$posts_page = get_post( $posts_page_id );
			$depth[] = array(
				'title' => $posts_page->post_title,
				'link' => get_permalink( $posts_page->ID )
			);
		}
	}
	
}


/*=== Pagenation
==============================================================================================*/
class Pagenation{
	function get(){
		global $wp_query;
		
		$current = max( 1, get_query_var( 'paged' ) );
		$max = max( 1, $wp_query->max_num_pages );
		return self::make( $current, $max );
	}
	function make( $current, $pagemax, $entries = 10, $edge = 2 ){
		$html = array();
		
		$ne_half = ceil( $entries / 2 );
		$upper_limit = $pagemax - $entries + 1;
		$start = $current > $ne_half ? max( min( $current - $ne_half, $upper_limit ), 1 ) : 1;
		$end = $current > $ne_half ? min( $current + $ne_half - 1, $pagemax ) : min( $entries, $pagemax );
		
		$start = ( $start > $edge + 1 ) ? $start : 1;
		$end = ( $end < $pagemax - $edge ) ? $end : $pagemax;
		
		// Prev
		if( $current > 1 ){
			$html[] = self::entriyhtml( $current - 1, 'page-new', '&laquo;', '新しい記事へ' );
		}
		// Starting Point
		if( $start > $edge + 1 ){
			for( $i = 1; $i <= $edge; $i++ ){
				$html[] = self::entriyhtml( $i, $current );
			}
			$html[] = '<li class="ellipsis">...</li>';
		}
		
		// Entries
		for( $i = $start; $i <= $end; $i++ ){
			$html[] = self::entriyhtml( $i, $current );
		}
		
		// Ending Point
		if( $end < $pagemax ){
			$html[] = '<li class="ellipsis">...</li>';
			for( $i = $pagemax - $edge + 1; $i <= $pagemax; $i++ ){
				$html[] = self::entriyhtml( $i, $current );
			}
		}
		//Next
		if( $current < $pagemax ){
			$html[] = self::entriyhtml( $current + 1, 'page-old', '&raquo;', '古い記事へ' );
		}
		
		return implode( "\n", $html );
	}
	function entriyhtml( $num, $class, $name = '', $title = '' ){
		if( is_numeric( $class ) && $class == $num ){
			$class = ' active';
		}elseif( is_string( $class ) ){
			$class = ' ' . $class;
		}else{
			$class = '';
		}
		$name = empty( $name ) ? $num : $name;
		$title = empty( $title ) ? $num . 'ページへ' : $title;
		$link = esc_url( get_pagenum_link( $num ) );
		return "<li><a href=\"$link\" title=\"$title\" class=\"btn$class\">$name</a></li>";
	}
}



?>