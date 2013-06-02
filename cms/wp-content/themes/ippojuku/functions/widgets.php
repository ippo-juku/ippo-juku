<?php
/*=== Widgets
==============================================================================================*/
function twentyeleven_widgets_init() {
	
	/*	Unregister Defaults
	-----------------------------------------------*/
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Meta' );
	unregister_widget( 'WP_Widget_RSS' );
	unregister_widget( 'WP_Widget_Tag_Cloud' );
	
	unregister_widget( 'WP_Widget_Pages' );
	unregister_widget( 'WP_Widget_Links' );
	unregister_widget( 'WP_Widget_Search' );
	unregister_widget( 'WP_Widget_Archives' );
	unregister_widget( 'WP_Widget_Text' );
	unregister_widget( 'WP_Widget_Categories' );
	unregister_widget( 'WP_Widget_Recent_Posts' );
	unregister_widget( 'WP_Widget_Recent_Comments' );
	unregister_widget( 'WP_Nav_Menu_Widget' );
	
	/*	Register Original
	-----------------------------------------------*/
	register_widget( 'CRST_Widget_Pages' );
	register_widget( 'CRST_Widget_Links' );
	register_widget( 'CRST_Widget_Search' );
	register_widget( 'CRST_Widget_Archives' );
	register_widget( 'CRST_Widget_Text' );
	register_widget( 'CRST_Widget_Categories' );
	register_widget( 'CRST_Widget_Recent_Posts' );
	register_widget( 'CRST_Widget_Recent_Comments' );
	register_widget( 'CRST_Nav_Menu_Widget' );
	
	register_widget( 'CRST_Widget_CTA_RSS' );
	register_widget( 'CRST_Widget_CTA_Twitter' );
	register_widget( 'CRST_Widget_Tweets' );
	register_widget( 'CRST_Widget_Random_Posts' );
	
	/*	Setup Sidebar Areas
	-----------------------------------------------*/
/*
	register_sidebar( array(
		'name' => 'ブログ',
		'id' => 'sidebar-blog',
		'before_widget' => '<aside class="%2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3>',
		'after_title' => '</h3>',
		'indent' => 3
	) );
*/
	
	register_sidebar( array(
		'name' => 'ホーム',
		'id' => 'sidebar-home',
		'description' => 'ホームでバーナーなどを表示します。',
		'before_widget' => '<aside class="%2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3>',
		'after_title' => '</h3>',
		'indent' => 3
	) );

}
add_action( 'widgets_init', 'twentyeleven_widgets_init' );


/*=== Caching Widget Example
==============================================================================================*/
class Twenty_Eleven_Ephemera_Widget extends WP_Widget {
	function Twenty_Eleven_Ephemera_Widget() {
		$widget_ops = array( 'classname' => 'widget_twentyeleven_ephemera', 'description' => __( 'Use this widget to list your recent Aside, Status, Quote, and Link posts', 'twentyeleven' ) );
		$this->WP_Widget( 'widget_twentyeleven_ephemera', __( 'Twenty Eleven Ephemera', 'twentyeleven' ), $widget_ops );
		$this->alt_option_name = 'widget_twentyeleven_ephemera';

		add_action( 'save_post', array(&$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache' ) );
	}

	function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_twentyeleven_ephemera', 'widget' );

		if ( !is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = null;

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}
		
		ob_start();
		extract( $args, EXTR_SKIP );
		
		// html
		
		$render = ob_get_flush();
		
		$cache[$args['widget_id']] = $render;
		wp_cache_set( 'widget_twentyeleven_ephemera', $cache, 'widget' );
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_twentyeleven_ephemera'] ) )
			delete_option( 'widget_twentyeleven_ephemera' );

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_twentyeleven_ephemera', 'widget' );
	}

	function form( $instance ) {
	}
}


/*=== CTA RSS
==============================================================================================*/
class CRST_Widget_CTA_RSS extends WP_Widget{
	function __construct(){
		$widget_ops = array(
			'description' => ''
		);
		parent::__construct( 'rss', __( 'RSSボタン' ), $widget_ops);
	}

	function widget( $args, $instance ){
		$title = $instance[ 'title' ] ? $instance[ 'title' ] : 'RSSを購読する';
		$title = esc_html( $title );
		
		$link = get_bloginfo( 'rss2_url' );
		
		$render = <<<HTML
<aside>
	<a href="{$link}" rel="alternate" class="cta rss">
		<h3 class="icon icon-big icon-rss">{$title}</h3>
	</a>
</aside>

HTML;
		
		$render = code_indent( $render, $args['indent'] );
		
		echo $render;
	}
	function form( $instance ) {
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : 'RSSを購読する';
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<?php
	}
}


/*=== CTA Twitter
==============================================================================================*/
class CRST_Widget_CTA_Twitter extends WP_Widget{
	function __construct(){
		$widget_ops = array(
			'description' => ''
		);
		parent::__construct( 'twitter', __( 'Twitterボタン' ), $widget_ops);
	}

	function widget( $args, $instance ){
		$title = $instance[ 'title' ] ? $instance[ 'title' ] : 'フォローする';
		$title = esc_html( $title );
		
		$twitter = get_option( 'twitter' );
		
		if( !$twitter )
			return;
		
		$link = 'http://twitter.com/' . $twitter;
		
		$render = <<<HTML
<aside>
	<a href="{$link}" class="cta twitter">
		<h3 class="icon icon-big icon-twitter">{$title}</h3>
	</a>
</aside>

HTML;
		
		$render = code_indent( $render, $args['indent'] );
		
		echo $render;
	}
	function form( $instance ) {
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : 'フォローする';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<?php
	}
}


/*=== Tweets
==============================================================================================*/
class CRST_Widget_Tweets extends WP_Widget{
	function __construct(){
		$widget_ops = array(
			'description' => ''
		);
		parent::__construct( 'tweets', __( 'ツイート' ), $widget_ops);
	}

	function widget( $args, $instance ){
		$title = $instance[ 'title' ] ? $instance[ 'title' ] : 'Twitter';
		$title = esc_html( $title );
		
		$twitter = get_option( 'twitter' );
		
		if( !$twitter )
			return;
		
		$link = 'http://twitter.com/' . $twitter;
		$link = esc_url( $link );
		
		$twitter = preg_quote( $twitter );
		
		$render = <<<HTML
<aside>
	<h3 class="icon icon-twitter">{$title} <a href="{$link}" class="more label">フォロー</a></h3>
	<ul id="twitter_status"></ul>
	<script>
		require( 'module.twitter' ).done(function(){
			$( '#twitter_status' ).tweet({
				username: '{$twitter}',
				count: 5
			});
		});
	</script>
</aside>

HTML;
		
		$render = code_indent( $render, $args['indent'] );

		echo $render;
	}
	function form( $instance ) {
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : 'Twitter';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<?php
	}
}


/*=== ランダム
==============================================================================================*/
class CRST_Widget_Random_Posts extends WP_Widget{
	function __construct(){
		$widget_ops = array(
			'description' => ''
		);
		parent::__construct( 'randomposts', __( 'ランダム' ), $widget_ops);
	}

	function widget( $args, $instance ){
		$title = $instance[ 'title' ] ? $instance[ 'title' ] : 'ランダム';
		$title = esc_html( $title );
		
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
 			$number = 5;
 		
		$r = new WP_Query( array( 
			'posts_per_page' => $number, 
			'no_found_rows' => true, 
			'post_status' => 'publish', 
			'ignore_sticky_posts' => true,
			'orderby' => 'rand'
		) );
		
		$render = '';
		if( $r->have_posts() ):
			$render .= "<aside>\n";
			
			$render .= "\t<h3>$title</h3>\n"; 
			
			$render .= "\t<ul>\n";
			
			while( $r->have_posts() ):
				$r->the_post();
				$permalink = get_permalink( $post );
				$title = get_the_title();
				$render .= "\t\t<li><a href=\"$permalink\">$title</a></li>\n";
			endwhile;
			
			$render .= "\t</ul>\n";
			
			$render .= "</aside>\n";
			
			wp_reset_postdata();
		
		endif;
		
		$render = code_indent( $render, $args['indent'] );

		echo $render;
	}
	function form( $instance ) {
		$title = isset( $instance['title']) ? esc_attr( $instance['title'] ) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		
		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<?php
	}
}


/*=== Default: Pages
==============================================================================================*/
class CRST_Widget_Pages extends WP_Widget_Pages{
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $instance['title'], $instance, $this->id_base);
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];

		if ( $sortby == 'menu_order' )
			$sortby = 'menu_order, post_title';

		$out = wp_list_pages( apply_filters('widget_pages_args', array('title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude) ) );

		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title)
				echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
		}
	}
}


/*=== Default: Links
==============================================================================================*/
class CRST_Widget_Links extends WP_Widget_Links{
	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);

		$show_description = isset($instance['description']) ? $instance['description'] : false;
		$show_name = isset($instance['name']) ? $instance['name'] : false;
		$show_rating = isset($instance['rating']) ? $instance['rating'] : false;
		$show_images = isset($instance['images']) ? $instance['images'] : true;
		$category = isset($instance['category']) ? $instance['category'] : false;
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'name';
		$order = $orderby == 'rating' ? 'DESC' : 'ASC';
		$limit = isset( $instance['limit'] ) ? $instance['limit'] : -1;

		$before_widget = preg_replace('/id="[^"]*"/','id="%id"', $before_widget);
		crstUtil::wp_list_bookmarks(apply_filters('widget_links_args', array(
			'title_before' => $before_title, 'title_after' => $after_title,
			'category_before' => $before_widget, 'category_after' => $after_widget,
			'show_images' => $show_images, 'show_description' => $show_description,
			'show_name' => $show_name, 'show_rating' => $show_rating,
			'category' => $category, 'class' => 'linkcat widget',
			'orderby' => $orderby, 'order' => $order,
			'limit' => $limit,
			'indent' => $indent
		)));
	}
}


/*=== Default: Search
==============================================================================================*/
class CRST_Widget_Search extends WP_Widget_Search{
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		// Use current theme search form if it exists
		get_search_form();

		echo $after_widget;
	}
}


/*=== Default: Archives
==============================================================================================*/
class CRST_Widget_Archives extends WP_Widget_Archives{
	function widget( $args, $instance ) {
		extract($args);
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Archives') : $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		if ( $d ) {
?>
		<select name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> <option value=""><?php echo esc_attr(__('Select Month')); ?></option> <?php wp_get_archives(apply_filters('widget_archives_dropdown_args', array('type' => 'monthly', 'format' => 'option', 'show_post_count' => $c))); ?> </select>
<?php
		} else {
?>
		<ul>
		<?php wp_get_archives(apply_filters('widget_archives_args', array('type' => 'monthly', 'show_post_count' => $c))); ?>
		</ul>
<?php
		}

		echo $after_widget;
	}
}


/*=== Default: Text
==============================================================================================*/
class CRST_Widget_Text extends WP_Widget_Text{
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
		
		$render = '';
		
		$render .= $before_widget;
		if( !empty( $title ) )
			$render .= $before_title . $title . $after_title;
		
		$render .= '<div class="textwidget">'  . $text . '</div>';
		
		$render .= $after_widget;
		
		$render = apply_filters( 'enhanced_autop', $render, $indent );
		
		echo $render;
	}
}


/*=== Default: Categories
==============================================================================================*/
class CRST_Widget_Categories extends WP_Widget_Categories{
	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Categories' ) : $instance['title'], $instance, $this->id_base);
		$render = '';
		
		$render .= $before_widget . NL;
		if ( $title )
			$render .= "\t<h3>$title</h3>\n";
			
		$render .= "\t<ul>\n";
		foreach( get_categories() as $cat ){
			$link = get_category_link( $cat->term_id );
			$name = esc_html( $cat->name );
			$render .= "\t\t<li><a href=\"$link\">$name</a></li>\n";
		}
		
		$render .= "\t</ul>\n";
		$render .= $after_widget . NL;
		
		$render = code_indent( $render, $indent );
		
		echo $render;
	}
	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

<?php
	}

}


/*=== Default: 
==============================================================================================*/
class CRST_Widget_Recent_Posts extends WP_Widget_Recent_Posts{
	function widget($args, $instance) {

		$cache = wp_cache_get('widget_recent_posts', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {var_dump('dsagadgad');
			echo code_indent( $cache[ $args['widget_id'] ], 2 );
			return;
		}
		
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title'], $instance, $this->id_base);
		
		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
 			$number = 10;
 		
		$r = new WP_Query( 
			apply_filters( 
				'widget_posts_args', 
				array( 
					'posts_per_page' => $number, 
					'no_found_rows' => true, 
					'post_status' => 'publish', 
					'ignore_sticky_posts' => true 
				)
			)
		);
		
		$render = '';
		if( $r->have_posts() ):
			$render .= $before_widget . NL;
			
			if ( $title )
				$render .= "\t<h3>$title</h3>\n"; 
			
			$render .= "\t<ul>\n";
			
			while( $r->have_posts() ):
				$r->the_post();
				$permalink = get_permalink( $post );
				$title = get_the_title();
				$render .= "\t\t<li><a href=\"$permalink\">$title</a></li>\n";
			endwhile;
			
			$render .= "\t</ul>\n";
			
			$render .= $after_widget . NL;
			
			wp_reset_postdata();
		
		endif;
		
		$render = code_indent( $render, $indent );
		
		echo $render;
		
		$cache[$args['widget_id']] = $render;
		wp_cache_set('widget_recent_posts', $cache, 'widget');
	}
}


/*=== Default: 
==============================================================================================*/
class CRST_Widget_Recent_Comments extends WP_Widget_Recent_Comments{
	function widget( $args, $instance ) {
		global $comments, $comment;

		$cache = wp_cache_get('widget_recent_comments', 'widget');

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( ! isset( $args['widget_id'] ) )
			$args['widget_id'] = $this->id;

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

 		extract($args, EXTR_SKIP);
 		$output = '';
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Comments' ) : $instance['title'], $instance, $this->id_base );

		if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
 			$number = 5;

		$comments = get_comments( apply_filters( 'widget_comments_args', array( 'number' => $number, 'status' => 'approve', 'post_status' => 'publish' ) ) );
		$output .= $before_widget;
		if ( $title )
			$output .= $before_title . $title . $after_title;

		$output .= '<ul id="recentcomments">';
		if ( $comments ) {
			foreach ( (array) $comments as $comment) {
				$output .=  '<li class="recentcomments">' . /* translators: comments widget: 1: comment author, 2: post link */ sprintf(_x('%1$s on %2$s', 'widgets'), get_comment_author_link(), '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
			}
 		}
		$output .= '</ul>';
		$output .= $after_widget;

		echo $output;
		$cache[$args['widget_id']] = $output;
		wp_cache_set('widget_recent_comments', $cache, 'widget');
	}
}


/*=== Default: 
==============================================================================================*/
class CRST_Nav_Menu_Widget extends WP_Nav_Menu_Widget{
	function widget($args, $instance) {
		// Get menu
		$nav_menu = ! empty( $instance['nav_menu'] ) ? wp_get_nav_menu_object( $instance['nav_menu'] ) : false;

		if ( !$nav_menu )
			return;

		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$render = '';
		
		$render .= $args['before_widget'];

		if ( !empty($instance['title']) )
			$render .= $args['before_title'] . $instance['title'] . $args['after_title'];
		
		$menu = wp_nav_menu( array(
			'menu' => $nav_menu,
			'container' => false,
			'walker' => new CRST_Walker_Nav_Menu(),
			'echo' => false,
			'menu_id' => '@delete@',
			'menu_class' => '@delete@'
		) );
		
		$render .= preg_replace( '!\s(id|class)="@delete@"!', '', $menu );
		
		$render .= $args['after_widget'];
		
		$render = apply_filters( 'enhanced_autop', "<wp_noautop>$render</wp_noautop>", $args['indent'] );
		echo $render;

	}
}
class CRST_Walker_Nav_Menu extends Walker_Nav_Menu {
    function start_el(&$output, $item, $depth, $args) {
        global $wp_query;
        
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;

        $output .= $indent . '<li>';

        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
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
}

?>