<?php 
header( 'Content-type: text/css' );

define( 'PATH', '../../../../../lib/css/' );

?>
@charset "UTF-8";

/** 
 * Reset CSS
 * 
 * @author ykiwng
 */
<?php readfile( PATH . 'resetcss.min.css' ); ?>

<?php
	readfile( PATH . 'master/typography.css' );
	readfile( PATH . 'master/elements.css' );
	readfile( PATH . 'master/columns.css' );
	readfile( PATH . 'master/images.css' );


	readfile( 'editor.css' );

?>