<?php
/* Прибрати рядок "Ціна: X ₴/кг" з коротких описів (ціна показується окремо з суфіксом /кг) */
if ( ! class_exists( 'WC_Product_Simple' ) ) { WP_CLI::error( 'WC off' ); }
$ids = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'publish' ) );
$n = 0;
foreach ( $ids as $id ) {
	$p = wc_get_product( $id );
	$s = $p->get_short_description();
	$new = preg_replace( '/<strong>Ціна:<\/strong>[^<]*<br>\s*/u', '', $s );
	if ( $new !== $s ) { $p->set_short_description( $new ); $p->save(); $n++; }
}
WP_CLI::success( "Прибрано рядок ціни у $n товарів" );
