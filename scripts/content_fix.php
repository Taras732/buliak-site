<?php
/* Контент-правки з дзвінка 2026-06-15: Шашлик домашній + BBQ охолоджене */
if ( ! class_exists( 'WC_Product_Simple' ) ) { WP_CLI::error( 'WC off' ); }

// Шашлик свинний -> домашній
$id = wc_get_product_id_by_sku( 'BLK-08' );
if ( $id ) { $p = wc_get_product( $id ); $p->set_name( 'Шашлик домашній' ); $p->save(); WP_CLI::log( 'BLK-08 -> Шашлик домашній' ); }

// BBQ готова продукція постачається ОХОЛОДЖЕНОЮ, не гарячою
$note = '<p><em>Постачається охолодженою, не гарячою — перед подачею достатньо розігріти.</em></p>';
foreach ( array( 'BLK-01','BLK-02','BLK-03','BLK-04','BLK-05','BLK-06' ) as $sku ) {
	$i = wc_get_product_id_by_sku( $sku ); if ( ! $i ) continue;
	$p = wc_get_product( $i ); $d = $p->get_description();
	// прибрати стару фразу "Можна одразу споживати без додаткового приготування"
	$d = str_replace( 'Можна одразу споживати без додаткового приготування.', '', $d );
	if ( strpos( $d, 'охолодженою' ) === false ) { $p->set_description( rtrim( $d ) . $note ); }
	else { $p->set_description( $d ); }
	$p->save(); WP_CLI::log( "chilled $sku" );
}
WP_CLI::success( 'Контент оновлено' );
