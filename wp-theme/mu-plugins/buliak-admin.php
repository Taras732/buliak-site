<?php
/* Plugin Name: Буляк Admin
 * Description: Фільтр замовлень за датою (від/по) у списку Замовлень (HPOS). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---- Кастомні статуси замовлень під процес Буляк ---- */
function buliak_statuses() {
	return array(
		'wc-blk-new'       => 'Нове',
		'wc-blk-calling'   => 'Уточнення',
		'wc-blk-preparing' => 'Готується',
		'wc-blk-agreed'    => 'Узгоджено',
		'wc-blk-shipped'   => 'Відправлено',
	);
}
add_action( 'init', function () {
	foreach ( buliak_statuses() as $slug => $label ) {
		register_post_status( $slug, array(
			'label'                     => $label,
			'public'                    => false,
			'internal'                  => false,
			'show_in_admin_status_list' => true,
			'show_in_admin_all_list'    => true,
			'exclude_from_search'       => false,
			/* translators: %s order count */
			'label_count'               => _n_noop( $label . ' (%s)', $label . ' (%s)' ),
		) );
	}
} );
add_filter( 'wc_order_statuses', function ( $statuses ) {
	return array(
		'wc-blk-new'       => 'Нове',
		'wc-blk-calling'   => 'Уточнення',
		'wc-blk-preparing' => 'Готується',
		'wc-blk-agreed'    => 'Узгоджено',
		'wc-blk-shipped'   => 'Відправлено',
		'wc-completed'     => 'Виконано',
		'wc-cancelled'     => 'Скасовано',
		'wc-checkout-draft' => 'Чернетка',
	);
} );
/* кольори статусів у списку замовлень */
add_action( 'admin_head', function () {
	echo '<style>'
		. '.order-status.status-blk-new{background:#e0b557;color:#1d1917;}'
		. '.order-status.status-blk-calling{background:#f0a500;color:#1d1917;}'
		. '.order-status.status-blk-agreed{background:#7ab8ff;color:#0b2545;}'
		. '.order-status.status-blk-preparing{background:#c77dff;color:#fff;}'
		. '.order-status.status-blk-shipped{background:#5bc88a;color:#06351f;}'
		. '.order-status.status-checkout-draft{background:#6b6b6b;color:#fff;}'
		. '</style>';
} );

/* ---- Дозволити редагувати позиції замовлення в робочих статусах ---- */
add_filter( 'wc_order_is_editable', function ( $editable, $order ) {
	$work = array( 'blk-new', 'blk-calling', 'blk-preparing', 'blk-agreed' );
	if ( in_array( $order->get_status(), $work, true ) ) { return true; }
	return $editable;
}, 10, 2 );
add_filter( 'woocommerce_valid_order_statuses_for_edit', function ( $statuses ) {
	return array_values( array_unique( array_merge( $statuses, array( 'blk-new', 'blk-calling', 'blk-preparing', 'blk-agreed' ) ) ) );
} );

/* ---- Дробова кількість (кг) у редакторі замовлення: ширше поле + step ---- */
add_action( 'admin_head', function () {
	echo '<style>'
		. '.woocommerce_order_items input.quantity,'
		. '.woocommerce_order_items td.quantity input[type="number"]{'
		. 'width:78px!important;min-width:78px!important;max-width:78px!important;'
		. 'text-align:center;padding:4px 6px!important;font-size:14px!important;}'
		. '.woocommerce_order_items td.quantity{min-width:90px;}'
		. '</style>';
} );
add_action( 'admin_footer', function () {
	?>
<script>
(function(){
  if (!window.jQuery) { return; }
  jQuery(function($){
    var box = document.querySelector('.woocommerce_order_items_wrapper, #woocommerce-order-items');
    if (!box) { return; }
    function fix(){
      box.querySelectorAll('input.quantity, input[name^="order_item_qty"]').forEach(function(i){
        i.setAttribute('step','any'); i.setAttribute('min','0');
        i.style.width='78px'; i.style.minWidth='78px'; i.style.maxWidth='78px'; i.style.textAlign='center';
      });
    }
    fix();
    try { new MutationObserver(fix).observe(box, { childList:true, subtree:true }); } catch(e){}
  });
})();
</script>
	<?php
} );

/* Поля «від / по» у панелі фільтрів списку замовлень */
add_action( 'woocommerce_order_list_table_restrict_manage_orders', function () {
	$from = isset( $_GET['blk_from'] ) ? sanitize_text_field( wp_unslash( $_GET['blk_from'] ) ) : '';
	$to   = isset( $_GET['blk_to'] ) ? sanitize_text_field( wp_unslash( $_GET['blk_to'] ) ) : '';
	echo '<label style="margin:0 6px 0 2px">Дата з <input type="date" name="blk_from" value="' . esc_attr( $from ) . '"></label>';
	echo '<label style="margin:0 6px 0 2px">по <input type="date" name="blk_to" value="' . esc_attr( $to ) . '"></label>';
} );

/* Застосувати фільтр до запиту замовлень */
add_filter( 'woocommerce_order_list_table_prepare_items_query_args', function ( $args ) {
	$from = ! empty( $_GET['blk_from'] ) ? sanitize_text_field( wp_unslash( $_GET['blk_from'] ) ) : '';
	$to   = ! empty( $_GET['blk_to'] ) ? sanitize_text_field( wp_unslash( $_GET['blk_to'] ) ) : '';
	if ( $from && $to ) { $args['date_created'] = $from . '...' . $to; }
	elseif ( $from ) { $args['date_created'] = '>=' . $from; }
	elseif ( $to ) { $args['date_created'] = '<=' . $to; }
	return $args;
} );
