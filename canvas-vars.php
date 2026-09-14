<?php
require_once dirname( __DIR__, 3 ) . '/wp-load.php';

header( 'Content-Type: text/css; charset=utf-8' );

if ( ! function_exists( 'wpdp_vars_css' ) ) {
	http_response_code( 404 );
	exit;
}

echo wpdp_vars_css( ':root' );
