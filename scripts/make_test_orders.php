<?php
/* 2 тест-замовлення для демо панелі. _buliak_tg_sent=1 щоб НЕ слати Telegram. */
if ( ! function_exists( 'wc_create_order' ) ) { WP_CLI::error( 'WC off' ); }
$prods = wc_get_products( array( 'limit' => 6, 'status' => 'publish' ) );
if ( count( $prods ) < 2 ) { WP_CLI::error( 'мало товарів' ); }
$rows = array(
	array( 'Тестова', 'Ірина', '0671112233', 'Telegram', 'Львів', 'Відділення №12', 'processing' ),
	array( 'Тестовий', 'Олег', '0509998877', 'Viber', 'Київ', 'Поштомат №4521', 'completed' ),
);
foreach ( $rows as $i => $d ) {
	$o = wc_create_order();
	$o->add_product( $prods[ ( $i * 2 ) % count( $prods ) ], 1 );
	$o->add_product( $prods[ ( $i * 2 + 1 ) % count( $prods ) ], 0.5 );
	$o->set_billing_last_name( $d[0] ); $o->set_billing_first_name( $d[1] );
	$o->set_billing_phone( $d[2] );
	$o->update_meta_data( '_billing_messenger', $d[3] );
	$o->update_meta_data( '_billing_np_city', $d[4] );
	$o->update_meta_data( '_billing_np_branch', $d[5] );
	$o->update_meta_data( '_buliak_tg_sent', 1 );
	$o->calculate_totals();
	$o->set_status( $d[6] );
	$o->save();
	WP_CLI::log( 'order #' . $o->get_order_number() . ' (' . $d[6] . ')' );
}
WP_CLI::success( 'test orders' );
