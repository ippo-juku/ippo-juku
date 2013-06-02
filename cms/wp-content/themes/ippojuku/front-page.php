<?php get_header(); ?>
<header id="docheader">
	<ul class="jcarousel-skin-ippo">
		<li><a href="/system/onestep"><img src="/lib/images/home/1.png" width="940" height="300" alt="なりたい自分になるために今日の一歩は何をする？" /></a></li>
		<li><a href="/about/strength"><img src="/lib/images/home/2.png" width="940" height="300" alt="勉強がニガテ、キライを根本から変えていきます" /></a></li>
		<li><a href="/system/#abs"><img src="/lib/images/home/3.png" width="940" height="300" alt="勉強ができないのは頭がわるいからじゃないんです" /></a></li>
		<li><a href="/system/#ronri-engine"><img src="/lib/images/home/4.png" width="940" height="300" alt="論理的思考力を養成する国語授業" /></a></li>	  
	</ul>
	<script>
		require( 'carousel', '!ready' ).done(function(){
			$( '#docheader > ul' ).jcarousel({
				start        : 0,
				scroll       : 1,
				auto         : 8,
				animation    : 900,
				easing       : 'easeInOutQuart',
				wrap         : 'circular',
				initCallback : function( c ){
					c.clip.hover(function(){
						c.stopAuto();
					}, function(){
						c.startAuto();
					});
				}
			});
		});
	</script>
</header>
<div id="container">
	<div id="content">
		<section class="rule-bottom clear-after">
<?php 
	crst::the_content( 3 );
?>

		</section>
		<section class="col-abc-a">
			<h3><a href="/news">お知らせ</a></h3>
			<ul class="article-list link-list">
<?php
	query_posts( 'post_type=news&posts_per_page=5&orderby=date' );
	if( have_posts() ):
		while( have_posts() ):
			the_post();
?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				<br /><time datetime="<?php crst::the_date( 'Y-m-d' ); ?>" class="note"><?php crst::the_date(); ?></time></li>
<?php
		endwhile;
	else:
?>
				<li>お知らせがありません。</li>
<?php
	endif;
	wp_reset_query();
?>
			</ul>
			<a href="/news" class="btn">お知らせ一覧</a>
		</section>
		<section class="col-abc-b">
			<h3><a href="/blog">塾長ブログ</a></h3>
			<ul class="article-list link-list">
<?php
	query_posts( 'posts_per_page=5&orderby=date' );
	if( have_posts() ):
		while( have_posts() ):
			the_post();
?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				<br /><time datetime="<?php crst::the_date( 'Y-m-d' ); ?>" class="note"><?php crst::the_date(); ?></time></li>
<?php
		endwhile;
	else:
?>
				<li>ブログ記事がありません。</li>
<?php
	endif;
	wp_reset_query();
?>
			</ul>
			<a href="/blog" class="btn">記事一覧</a>
		</section>
		<div class="col-abc-c">
<?php dynamic_sidebar( 'sidebar-home' ); ?>
		</div>
		<br class="clear" />
	<!--/ #content --></div>
<!--/ #container --></div>
<?php get_footer(); ?>