<?php
/* Футер копірайт: права захищені + кредит Кузні з посиланням */
$f = '/var/www/html/wp-content/themes/buliak-astra/functions.php';
$c = file_get_contents( $f );
$new = 'БУЛЯК. Усі права захищені. · Сайт зроблено в <a href="https://forge-lab.ascendgriffin.org" target="_blank" rel="noopener" style="color:var(--gold,#e0b557)">Кузні</a>';
$c = preg_replace( '#БУЛЯК\. Усі смаки захищені\.#u', $new, $c, 1 );
file_put_contents( $f, $c );
WP_CLI::success( 'footer credit set' );
