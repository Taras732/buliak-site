<?php
/* Буляк — чистка назв категорій і товарів + прибрати "готове до споживання" з описів (2026-06-15) */
if ( ! class_exists( 'WC_Product_Simple' ) ) { WP_CLI::error( 'WC off' ); }

/* 1. Категорії */
$cat = array(
	'Готова смажена BBQ'            => 'BBQ',
	'Шашлики та для гриля'          => 'На вогні',
	'Домашні ковбаси та копченості' => 'Ковбаси та копченості',
);
foreach ( $cat as $old => $new ) {
	$t = get_term_by( 'name', $old, 'product_cat' );
	if ( $t ) { wp_update_term( $t->term_id, 'product_cat', array( 'name' => $new ) ); WP_CLI::log( "cat: $old -> $new" ); }
}

/* 2. Назви товарів */
$names = array(
	'BLK-01' => 'Шия BBQ',
	'BLK-02' => 'Ребро BBQ',
	'BLK-03' => 'Рулька BBQ',
	'BLK-04' => 'Полоска свиняча BBQ',
	'BLK-05' => 'Ковбаска «Бабусина» BBQ',
	'BLK-06' => 'Ковбаска «Бабусина» BBQ з сиром',
	'BLK-07' => 'Рулька вакуумована',
	'BLK-16' => 'Ковбаска «Бабусина»',
	'BLK-17' => 'Ковбаска «Бабусина» з сиром Гауда',
	'BLK-18' => 'Ковбаска мелена',
	'BLK-22' => 'Ковбаска куряча',
	'BLK-23' => 'Полядвиця печена',
);

/* 3. + чистка описів усіх BLK */
$all = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'publish' ) );
foreach ( $all as $id ) {
	$p = wc_get_product( $id );
	$sku = $p->get_sku();
	if ( isset( $names[ $sku ] ) ) { $p->set_name( $names[ $sku ] ); }

	$d = $p->get_description();
	// прибрати доданий "охолоджена" note (виносимо з картки)
	$d = str_replace( '<p><em>Постачається охолодженою, не гарячою — перед подачею достатньо розігріти.</em></p>', '', $d );
	// прибрати "(повністю) готова/готове до споживання" з опт. ", " або " та" попереду
	$d = preg_replace( '/(,|\s+та)?\s*(повністю\s+)?готов[аіе]\s+до\s+споживання/iu', '', $d );
	$d = str_replace( 'Можна одразу споживати без додаткового приготування.', '', $d );
	// нормалізація пунктуації
	$d = preg_replace( '/\s+([.,])/u', '$1', $d );
	$d = str_replace( array( '..', ' .', ' ,' ), array( '.', '.', ',' ), $d );
	$d = preg_replace( '/\s{2,}/u', ' ', $d );
	$p->set_description( trim( $d ) );

	$p->save();
	if ( isset( $names[ $sku ] ) ) { WP_CLI::log( "name: $sku -> {$names[$sku]}" ); }
}
WP_CLI::success( 'Назви й описи почищено' );
