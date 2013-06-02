<?php

/*=== Creasty Library
==============================================================================================*/
require_once( TEMPLATEPATH . '/functions/crst.php' );
require_once( TEMPLATEPATH . '/functions/init.php' );
require_once( TEMPLATEPATH . '/functions/widgets.php' );
require_once( TEMPLATEPATH . '/functions/shortcodes.php' );

if( is_admin() ){
	require_once( TEMPLATEPATH . '/functions/admin.php' );
}


//add_action( 'init', 'site_lockout' );
function site_lockout(){
	global $pagenow;
	
	if( !is_user_logged_in() && 'wp-login.php' != $pagenow && 'wp-register.php' != $pagenow ){
		nocache_headers();
		header("HTTP/1.1 302 Moved Temporarily");
		header('Location: ' . get_settings('siteurl') . '/wp-login.php?redirect_to=' .
		urlencode($_SERVER['REQUEST_URI']));
		header("Status: 302 Moved Temporarily");
		exit();
	}
}


/*=== IppoJuku
==============================================================================================*/
function the_leading_title(){
	global $post;
	
	$content = $post->post_excerpt;
	
	if( empty( $content ) )
		return;
	
	$content = strip_tags( $content );
	$content = trim( $content );
	$content = str_replace( array( "\r\n", "\r" ), "\n", $content );
	$content = preg_replace( '|\n+|u','<br />', $content );
	$content = preg_replace( '|\s+|u',' ', $content );
	echo $content;
}

class ippo_walker extends Walker_Nav_Menu {
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n<ul class=\"dropdown-pane\">\n";
	}
    function start_el(&$output, $item, $depth, $args) {
        global $wp_query;
        
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
        $class_names = ' class="'. esc_attr( $class_names ) . '"';

        $output .= $indent . '<li>';

        $attributes = '';
        if( $item->menu_item_parent == 0 ){
	        $attributes .= ' class="dropdown-tab"';
        }
        
        $attributes .= ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

        $description  = ! empty( $item->description ) ? '<br /><span>'.esc_attr( $item->description ).'</span>' : '';

        if($depth != 0) {
            $description = $append = $prepend = "";
        }
        
        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
        $item_output .= $description.$args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;
        
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
		$id_field = $this->db_fields['id'];
		if ( is_object( $args[0] ) ) {
			$args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
		}
		return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

}
?>