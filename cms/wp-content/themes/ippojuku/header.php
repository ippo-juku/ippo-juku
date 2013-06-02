<!doctype html>
<html lang="ja" prefix="og: http://ogp.me/ns# fb: http://www.facebook.com/2008/fbml">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=940" />
	<title><?php tpl_param( 'pagetitle', '' ); ?></title>
	<link rel="alternate" href="<?php bloginfo( 'rss2_url' ); ?>" type="application/rss+xml" title="一歩塾 塾長ブログ" />
	<link rel="stylesheet" href="/lib/css/master.css?ver=" />
	<script src="/lib/scripts.js?ver=20120820"></script>
	<script src="/lib/ippo.js?ver=20120820"></script>
	<script>require(<?php tpl_param( 'modules__out', '' ); ?>);</script>
	<script>
		var _gaq = _gaq || [];
		_gaq.push([ '_setAccount', 'UA-15676697-1' ]);
		_gaq.push([ '_setDomainName', 'ippo-juku.com' ]);
		_gaq.push([ '_trackPageview' ]);
		
		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
<?php 
	wp_head();
?>
</head>
<body class="<?php crst::body_class(); ?>">
<header id="globalheader">
	<div class="inner">
		<h1 id="ippojuku-logo"><a href="/" title="ホーム">一歩塾</a></h1>
		<nav>
<?php
	$menu = wp_nav_menu( array(
		'menu' => 'global-header',
		'container' => false,
		'menu_id' => 'gnav-site-menu',
		'menu_class' => 'menu compact',
		'walker' => new ippo_walker(),
		'echo' => false
	) );
	
	echo apply_filters( 'enhanced_autop', "<wp_noautop>$menu</wp_noautop>", 3 );
?>
		</nav>
		<a href="<?php echo esc_url( home_url( '/about/contact' ) ); ?>" id="head-banner">
			しっかりと理解してほしいから
			<br />2週間無料体験
		</a>
	</div>
</header>
<?php
	if( !is_front_page() ):
?>
<header id="docheader">
	<ol id="breadcrumb" class="menu compact">
<?php
		foreach( Breadcrumb::get_array() as $bc ):
?>
		<li><a href="<?php echo relative_url( $bc['link'] ); ?>"><?php echo esc_html( $bc['title'] ); ?></a></li>
<?php
		endforeach;
?>
	</ol>
<?php 
		if( is_page() && has_excerpt() ):
?>
	<hgroup>
		<h2><?php tpl_param( 'doctitle', '' ); ?></h2>
		<h3><?php the_leading_title(); ?></h3>
	</hgroup>
<?php 
		else:
?>
	<h2><?php tpl_param( 'doctitle', '' ); ?></h2>
<?php
		endif;
?>
</header>
<?php
	endif;
?>
