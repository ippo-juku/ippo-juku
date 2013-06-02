<?php

/*=== Custom Post Type
==============================================================================================*/
add_action( 'init', 'create_post_type' );
function create_post_type(){
	global $wp_rewrite;
	
	/*	Works
	-----------------------------------------------*/
	register_post_type( 'news',
		array(
			'labels' => array(
				'name' => 'お知らせ',
				'singular_name' => 'お知らせ'
			),
			'public' => true,
			'menu_position' => 5,
			'query_var' => false,
			'has_archive' => true,
			'rewrite' => array( 'with_front' => false )
		)
	);
	register_taxonomy(
		'news_cat',
		'news',
		array(
			'label' => 'カテゴリー',
			'hierarchical' => false,
			'rewrite' => array(
				'slug' => 'news',
				'with_front' => false
			)
		)
	);
	
	//$wp_rewrite->add_rewrite_tag( '%news_id%', '([^/]+)', 'post_type=news&p=' );
	//$wp_rewrite->add_permastruct( 'news', '/news/%news_id%', false );
}
/*

add_filter( 'post_type_link', 'myposttype_permalink', 1, 3 );
function myposttype_permalink( $post_link, $id = 0, $leavename ){
	global $wp_rewrite;
	$post = &get_post($id);
	
	if ( is_wp_error( $post ) )
		return $post;
	
	$newlink = $wp_rewrite->get_extra_permastruct( $post->post_type );
	$newlink = str_replace( '%' . $post->post_type . '_id%', $post->ID, $newlink );
	$newlink = home_url( user_trailingslashit( $newlink ) );
	
	return $newlink;
}
*/


/*=== Primitive
==============================================================================================*/
/*	Supports
-----------------------------------------------*/
add_theme_support( 'menus' );
add_theme_support( 'post-thumbnails' );
add_post_type_support( 'page', 'excerpt' );
//add_theme_support( 'post-formats', array( 'aside', 'link', 'gallery', 'status', 'quote', 'image' ) );

/*	Image Sizes
-----------------------------------------------*/
set_post_thumbnail_size( 817, 260, true ); // eye-catch image
add_image_size( 'post-thumbnail-small', 267, 189, true ); // home tiles
add_image_size( 'hero-image', 1100, 0, true ); // layout-1 fullwidth


/*=== Custom Query Vars
==============================================================================================*/
add_action( 'parse_query', 'parse_query_search' );
function parse_query_search( $query ){
	if( !is_search() )
		return;
	
	$post_type = $_REQUEST[ 'post_type' ];
	
	if( isset( $post_type ) && !empty( $post_type ) ){
		set_query_var( 'post_type', $post_type );
	}else{
		set_query_var( 'post_type', 'post' );
	}
}

/*	検索まわり
-----------------------------------------------*/
add_action( 'parse_query', 'parse_query_plus' );
function parse_query_plus( $query ){
	global $wp_query;
	
	$s = $wp_query->query[ 's' ];
	
	if( isset( $s ) && empty( $s ) ){
		$wp_query->post_count = 0;
		$wp_query->posts = array();
		set_query_var( 'is_search_box', 'true' );
	}
}
add_filter( 'posts_search', 'custom_search', 10, 2 );
function custom_search( $search, $wp_query ){
	$s = $wp_query->query[ 's' ];
	
	if( isset( $s ) ){
		$wp_query->is_search = true;
	}
	return $search;
}
add_action( 'parse_request', 'parse_request_plus' );
function parse_request_plus( $wp ){
	if( $wp->query_vars[ 's' ] ){
		$s = &$wp->query_vars[ 's' ];
		$s = mb_convert_kana( $s, 'saKHV', 'UTF-8' );
		$s = strip_tags( $s );
		$s = trim( $s );
		$s = preg_replace( '|\s+|u' ,' ', $s );
		
	}
}


/*=== Remove the Width and Height Attributes From WP Image Uploader
==============================================================================================*/
add_filter( 'post_thumbnail_html', 'remove_width_attribute', 10 );
add_filter( 'image_send_to_editor', 'remove_width_attribute', 10 );
function remove_width_attribute( $html ){
	return preg_replace( '/(width|height)="\d*"\s/', '', $html );
}


/*=== Custom Filters
==============================================================================================*/
remove_filter( 'comments_template', 'dsq_comments_template' );
remove_action( 'loop_end', 'dsq_loop_end' );

add_filter( 'the_content', 'replace_text_wps' );
function replace_text_wps( $content ){
	$pairs = array(
	);
	
	if( get_option( 'twitter' ) )
		$pairs[ '@twitter' ] = vsprintf( '<a href="http://twitter.com/%s" target="_blank">@%1$s</a>', get_option( 'twitter' ) );
	
	if( get_option( 'facebook' ) )
		$pairs[ '@facebook' ] = vsprintf( '<a href="%s" target="_blank">%1$s</a>', get_option( 'facebook' ) );
	
	$content = strtr( $content, $pairs );
	return $content;
}

if( !is_feed() && !is_admin() && defined( 'WP_USE_THEMES' ) && WP_USE_THEMES == true ){
	add_filter( 'attachment_link', 'relative_url', 1 );
	add_filter( 'author_link', 'relative_url', 1 );
	//add_filter( 'feed_link', 'relative_url', 1 );
	add_filter( 'day_link', 'relative_url', 1 );
	add_filter( 'month_link', 'relative_url', 1 );
	add_filter( 'year_link', 'relative_url', 1 );
	add_filter( 'term_link', 'relative_url', 1 );
	add_filter( 'category_link', 'relative_url', 1 );
	add_filter( 'page_link', 'relative_url', 1 );
	add_filter( 'post_link', 'relative_url', 1 );
	add_filter( 'the_permalink', 'relative_url' );
	add_filter( 'get_pagenum_link', 'relative_url' );
	
}

add_filter( 'the_tags', 'crst_the_tags', 1 );
function crst_the_tags( $tags ){
	if( empty( $tags ) )
		$tags = 'タグなし';
	
	return $tags;
}

add_action( 'init', 'deregister_jquery', 20 );
function deregister_jquery(){
	if( !is_admin() )
		wp_deregister_script( 'jquery' );
}


/*=== Admin Bar 関連
==============================================================================================*/
add_action( 'admin_bar_menu', 'crst_admin_bar_menu', 999 );
function crst_admin_bar_menu( $wp_admin_bar ){
	$wp_admin_bar->remove_menu( 'wp-logo' );
	$wp_admin_bar->remove_node( 'themes' );
	$wp_admin_bar->remove_node( 'customize' );
	
	$wp_admin_bar->add_node( array(
		'id' => 'samplepage',
		'title' => 'サンプルページ',
		'href' => home_url( '/sample' ),
		'parent' => 'site-name'
	) );
}


/*=== TPL function
==============================================================================================*/
add_action( 'get_header', 'tpl_init' );
function tpl_init(){
	global $params, $post;
	
	$_page_title = crstUtil::title_array();
	
	$params = array(
		'modules' => 
			is_singular()
			? safe_value( get_post_meta( $post->ID, '_crst_module', true ), array(), 'array' ) 
			: array(),
		'canonical'=> 
			crstUtil::canonical_rel( $post ),
		'pagetitle' =>
			$_page_title,
		'doctitle' => 
			$_page_title[ 'doc' ],
		'description' => 
			is_singular() 
			? crst::get_the_excerpt( $post, 110, true, false )
			: null,
		'template' =>
			crstUtil::get_template_info(),
		'css' =>
			crstUtil::css(),
	);
	
	foreach( $params as $key => $val ){
		$params[ $key ] = apply_filters( 'tpl_param_' . $key, $params[ $key ] );
	}
	
	/*	Moduels
	-----------------------------------------------*/
	$params[ 'modules' ] = array_unique( $params[ 'modules' ] );
	natsort( $params[ 'modules' ] );
	
	if( !empty( $params[ 'modules' ] ) )
		$params[ 'modules__out' ] = ' \'' . implode( '\', \'', $params[ 'modules' ] ) . '\' ';
	
	/*	Pagetitle
	-----------------------------------------------*/
	$params[ 'pagetitle' ] = crstUtil::title_from_array( $params[ 'pagetitle' ] );
}

/*	Get params
-----------------------------------------------*/
function tpl_param( $key, $default = '', $option = 'none' ){
	echo get_tpl_param( $key, $default, $option );
}
function get_tpl_param( $key, $default = false, $option = 'none' ){
	global $params;
	
	$param = safe_value( $params[ $key ], $default, $option );
	
	return $param;
}


/*=== WP Head
==============================================================================================*/
/*	Remove
-----------------------------------------------*/
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
//remove_action( 'wp_head', 'noindex', 1 );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rel_canonical' );
remove_action( 'wp_head', 'feed_links_extra', 3 );

add_filter( 'show_recent_comments_widget_style', function( $frag, $id_base ){
	return false;
});

/*	Head Cleaner
-----------------------------------------------*/
add_action( 'get_header', 'wp_head_cleaner_start', 9999 );
function wp_head_cleaner_start(){
	ob_start();
}
add_action( 'wp_head', 'wp_head_cleaner_end', 9999 );
function wp_head_cleaner_end(){
	$render = ob_get_contents();
	ob_end_clean();
	
	$start = strpos( $render, '<head' );
	if( $start >= 0 )
		$start = strpos( $render, '>', $start );
	else
		$start = -1;
	
	echo mb_substr( $render, 0, $start + 1 ), NL;
	
	$render = mb_substr( $render, $start + 1 );
	$tags = array();
	
	$render = preg_replace( '!\stype=("|\')text/(javascript|css)\1!', '', $render );
	
	preg_match_all( '!<(meta|base)[^>]*?>!', $render, $meta );
	preg_match_all( '!<link[^>]*?>!', $render, $link );
	preg_match_all( '!\t*<style[^>]*?>.*?</style>!s', $render, $style );
	preg_match_all( '!\t*<(noscript|script)[^>]*?>.*?</\1>!s', $render, $script );
	
	preg_match( '!<title>.+?</title>!', $render, $title );
	
	sort( $meta[0] );
	
	foreach( $script[0] as &$sc ){
		$sc = code_indent( $sc, 0, true );
	}
	foreach( $style[0] as &$st ){
		$st = code_indent( $st, 0, true );
	}
	
	$tags = array_merge( $meta[0], $title, $link[0], $script[0], $style[0] );
	
	unset( $meta, $title, $link, $script, $style );
	
	if( !empty( $tags ) ){
		$render = '';
		$render .= implode( $tags, "\n" );
		$render = code_indent( $render, 1 );
		echo $render . "\n";
	}
	
	unset( $tags, $render );
}

/*	Primitive Head
-----------------------------------------------*/
add_action( 'wp_head', 'wp_head_init', 0 );
function wp_head_init(){
	global $post, $params;
	
	/*	Meta
	-----------------------------------------------*/
	if( get_tpl_param( 'description' ) )
		echo '<meta name="description" content="', esc_attr( get_tpl_param( 'description' ) ), '" />';
	
	/*	Facebook OG
	-----------------------------------------------*/
	if( get_option( 'facebook_id' ) && get_tpl_param( 'canonical' ) ){
		$og_type = is_front_page() ? 'website' : ( is_home() ? 'blog' : 'article' );
		
		echo '<meta property="og:site_name" content="', esc_attr( get_bloginfo( 'name' ) ), '" />';
		echo '<meta property="og:title" content="', esc_attr( get_tpl_param( 'doctitle' ) ), '" />';
		echo '<meta property="og:url" content="', get_tpl_param( 'canonical' ), '" />';
		echo '<meta property="og:type" content="', $og_type, '" />';
		
		if( is_singular() && has_post_thumbnail() ){
			
			$thumb = get_post_thumbnail_id( $post->ID );
			$img = wp_get_attachment_image_src( $thumb, 'post-thumbnail-small' );
			
			if( $img ){
				if( !preg_match( '|^(https?:)?//|', $img[0] ) )
					$img[0] = home_url( $img[0] );
				
				echo '<meta property="og:image" content="', esc_url( $img[0] ), '" />';
			}
		}
		
		if( ( is_front_page() || is_singular() ) && get_tpl_param( 'description' ) )
			echo '<meta property="og:description" content="', esc_attr( get_tpl_param( 'description' ) ), '" />';
		
		echo '<meta property="fb:app_id" content="', get_option( 'facebook_id' ), '" />';
	}
	
	/*	Link
	-----------------------------------------------*/
	if( get_tpl_param( 'canonical' ) )
		echo '<link rel="canonical" href="', get_tpl_param( 'canonical' ), '" />';
	
	if( is_single() )
		echo '<link rel="shortlink" href="', home_url( '?p=' . $post->ID ), '" />';

}

?>