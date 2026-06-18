<?php
/* Plugin Name: Буляк Панель (спрощена адмінка — прототип)
 * Description: Чисте єдине меню для менеджерів поверх WooCommerce. Прототип: екран Замовлення. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* меню — нагорі, бачать менеджери (edit_shop_orders) */
add_action( 'admin_menu', function () {
	add_menu_page( 'Панель БУЛЯК', '🥩 Панель', 'edit_shop_orders', 'buliak-panel', 'blk_panel_render', 'dashicons-store', 2 );
}, 1 );

/* AJAX: зміна статусу */
add_action( 'wp_ajax_blk_panel_status', function () {
	if ( ! current_user_can( 'edit_shop_orders' ) ) { wp_send_json_error(); }
	$id     = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
	$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
	$order  = $id ? wc_get_order( $id ) : false;
	if ( ! $order || ! $status ) { wp_send_json_error(); }
	$order->update_status( $status, 'Панель: зміна статусу. ' );
	wp_send_json_success( array( 'label' => wc_get_order_status_name( $status ), 'slug' => $status ) );
} );

function blk_panel_render() {
	if ( ! function_exists( 'wc_get_orders' ) ) { echo '<div class="wrap"><p>WooCommerce вимкнено.</p></div>'; return; }
	$statuses = wc_get_order_statuses();                       // [wc-xxx => Назва]
	$orders   = wc_get_orders( array( 'limit' => 40, 'orderby' => 'date', 'order' => 'DESC' ) );
	$nonce    = wp_create_nonce( 'blk_panel' );
	?>
	<div class="blk-panel">
		<div class="blk-p-head">
			<h1>🥩 Панель БУЛЯК</h1>
			<nav class="blk-p-tabs">
				<a class="blk-p-tab is-active" href="#">Замовлення <span><?php echo count( $orders ); ?></span></a>
				<a class="blk-p-tab is-soon" href="#">Товари <em>скоро</em></a>
				<a class="blk-p-tab is-soon" href="#">Клієнти <em>скоро</em></a>
			</nav>
		</div>

		<?php if ( empty( $orders ) ) : ?>
			<div class="blk-p-empty">Замовлень поки немає.</div>
		<?php else : ?>
		<div class="blk-p-orders">
			<?php foreach ( $orders as $o ) :
				$id    = $o->get_id();
				$num   = $o->get_order_number();
				$date  = $o->get_date_created() ? $o->get_date_created()->date_i18n( 'd.m.Y · H:i' ) : '';
				$st    = 'wc-' . $o->get_status();
				$name  = trim( $o->get_billing_last_name() . ' ' . $o->get_billing_first_name() );
				$phone = $o->get_billing_phone();
				$pclean = preg_replace( '/[^0-9]/', '', $phone );
				$msg   = $o->get_meta( '_billing_messenger' );
				$city  = $o->get_meta( '_billing_np_city' );
				$branch= $o->get_meta( '_billing_np_branch' );
				$np    = trim( $city . ( $branch ? ', ' . $branch : '' ) );
			?>
			<div class="blk-p-card" data-id="<?php echo esc_attr( $id ); ?>">
				<div class="blk-p-c-top">
					<span class="blk-p-num">№<?php echo esc_html( $num ); ?></span>
					<span class="blk-p-date"><?php echo esc_html( $date ); ?></span>
					<span class="blk-p-badge blk-st-<?php echo esc_attr( $o->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $o->get_status() ) ); ?></span>
				</div>
				<div class="blk-p-cust">
					<strong><?php echo esc_html( $name ? $name : 'Без імені' ); ?></strong>
					<?php if ( $phone ) : ?><span class="blk-p-phone"><?php echo esc_html( $phone ); ?></span><?php endif; ?>
					<?php if ( $msg ) : ?><span class="blk-p-msg"><?php echo esc_html( $msg ); ?></span><?php endif; ?>
				</div>
				<?php if ( $pclean ) : ?>
				<div class="blk-p-contact">
					<a href="tel:<?php echo esc_attr( $phone ); ?>" class="blk-p-btn blk-p-call">📞 Подзвонити</a>
					<a href="https://t.me/+38<?php echo esc_attr( ltrim( $pclean, '0' ) === $pclean ? $pclean : '38' . substr( $pclean, 1 ) ); ?>" target="_blank" rel="noopener" class="blk-p-btn blk-p-tg">Telegram</a>
					<a href="viber://chat?number=%2B38<?php echo esc_attr( substr( $pclean, -9 ) ); ?>" class="blk-p-btn blk-p-vb">Viber</a>
				</div>
				<?php endif; ?>
				<?php if ( $np ) : ?><div class="blk-p-np">🚚 <?php echo esc_html( $np ); ?></div><?php endif; ?>
				<ul class="blk-p-items">
					<?php foreach ( $o->get_items() as $item ) : ?>
						<li><span><?php echo esc_html( $item->get_name() ); ?></span><span class="blk-p-qty"><?php echo esc_html( $item->get_quantity() ); ?> кг</span><span class="blk-p-sum"><?php echo wp_kses_post( wc_price( $item->get_total(), array( 'decimals' => 0 ) ) ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<div class="blk-p-foot">
					<div class="blk-p-total">Орієнтовно: <strong><?php echo wp_kses_post( wc_price( $o->get_total(), array( 'decimals' => 0 ) ) ); ?></strong></div>
					<div class="blk-p-status">
						<select class="blk-p-st-sel">
							<?php foreach ( $statuses as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $st ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>

	<style>
		.blk-panel { --g:#b8860b; --gl:#E0B557; --p:#B81F33; max-width:1180px; }
		#wpcontent { padding-left:0; }
		.blk-p-head { display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; margin:18px 20px 0; }
		.blk-p-head h1 { font-size:1.7rem; margin:0; }
		.blk-p-tabs { display:flex; gap:6px; }
		.blk-p-tab { padding:9px 16px; border-radius:99px; text-decoration:none; font-weight:600; color:#50575e; background:#f0f0f1; }
		.blk-p-tab.is-active { background:var(--p); color:#fff; }
		.blk-p-tab span { background:rgba(255,255,255,.25); padding:1px 8px; border-radius:99px; margin-left:4px; font-size:.85em; }
		.blk-p-tab.is-soon { opacity:.6; cursor:default; }
		.blk-p-tab em { font-style:normal; font-size:.7em; opacity:.7; margin-left:5px; text-transform:uppercase; }
		.blk-p-orders { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:18px; margin:22px 20px; }
		.blk-p-card { background:#fff; border:1px solid #e0d8c8; border-left:4px solid var(--gl); border-radius:12px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,.04); }
		.blk-p-c-top { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
		.blk-p-num { font-weight:800; font-size:1.05rem; }
		.blk-p-date { color:#787c82; font-size:.85rem; margin-right:auto; }
		.blk-p-badge { padding:3px 12px; border-radius:99px; font-size:.78rem; font-weight:700; background:#eee; color:#333; }
		.blk-st-processing,.blk-st-blk-new { background:#fff3cd; color:#7a5a00; }
		.blk-st-completed { background:#d4edda; color:#155724; }
		.blk-st-cancelled,.blk-st-failed { background:#f8d7da; color:#721c24; }
		.blk-p-cust { margin-bottom:10px; line-height:1.5; }
		.blk-p-cust strong { font-size:1.05rem; }
		.blk-p-phone { display:inline-block; margin-left:8px; color:var(--p); font-weight:700; }
		.blk-p-msg { display:inline-block; margin-left:8px; font-size:.8rem; color:#787c82; background:#f0f0f1; padding:1px 8px; border-radius:6px; }
		.blk-p-contact { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
		.blk-p-btn { padding:7px 14px; border-radius:8px; text-decoration:none; font-weight:700; font-size:.82rem; color:#fff; }
		.blk-p-call { background:#2c7; } .blk-p-tg { background:#229ED9; } .blk-p-vb { background:#7360f2; }
		.blk-p-np { font-size:.88rem; color:#3c434a; margin-bottom:10px; }
		.blk-p-items { list-style:none; margin:0 0 12px; padding:10px 0; border-top:1px dashed #e0d8c8; border-bottom:1px dashed #e0d8c8; }
		.blk-p-items li { display:flex; align-items:center; gap:8px; padding:3px 0; font-size:.9rem; }
		.blk-p-items li span:first-child { flex:1; }
		.blk-p-qty { color:#787c82; } .blk-p-sum { font-weight:700; color:var(--g); }
		.blk-p-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
		.blk-p-total strong { font-size:1.15rem; color:var(--g); }
		.blk-p-st-sel { min-width:150px; padding:6px 10px; border-radius:8px; border:1px solid #c3aa6e; font-weight:600; }
		.blk-p-empty { margin:40px 20px; color:#787c82; font-size:1.1rem; }
		.blk-p-saved { outline:2px solid #2c7 !important; transition:outline .3s; }
	</style>
	<script>
	(function(){
		var AJAX='<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', N='<?php echo esc_js( $nonce ); ?>';
		document.querySelectorAll('.blk-p-st-sel').forEach(function(sel){
			sel.addEventListener('change', function(){
				var card=sel.closest('.blk-p-card'), id=card.getAttribute('data-id'), v=sel.value.replace(/^wc-/,'');
				var body=new URLSearchParams(); body.append('action','blk_panel_status'); body.append('_n',N); body.append('id',id); body.append('status',v);
				fetch(AJAX,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin'})
					.then(function(r){return r.json();}).then(function(res){
						if(res&&res.success){ var bdg=card.querySelector('.blk-p-badge'); if(bdg) bdg.textContent=res.data.label; card.classList.add('blk-p-saved'); setTimeout(function(){card.classList.remove('blk-p-saved');},1200); }
					}).catch(function(){});
			});
		});
	})();
	</script>
	<?php
}
