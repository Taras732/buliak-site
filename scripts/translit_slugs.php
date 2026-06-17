<?php
/* Транслітерація slug товарів + категорій (кирилиця -> латиниця) з поточних НАЗВ.
 * Заодно лагодить розбіжність slug↔назва (напр. «шашлик-свинний» -> «shashlyk-domashniy»).
 * Старі URL ловить 301-guard у buliak-shop.php. Запуск: wp eval-file - (stdin). */
if ( ! function_exists( 'wc_get_products' ) ) { WP_CLI::error( 'WC off' ); }

function blk_translit( $s ) {
	$s = function_exists( 'mb_strtolower' ) ? mb_strtolower( $s ) : strtolower( $s );
	$map = array(
		'а'=>'a','б'=>'b','в'=>'v','г'=>'h','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ie','ж'=>'zh',
		'з'=>'z','и'=>'y','і'=>'i','ї'=>'i','й'=>'i','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
		'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch',
		'ш'=>'sh','щ'=>'shch','ь'=>'','ю'=>'iu','я'=>'ia','’'=>'',"'"=>'','`'=>'','«'=>'','»'=>'',
	);
	$s = strtr( $s, $map );
	return sanitize_title( $s );
}

$n = 0;
foreach ( wc_get_products( array( 'limit' => -1, 'status' => array( 'publish', 'draft' ) ) ) as $p ) {
	$new = blk_translit( $p->get_name() );
	if ( ! $new ) { continue; }
	if ( $p->get_slug() !== $new ) {
		wp_update_post( array( 'ID' => $p->get_id(), 'post_name' => $new ) );
		WP_CLI::log( 'product ' . $p->get_sku() . ': ' . $p->get_slug() . ' -> ' . $new );
		$n++;
	}
}
foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) as $t ) {
	$new = blk_translit( $t->name );
	if ( ! $new || $t->slug === $new ) { continue; }
	wp_update_term( $t->term_id, 'product_cat', array( 'slug' => $new ) );
	WP_CLI::log( 'cat ' . $t->name . ': ' . $t->slug . ' -> ' . $new );
	$n++;
}
WP_CLI::success( "Транслітеровано slug: $n" );
