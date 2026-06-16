<?php
/* Правки каталогу зі скрінів Тараса 2026-06-16:
 * - draft 3 готових-смажених дублі (лишаються вакуум/гриль-версії)
 * - перейменування 4 товарів на бренд-назви
 * - вакуум-нота в опис BBQ, що лишаються */
if ( ! class_exists( 'WC_Product_Simple' ) ) { WP_CLI::error( 'WC off' ); }

function blk_p( $sku ) { $i = wc_get_product_id_by_sku( $sku ); return $i ? wc_get_product( $i ) : null; }

/* 1) Дублі готова-смажена -> draft (не видаляємо, оборотно) */
foreach ( array( 'BLK-03', 'BLK-04', 'BLK-06' ) as $sku ) {
	$p = blk_p( $sku ); if ( ! $p ) { WP_CLI::log( "skip $sku (нема)" ); continue; }
	$p->set_status( 'draft' );
	// зняти тег bestseller, щоб не лишився на головній
	wp_remove_object_terms( $p->get_id(), 'bestseller', 'product_tag' );
	$p->save();
	WP_CLI::log( "draft $sku ({$p->get_name()})" );
}

/* 2) Перейменування на бренд-назви */
$rename = array(
	'BLK-07' => 'Рулька BBQ вакуумована',
	'BLK-11' => 'Файні крила',
	'BLK-14' => 'Купати',
	'BLK-15' => 'Цибуляки',
);
foreach ( $rename as $sku => $name ) {
	$p = blk_p( $sku ); if ( ! $p ) { WP_CLI::log( "skip $sku (нема)" ); continue; }
	$p->set_name( $name ); $p->save();
	WP_CLI::log( "rename $sku -> $name" );
}

/* 3) Вакуум-нота: BBQ-товари, що лишаються опубліковані */
$note = '<p><em>Постачається охолодженою у вакуумі, не гарячою — удома достатньо розігріти.</em></p>';
foreach ( array( 'BLK-01', 'BLK-02', 'BLK-05' ) as $sku ) {
	$p = blk_p( $sku ); if ( ! $p ) continue;
	$d = $p->get_description();
	// нормалізувати стару фразу без "у вакуумі"
	$d = str_replace( 'Постачається охолодженою, не гарячою — перед подачею достатньо розігріти.', 'Постачається охолодженою у вакуумі, не гарячою — удома достатньо розігріти.', $d );
	if ( strpos( $d, 'у вакуумі' ) === false ) { $d = rtrim( $d ) . $note; }
	$p->set_description( $d ); $p->save();
	WP_CLI::log( "vacuum-note $sku" );
}

WP_CLI::success( 'Каталог оновлено (draft x3, rename x4, vacuum-note x3)' );
