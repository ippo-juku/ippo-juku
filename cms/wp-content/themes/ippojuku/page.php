<?php get_header(); ?>
<div id="container">
	<div id="content">
<?php
	if( has_post_thumbnail() ):
?>
		<div id="hero">
			<?php the_post_thumbnail( 'hero-image' ); ?>

		</div>
<?php
	endif;
	
	crst::the_content( 2 );
?>

	<!--/ #content --></div>
<!--/ #container --></div>
<?php get_footer(); ?>