<?php get_header(); ?>
<div id="container">
	<div id="content" class="<?php crst::post_class( 'post' ); ?>">
<?php
	if( have_posts() ):
		while( have_posts() ):
			the_post();
?>
<?php
			crst::the_content( 3 );
?>

		<footer class="post-footer">
			<ul class="menu compact">
				<li class="time">投稿日: <time datetime="<?php crst::the_date( 'Y-m-d' ); ?>"><?php crst::the_date(); ?></time></li>
				<li class="permalink"><a href="<?php the_permalink(); ?>">パーマリンク</a></li>
			</ul>
		</footer>
<?php
		endwhile;
	else: 
?>
		<p class="txt-big note">記事が見つかりませんでした</p>
<?php
	endif;
?>
	<!--/ #content --></div>
<!--/ #container --></div>
<?php get_footer(); ?>