<?php
/* Футер: додати рядок «Опт / гурт» у колонку Магазин */
$f = '/var/www/html/wp-content/themes/buliak-astra/functions.php';
$c = file_get_contents( $f );
if ( strpos( $c, 'Опт / гурт' ) === false ) {
	$c = str_replace(
		'Кошик</a>\'',
		'Кошик</a>\' . \'<a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Опт / гурт</a>\'',
		$c
	);
	file_put_contents( $f, $c );
	WP_CLI::success( 'footer wholesale added' );
} else {
	WP_CLI::success( 'already there' );
}
