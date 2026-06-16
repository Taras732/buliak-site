<?php
/* Футер: прибрати таглайн-текст зліва + збільшити лого */
$f = '/var/www/html/wp-content/themes/buliak-astra/functions.php';
$c = file_get_contents( $f );
$c = preg_replace( '#<p>М.*?Львівщина\.</p>#us', '', $c, 1 );
$c = str_replace( 'max-height:46px', 'max-height:72px', $c );
file_put_contents( $f, $c );
WP_CLI::success( 'footer cleaned' );
