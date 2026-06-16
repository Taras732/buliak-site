<?php
/* Замінити текстовий "◆ БУЛЯК" у футері на біле лого (custom_logo) */
$f = '/var/www/html/wp-content/themes/buliak-astra/functions.php';
$c = file_get_contents( $f );

$search = "\$tel = '073 111 76 70';";
$inject = "\$tel = '073 111 76 70';\n    \$blk_logo = wp_get_attachment_image_url( get_theme_mod('custom_logo'), 'full' );\n    \$blk_logo_html = \$blk_logo ? '<img src=\"' . esc_url(\$blk_logo) . '\" alt=\"Буляк\" style=\"max-height:46px;width:auto\">' : '◆ БУЛЯК';";
if ( strpos( $c, '$blk_logo_html' ) === false ) {
	$c = str_replace( $search, $inject, $c );
}
$c = preg_replace( '#<div class="fb">.*?</div>#us', '<div class="fb">\' . $blk_logo_html . \'</div>', $c, 1 );

file_put_contents( $f, $c );
WP_CLI::success( 'footer logo set' );
