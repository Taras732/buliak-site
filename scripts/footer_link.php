<?php
/* Футер-лого -> клікабельне посилання на головну */
$f = '/var/www/html/wp-content/themes/buliak-astra/functions.php';
$c = file_get_contents( $f );
$new = "\$blk_logo_html = '<a href=\"' . esc_url(home_url('/')) . '\" class=\"footer-logo-link\">' . ( \$blk_logo ? '<img src=\"' . esc_url(\$blk_logo) . '\" alt=\"Буляк\" style=\"max-height:72px;width:auto\">' : '◆ БУЛЯК' ) . '</a>';";
$c = preg_replace( '#\$blk_logo_html = .*$#m', $new, $c, 1 );
file_put_contents( $f, $c );
WP_CLI::success( 'footer logo link set' );
