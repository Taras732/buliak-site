<?php
/* Plugin Name: Буляк Панель (спрощена адмінка)
 * Description: Чисте єдине меню для менеджерів поверх WooCommerce. Екран Замовлення: фільтри, картки/список, клік→повне замовлення. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
	add_menu_page( 'Панель БУЛЯК', '🥩 Панель', 'edit_shop_orders', 'buliak-panel', 'blk_panel_render', 'dashicons-store', 2 );
}, 1 );

add_action( 'wp_ajax_blk_panel_status', function () {
	if ( ! current_user_can( 'edit_shop_orders' ) ) { wp_send_json_error(); }
	$id     = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
	$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
	$order  = $id ? wc_get_order( $id ) : false;
	if ( ! $order || ! $status ) { wp_send_json_error(); }
	$order->update_status( $status, 'Панель: ' );
	wp_send_json_success( array( 'label' => wc_get_order_status_name( $status ) ) );
} );

/* URL редагування замовлення (HPOS-сумісно) */
function blk_panel_order_url( $id ) {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
		return admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $id );
	}
	return admin_url( 'post.php?post=' . $id . '&action=edit' );
}

function blk_panel_render() {
	if ( ! function_exists( 'wc_get_orders' ) ) { echo '<div class="wrap"><p>WooCommerce вимкнено.</p></div>'; return; }

	$today   = current_time( 'Y-m-d' );
	$status  = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';
	$range   = isset( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '';
	$from    = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
	$to      = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';

	// визначити діапазон дат
	if ( $from || $to ) { /* кастомний — як є */ }
	elseif ( $range === 'all' ) { $from = ''; $to = ''; }
	elseif ( $range === 'week' ) { $from = date( 'Y-m-d', strtotime( '-6 days', current_time( 'timestamp' ) ) ); $to = $today; }
	else { $from = $today; $to = $today; $range = 'today'; }

	$args = array( 'limit' => 150, 'orderby' => 'date', 'order' => 'DESC' );
	if ( $from && $to ) { $args['date_created'] = strtotime( $from . ' 00:00:00' ) . '...' . strtotime( $to . ' 23:59:59' ); }
	elseif ( $from ) { $args['date_created'] = '>=' . strtotime( $from . ' 00:00:00' ); }
	if ( $status && $status !== 'all' ) { $args['status'] = $status; }

	$orders   = wc_get_orders( $args );
	$statuses = wc_get_order_statuses();
	$nonce    = wp_create_nonce( 'blk_panel' );
	$base     = admin_url( 'admin.php?page=buliak-panel' );
	$qs       = function ( $extra ) use ( $base, $status, $from, $to ) {
		return esc_url( add_query_arg( array_merge( array( 'status' => $status, 'from' => $from, 'to' => $to ), $extra ), $base ) );
	};
	?>
	<div class="blk-panel">
		<div class="blk-p-head">
			<h1>🥩 Панель БУЛЯК</h1>
			<nav class="blk-p-tabs">
				<a class="blk-p-tab is-active" href="#">Замовлення <span><?php echo count( $orders ); ?></span></a>
				<a class="blk-p-tab is-soon" href="#">Товари <em>скоро</em></a>
			</nav>
		</div>

		<form class="blk-p-filters" method="get">
			<input type="hidden" name="page" value="buliak-panel">
			<div class="blk-p-quick">
				<a class="blk-p-q <?php echo $range === 'today' ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'buliak-panel', 'status' => $status, 'range' => 'today', 'from' => '', 'to' => '' ), admin_url( 'admin.php' ) ) ); ?>">Сьогодні</a>
				<a class="blk-p-q <?php echo $range === 'week' ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'buliak-panel', 'status' => $status, 'range' => 'week', 'from' => '', 'to' => '' ), admin_url( 'admin.php' ) ) ); ?>">7 днів</a>
				<a class="blk-p-q <?php echo $range === 'all' ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'buliak-panel', 'status' => $status, 'range' => 'all', 'from' => '', 'to' => '' ), admin_url( 'admin.php' ) ) ); ?>">Усі</a>
			</div>
			<label>з <input type="date" name="from" value="<?php echo esc_attr( $from ); ?>"></label>
			<label>по <input type="date" name="to" value="<?php echo esc_attr( $to ); ?>"></label>
			<select name="status">
				<option value="all" <?php selected( $status, 'all' ); ?>>Усі статуси</option>
				<?php foreach ( $statuses as $slug => $label ) : $s = preg_replace( '/^wc-/', '', $slug ); ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button button-primary">Показати</button>
			<span class="blk-p-view">
				<button type="button" class="blk-p-vw" data-v="cards" title="Картки">▦</button>
				<button type="button" class="blk-p-vw" data-v="list" title="Список">☰</button>
			</span>
		</form>

		<?php if ( empty( $orders ) ) : ?>
			<div class="blk-p-empty">За вибраний період замовлень немає. Спробуй «7 днів» або «Усі».</div>
		<?php else : ?>
		<div class="blk-p-orders" id="blk-p-orders">
			<?php foreach ( $orders as $o ) :
				$id    = $o->get_id();
				$date  = $o->get_date_created() ? $o->get_date_created()->date_i18n( 'd.m.Y · H:i' ) : '';
				$st    = 'wc-' . $o->get_status();
				$name  = trim( $o->get_billing_last_name() . ' ' . $o->get_billing_first_name() );
				$phone = $o->get_billing_phone();
				$pclean= preg_replace( '/[^0-9]/', '', $phone );
				$msg   = $o->get_meta( '_billing_messenger' );
				$np    = trim( $o->get_meta( '_billing_np_city' ) . ( $o->get_meta( '_billing_np_branch' ) ? ', ' . $o->get_meta( '_billing_np_branch' ) : '' ) );
				$ourl  = blk_panel_order_url( $id );
			?>
			<div class="blk-p-card" data-id="<?php echo esc_attr( $id ); ?>" data-href="<?php echo esc_url( $ourl ); ?>">
				<div class="blk-p-c-top">
					<a class="blk-p-num" href="<?php echo esc_url( $ourl ); ?>">№<?php echo esc_html( $o->get_order_number() ); ?></a>
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
					<a href="tel:<?php echo esc_attr( $phone ); ?>" class="blk-p-btn blk-p-call">📞</a>
					<a href="https://t.me/+38<?php echo esc_attr( substr( $pclean, -9 ) ); ?>" target="_blank" rel="noopener" class="blk-p-btn blk-p-tg">Telegram</a>
					<a href="viber://chat?number=%2B38<?php echo esc_attr( substr( $pclean, -9 ) ); ?>" class="blk-p-btn blk-p-vb">Viber</a>
					<a href="<?php echo esc_url( $ourl ); ?>" class="blk-p-btn blk-p-open">Відкрити →</a>
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
					<select class="blk-p-st-sel" title="Статус">
						<?php foreach ( $statuses as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $st ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>

	<style>
		.blk-panel { --g:#b8860b; --gl:#E0B557; --p:#B81F33; max-width:1200px; }
		.blk-p-head { display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; margin:18px 20px 0; }
		.blk-p-head h1 { font-size:1.7rem; margin:0; }
		.blk-p-tabs { display:flex; gap:6px; }
		.blk-p-tab { padding:9px 16px; border-radius:99px; text-decoration:none; font-weight:600; color:#50575e; background:#f0f0f1; }
		.blk-p-tab.is-active { background:var(--p); color:#fff; }
		.blk-p-tab span { background:rgba(255,255,255,.25); padding:1px 8px; border-radius:99px; margin-left:4px; font-size:.85em; }
		.blk-p-tab.is-soon { opacity:.55; cursor:default; } .blk-p-tab em { font-style:normal; font-size:.7em; margin-left:5px; text-transform:uppercase; }
		.blk-p-filters { display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:18px 20px 0; background:#fff; border:1px solid #e0d8c8; border-radius:12px; padding:12px 16px; }
		.blk-p-quick { display:flex; gap:6px; margin-right:6px; }
		.blk-p-q { padding:6px 14px; border-radius:99px; text-decoration:none; font-weight:600; font-size:.85rem; background:#f0f0f1; color:#50575e; }
		.blk-p-q.on { background:var(--gl); color:#3a2c00; }
		.blk-p-filters label { font-size:.85rem; color:#50575e; }
		.blk-p-filters input[type=date], .blk-p-filters select { padding:5px 8px; border-radius:8px; border:1px solid #c3aa6e; }
		.blk-p-view { margin-left:auto; display:flex; gap:4px; }
		.blk-p-vw { width:36px; height:34px; border:1px solid #c3aa6e; background:#fff; border-radius:8px; cursor:pointer; font-size:1.1rem; line-height:1; }
		.blk-p-vw.on { background:var(--p); color:#fff; border-color:var(--p); }
		.blk-p-orders { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:18px; margin:18px 20px; }
		.blk-p-card { background:#fff; border:1px solid #e0d8c8; border-left:4px solid var(--gl); border-radius:12px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,.04); cursor:pointer; transition:box-shadow .15s, transform .15s; }
		.blk-p-card:hover { box-shadow:0 6px 22px rgba(0,0,0,.1); transform:translateY(-2px); }
		.blk-p-c-top { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
		.blk-p-num { font-weight:800; font-size:1.05rem; color:var(--p); text-decoration:none; }
		.blk-p-date { color:#787c82; font-size:.85rem; margin-right:auto; }
		.blk-p-badge { padding:3px 12px; border-radius:99px; font-size:.78rem; font-weight:700; background:#eee; color:#333; }
		.blk-st-processing { background:#fff3cd; color:#7a5a00; } .blk-st-completed { background:#d4edda; color:#155724; }
		.blk-st-cancelled,.blk-st-failed,.blk-st-refunded { background:#f8d7da; color:#721c24; }
		.blk-p-cust { margin-bottom:10px; line-height:1.5; }
		.blk-p-cust strong { font-size:1.05rem; }
		.blk-p-phone { margin-left:8px; color:var(--p); font-weight:700; }
		.blk-p-msg { margin-left:8px; font-size:.8rem; color:#787c82; background:#f0f0f1; padding:1px 8px; border-radius:6px; }
		.blk-p-contact { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
		.blk-p-btn { padding:7px 12px; border-radius:8px; text-decoration:none; font-weight:700; font-size:.8rem; color:#fff; }
		.blk-p-call { background:#2c7; } .blk-p-tg { background:#229ED9; } .blk-p-vb { background:#7360f2; } .blk-p-open { background:#3a3a3a; }
		.blk-p-np { font-size:.88rem; color:#3c434a; margin-bottom:10px; }
		.blk-p-items { list-style:none; margin:0 0 12px; padding:10px 0; border-top:1px dashed #e0d8c8; border-bottom:1px dashed #e0d8c8; }
		.blk-p-items li { display:flex; align-items:center; gap:8px; padding:3px 0; font-size:.9rem; }
		.blk-p-items li span:first-child { flex:1; } .blk-p-qty { color:#787c82; } .blk-p-sum { font-weight:700; color:var(--g); }
		.blk-p-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
		.blk-p-total strong { font-size:1.15rem; color:var(--g); }
		.blk-p-st-sel { min-width:140px; padding:6px 10px; border-radius:8px; border:1px solid #c3aa6e; font-weight:600; }
		.blk-p-empty { margin:40px 20px; color:#787c82; font-size:1.1rem; }
		.blk-p-saved { outline:2px solid #2c7 !important; }
		/* === СПИСОК === */
		.blk-p-orders.is-list { display:block; }
		.blk-p-orders.is-list .blk-p-card { display:grid; grid-template-columns:auto 1.4fr 1fr auto auto; align-items:center; gap:14px; padding:10px 16px; border-left-width:4px; margin-bottom:8px; }
		.blk-p-orders.is-list .blk-p-c-top { margin:0; gap:8px; }
		.blk-p-orders.is-list .blk-p-cust { margin:0; }
		.blk-p-orders.is-list .blk-p-contact, .blk-p-orders.is-list .blk-p-np, .blk-p-orders.is-list .blk-p-items { display:none; }
		.blk-p-orders.is-list .blk-p-foot { margin:0; justify-content:flex-end; }
		.blk-p-orders.is-list .blk-p-date { margin:0; }
	</style>
	<script>
	(function(){
		var AJAX='<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', N='<?php echo esc_js( $nonce ); ?>';
		// статус
		document.querySelectorAll('.blk-p-st-sel').forEach(function(sel){
			sel.addEventListener('click',function(e){e.stopPropagation();});
			sel.addEventListener('change', function(){
				var card=sel.closest('.blk-p-card'), id=card.getAttribute('data-id'), v=sel.value.replace(/^wc-/,'');
				var body=new URLSearchParams(); body.append('action','blk_panel_status'); body.append('_n',N); body.append('id',id); body.append('status',v);
				fetch(AJAX,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin'})
					.then(function(r){return r.json();}).then(function(res){ if(res&&res.success){ var bdg=card.querySelector('.blk-p-badge'); if(bdg) bdg.textContent=res.data.label; card.classList.add('blk-p-saved'); setTimeout(function(){card.classList.remove('blk-p-saved');},1200);} });
			});
		});
		// клік по картці → замовлення (крім контролів)
		document.querySelectorAll('.blk-p-card').forEach(function(card){
			card.addEventListener('click',function(e){ if(e.target.closest('a,select,button,input')) return; var h=card.getAttribute('data-href'); if(h) window.location=h; });
		});
		// перемикач картки/список (localStorage)
		var box=document.getElementById('blk-p-orders');
		function applyView(v){ if(!box) return; box.classList.toggle('is-list', v==='list'); document.querySelectorAll('.blk-p-vw').forEach(function(b){ b.classList.toggle('on', b.dataset.v===v); }); }
		var saved=localStorage.getItem('blk_p_view')||'cards'; applyView(saved);
		document.querySelectorAll('.blk-p-vw').forEach(function(b){ b.addEventListener('click',function(){ localStorage.setItem('blk_p_view',b.dataset.v); applyView(b.dataset.v); }); });
	})();
	</script>
	<?php
}
