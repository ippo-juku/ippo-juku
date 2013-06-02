<?php get_header(); ?>
<div id="container">
	<div id="content">
<?php
	if( have_posts() ):
		while( have_posts() ):
			the_post();
?>
		<article class="post <?php crst::post_class(); ?>">
			<header class="post-header">
				<time class="post-time" datetime="<?php crst::the_date( 'Y-m-d' ); ?>"><span class="date"><?php crst::the_date( 'm/d' ); ?></span> <span class="year"><?php crst::the_date( 'Y' ); ?></span></time>
				<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			</header>
<?php 
			crst::the_content( 3 );
?>
		</article>
<?php
		endwhile;
		
		if( $wp_query->max_num_pages > 0 ):
?>
		<div id="page-navigation">
			<ul class="compact menu">
<?php
			echo code_indent( Pagenation::get(), 5 );
?>

			</ul>
		</div>
<?php
		endif;
	else: 
?>
		<p class="txt-big note">記事が見つかりませんでした</p>
<?php
	endif;
?>	
	<!--/ #content --></div>
<!--/ #container --></div>
<?php get_footer(); ?>