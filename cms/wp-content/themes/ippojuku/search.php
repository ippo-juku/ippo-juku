<?php get_header(); ?>
<div id="container">
	<div id="content">
		<aside id="searchform">
			<form class="form-inline" action="">
				<input type="text" name="s" value="<?php echo esc_html( get_query_var( 's' ) ); ?>" class="text span-8" />
				<select name="post_type" class="span-2">
					<option value="post"<?php _selected( $_query_post_type, 'post' ); ?>>ブログ</option>
<?php
	$all_post_type = get_post_types( array(
		'public' => true,
		'_builtin' => false	
	) );
	
	foreach( $all_post_type as $post_type ):
		$pto = get_post_type_object( $post_type );
?>
					<option value="<?php echo $post_type; ?>"<?php _selected( $_query_post_type, $post_type ); ?>><?php echo esc_html( $pto->labels->name ); ?></option>
<?php
	endforeach;
?>
					<option value="page"<?php _selected( $_query_post_type, 'page' ); ?>>一般ページ</option>
				</select>
				<button class="btn" type="submit">Go!</button>
			</form>
		</aside>
		
<?php
	if( have_posts() ):
?>
		<p class="lead">全<?php echo $wp_query->found_posts; ?>件</p>
<?php
		while( have_posts() ):
			the_post();
?>
		<article class="<?php crst::post_class(); ?>">
			<hr />
			<div class="span-10">
				<header class="post-header">
					<h3 class="no-margin"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
					<p class="txt-small note"><small><?php the_permalink(); ?></small></p>
				</header>
				<p><?php crstSearch::the_content(); ?></p>
			</div>
			<div class="span-2 txt-center">
<?php
			if( has_post_thumbnail( $post->ID ) ):
?>
				<a href="<?php the_permalink(); ?>" class="block thumb scale70-lt480"><?php 
					the_post_thumbnail( 'post-thumbnail-small' ); 
				?></a>
<?php
			endif;
?>
			</div>
			<br class="clear" />
		</article>
<?php
		endwhile;
?>
<?php
		if( $wp_query->max_num_pages > 0 ):
?>
		<div id="page-navigation">
			<ul class="compact menu">
<?php
			echo code_indent( Pagenation::get(), 4 );
?>
			</ul>
		<!--/ #page-navigation --></div>
<?php
		endif;
	elseif( !get_query_var( 'is_search_box' ) ): 
?>
		<p class="lead txt-center">記事が見つかりませんでした<br />ほかの言葉で検索すると見つかるかもしれません</p>
<?php
	endif;
?>
	<!--/ #content --></div>
<!--/ #container --></div>
<?php get_footer(); ?>