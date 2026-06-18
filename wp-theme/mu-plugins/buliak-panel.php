<?php
/* Plugin Name: Буляк Панель (спрощена адмінка)
 * Description: Менеджерська панель поверх WooCommerce: список замовлень (фільтри, картки/список) + власний зручний редактор замовлення (додати товар, вага, статус). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'admin_menu', function () {
	add_menu_page( 'Панель БУЛЯК', '🥩 Панель', 'edit_shop_orders', 'buliak-panel', 'blk_panel_render', 'dashicons-store', 2 );
}, 1 );

/* ---- AJAX: швидка зміна статусу зі списку ---- */
add_action( 'wp_ajax_blk_panel_status', function () {
	if ( ! current_user_can( 'edit_shop_orders' ) ) { wp_send_json_error(); }
	$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
	$st = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
	$o  = $id ? wc_get_order( $id ) : false;
	if ( ! $o || ! $st ) { wp_send_json_error(); }
	$o->update_status( $st, 'Панель: ' );
	wp_send_json_success( array( 'label' => wc_get_order_status_name( $st ) ) );
} );

/* ---- AJAX: зберегти/створити замовлення з редактора ---- */
add_action( 'wp_ajax_blk_panel_save', function () {
	if ( ! current_user_can( 'edit_shop_orders' ) ) { wp_send_json_error( array( 'msg' => 'Немає прав' ) ); }
	check_ajax_referer( 'blk_panel', '_n' );
	$id     = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : 'new';
	$items  = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : array();
	$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'processing';
	$g = function ( $k ) { return isset( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : ''; };

	$order = ( $id === 'new' ) ? wc_create_order() : wc_get_order( intval( $id ) );
	if ( ! $order ) { wp_send_json_error( array( 'msg' => 'Замовлення не знайдено' ) ); }

	$order->set_billing_last_name( $g( 'last_name' ) );
	$order->set_billing_first_name( $g( 'first_name' ) );
	$order->set_billing_phone( $g( 'phone' ) );
	$order->update_meta_data( '_billing_messenger', $g( 'messenger' ) );
	$order->update_meta_data( '_billing_np_city', $g( 'np_city' ) );
	$order->update_meta_data( '_billing_np_branch', $g( 'np_branch' ) );
	$order->update_meta_data( '_buliak_tg_sent', 1 ); // створене вручну з панелі — без авто-Telegram

	foreach ( $order->get_items() as $item_id => $item ) { $order->remove_item( $item_id ); }
	foreach ( $items as $row ) {
		$pid = isset( $row['id'] ) ? intval( $row['id'] ) : 0;
		$qty = isset( $row['qty'] ) ? floatval( str_replace( ',', '.', $row['qty'] ) ) : 0;
		$p   = $pid ? wc_get_product( $pid ) : false;
		if ( $p && $qty > 0 ) { $order->add_product( $p, $qty ); }
	}
	$order->calculate_totals();
	$order->set_status( $status );
	if ( $id === 'new' ) { $order->add_order_note( 'Створено вручну в Панелі.' ); }
	$order->save();
	wp_send_json_success( array( 'id' => $order->get_id(), 'redirect' => admin_url( 'admin.php?page=buliak-panel&order=' . $order->get_id() . '&saved=1' ) ) );
} );

/* ====================== РОУТЕР ====================== */
function blk_panel_render() {
	if ( ! function_exists( 'wc_get_orders' ) ) { echo '<div class="wrap"><p>WooCommerce вимкнено.</p></div>'; return; }
	$ord = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : '';
	echo '<div class="blk-panel">';
	blk_panel_styles();
	if ( $ord ) { blk_panel_editor( $ord ); } else { blk_panel_list(); }
	echo '</div>';
}

/* ====================== СПИСОК ====================== */
function blk_panel_list() {
	$today  = current_time( 'Y-m-d' );
	$status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';
	$range  = isset( $_GET['range'] ) ? sanitize_text_field( wp_unslash( $_GET['range'] ) ) : '';
	$from   = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
	$to     = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
	if ( $from || $to ) { $range = 'custom'; }
	elseif ( $range === 'all' ) { $from = ''; $to = ''; }
	elseif ( $range === 'week' ) { $from = date( 'Y-m-d', strtotime( '-6 days', current_time( 'timestamp' ) ) ); $to = $today; }
	else { $from = $today; $to = $today; $range = 'today'; }

	$args = array( 'limit' => 150, 'orderby' => 'date', 'order' => 'DESC' );
	if ( $from && $to ) { $args['date_created'] = $from . '...' . $to; }
	if ( $status && $status !== 'all' ) { $args['status'] = $status; }
	$orders   = wc_get_orders( $args );
	$statuses = wc_get_order_statuses();
	$nonce    = wp_create_nonce( 'blk_panel' );
	$ql = function ( $r ) use ( $status ) { return esc_url( add_query_arg( array( 'page' => 'buliak-panel', 'status' => $status, 'range' => $r, 'from' => '', 'to' => '' ), admin_url( 'admin.php' ) ) ); };
	?>
	<div class="blk-p-head">
		<h1>🥩 Панель БУЛЯК</h1>
		<nav class="blk-p-tabs">
			<a class="blk-p-tab is-active" href="#">Замовлення <span><?php echo count( $orders ); ?></span></a>
			<a class="blk-p-tab is-soon" href="#">Товари <em>скоро</em></a>
		</nav>
	</div>

	<div class="blk-p-bar">
		<a class="blk-p-add" href="<?php echo esc_url( admin_url( 'admin.php?page=buliak-panel&order=new' ) ); ?>">➕ Додати замовлення</a>
		<form class="blk-p-filters" method="get" id="blk-p-filt">
			<input type="hidden" name="page" value="buliak-panel">
			<div class="blk-p-seg">
				<a class="blk-p-q <?php echo $range === 'today' ? 'on' : ''; ?>" href="<?php echo $ql( 'today' ); ?>">Сьогодні</a>
				<a class="blk-p-q <?php echo $range === 'week' ? 'on' : ''; ?>" href="<?php echo $ql( 'week' ); ?>">7 днів</a>
				<a class="blk-p-q <?php echo $range === 'all' ? 'on' : ''; ?>" href="<?php echo $ql( 'all' ); ?>">Усі</a>
			</div>
			<span class="blk-p-dates">
				<input type="date" name="from" value="<?php echo esc_attr( $from ); ?>" onchange="this.form.submit()">
				<span>–</span>
				<input type="date" name="to" value="<?php echo esc_attr( $to ); ?>" onchange="this.form.submit()">
			</span>
			<select name="status" onchange="this.form.submit()">
				<option value="all" <?php selected( $status, 'all' ); ?>>Усі статуси</option>
				<?php foreach ( $statuses as $slug => $label ) : $s = preg_replace( '/^wc-/', '', $slug ); ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="blk-p-view">
				<button type="button" class="blk-p-vw" data-v="cards" title="Картки">▦</button>
				<button type="button" class="blk-p-vw" data-v="list" title="Список">☰</button>
			</span>
		</form>
	</div>

	<?php if ( empty( $orders ) ) : ?>
		<div class="blk-p-empty">За вибраний період замовлень немає. Спробуй «7 днів» або «Усі».</div>
	<?php else : ?>
	<div class="blk-p-orders" id="blk-p-orders">
		<?php foreach ( $orders as $o ) :
			$id = $o->get_id();
			$st = 'wc-' . $o->get_status();
			$name = trim( $o->get_billing_last_name() . ' ' . $o->get_billing_first_name() );
			$phone = $o->get_billing_phone();
			$pclean = preg_replace( '/[^0-9]/', '', $phone );
			$msg = $o->get_meta( '_billing_messenger' );
			$np = trim( $o->get_meta( '_billing_np_city' ) . ( $o->get_meta( '_billing_np_branch' ) ? ', ' . $o->get_meta( '_billing_np_branch' ) : '' ) );
			$ourl = admin_url( 'admin.php?page=buliak-panel&order=' . $id );
		?>
		<div class="blk-p-card" data-id="<?php echo esc_attr( $id ); ?>" data-href="<?php echo esc_url( $ourl ); ?>">
			<div class="blk-p-c-top">
				<a class="blk-p-num" href="<?php echo esc_url( $ourl ); ?>">№<?php echo esc_html( $o->get_order_number() ); ?></a>
				<span class="blk-p-date"><?php echo esc_html( $o->get_date_created() ? $o->get_date_created()->date_i18n( 'd.m · H:i' ) : '' ); ?></span>
				<span class="blk-p-badge blk-st-<?php echo esc_attr( $o->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $o->get_status() ) ); ?></span>
			</div>
			<div class="blk-p-cust"><strong><?php echo esc_html( $name ? $name : 'Без імені' ); ?></strong>
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
	<script>
	(function(){
		var AJAX='<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', N='<?php echo esc_js( $nonce ); ?>';
		document.querySelectorAll('.blk-p-st-sel').forEach(function(sel){
			sel.addEventListener('click',function(e){e.stopPropagation();});
			sel.addEventListener('change',function(){ var card=sel.closest('.blk-p-card'); var body=new URLSearchParams(); body.append('action','blk_panel_status'); body.append('_n',N); body.append('id',card.dataset.id); body.append('status',sel.value.replace(/^wc-/,''));
				fetch(AJAX,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin'}).then(function(r){return r.json();}).then(function(res){ if(res&&res.success){var b=card.querySelector('.blk-p-badge'); if(b)b.textContent=res.data.label; card.classList.add('blk-p-saved'); setTimeout(function(){card.classList.remove('blk-p-saved');},1200);} }); });
		});
		document.querySelectorAll('.blk-p-card').forEach(function(card){ card.addEventListener('click',function(e){ if(e.target.closest('a,select,button,input'))return; var h=card.dataset.href; if(h)location=h; }); });
		var box=document.getElementById('blk-p-orders');
		function v(x){ if(box)box.classList.toggle('is-list',x==='list'); document.querySelectorAll('.blk-p-vw').forEach(function(b){b.classList.toggle('on',b.dataset.v===x);}); }
		v(localStorage.getItem('blk_p_view')||'cards');
		document.querySelectorAll('.blk-p-vw').forEach(function(b){ b.addEventListener('click',function(){ localStorage.setItem('blk_p_view',b.dataset.v); v(b.dataset.v); }); });
	})();
	</script>
	<?php
}

/* ====================== РЕДАКТОР ЗАМОВЛЕННЯ ====================== */
function blk_panel_editor( $ord ) {
	$is_new = ( $ord === 'new' );
	$order  = $is_new ? null : wc_get_order( intval( $ord ) );
	if ( ! $is_new && ! $order ) { echo '<p>Замовлення не знайдено. <a href="' . esc_url( admin_url( 'admin.php?page=buliak-panel' ) ) . '">← Назад</a></p>'; return; }
	$nonce    = wp_create_nonce( 'blk_panel' );
	$statuses = wc_get_order_statuses();
	$cur_st   = $is_new ? 'wc-processing' : 'wc-' . $order->get_status();
	$ln = $is_new ? '' : $order->get_billing_last_name();
	$fn = $is_new ? '' : $order->get_billing_first_name();
	$ph = $is_new ? '' : $order->get_billing_phone();
	$mg = $is_new ? 'Telegram' : $order->get_meta( '_billing_messenger' );
	$ct = $is_new ? '' : $order->get_meta( '_billing_np_city' );
	$br = $is_new ? '' : $order->get_meta( '_billing_np_branch' );

	// каталог для додавання товару
	$catalog = array();
	foreach ( wc_get_products( array( 'limit' => -1, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
		$catalog[] = array( 'id' => $p->get_id(), 'name' => $p->get_name(), 'price' => (float) $p->get_price() );
	}
	// поточні позиції
	$cur_items = array();
	if ( ! $is_new ) {
		foreach ( $order->get_items() as $item ) {
			$p = $item->get_product();
			$cur_items[] = array( 'id' => $p ? $p->get_id() : 0, 'name' => $item->get_name(), 'qty' => (float) $item->get_quantity(), 'price' => $p ? (float) $p->get_price() : 0 );
		}
	}

	// клієнт-історія за телефоном
	$hist = array();
	if ( ! $is_new && $ph ) {
		foreach ( wc_get_orders( array( 'billing_phone' => $ph, 'limit' => 8, 'exclude' => array( $order->get_id() ), 'orderby' => 'date', 'order' => 'DESC' ) ) as $h ) {
			$hist[] = array( 'num' => $h->get_order_number(), 'id' => $h->get_id(), 'date' => $h->get_date_created() ? $h->get_date_created()->date_i18n( 'd.m.Y' ) : '', 'total' => wp_strip_all_tags( wc_price( $h->get_total(), array( 'decimals' => 0 ) ) ), 'status' => wc_get_order_status_name( $h->get_status() ) );
		}
	}
	$pclean = preg_replace( '/[^0-9]/', '', $ph );
	?>
	<div class="blk-p-head">
		<h1><a class="blk-p-back" href="<?php echo esc_url( admin_url( 'admin.php?page=buliak-panel' ) ); ?>">←</a> <?php echo $is_new ? 'Нове замовлення' : '🥩 Замовлення №' . esc_html( $order->get_order_number() ); ?></h1>
		<?php if ( ! $is_new ) : ?><span class="blk-p-badge blk-st-<?php echo esc_attr( $order->get_status() ); ?>" style="font-size:.9rem"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span><?php endif; ?>
	</div>
	<?php if ( isset( $_GET['saved'] ) ) : ?><div class="blk-p-ok">✓ Збережено</div><?php endif; ?>

	<div class="blk-p-edit" data-id="<?php echo esc_attr( $is_new ? 'new' : $order->get_id() ); ?>">
		<div class="blk-p-col">
			<div class="blk-p-box">
				<h3>Клієнт</h3>
				<div class="blk-p-grid2">
					<label>Прізвище<input type="text" id="bp-ln" value="<?php echo esc_attr( $ln ); ?>"></label>
					<label>Ім'я<input type="text" id="bp-fn" value="<?php echo esc_attr( $fn ); ?>"></label>
					<label>Телефон<input type="text" id="bp-ph" value="<?php echo esc_attr( $ph ); ?>" placeholder="073..."></label>
					<label>Месенджер<select id="bp-mg"><option <?php selected( $mg, 'Telegram' ); ?>>Telegram</option><option <?php selected( $mg, 'Viber' ); ?>>Viber</option></select></label>
					<label class="blk-np-wrap">Місто (НП)<input type="text" id="bp-ct" value="<?php echo esc_attr( $ct ); ?>" autocomplete="off" placeholder="Почни вводити місто…"><ul class="blk-np-dd" id="bp-ct-dd"></ul></label>
					<label class="blk-np-wrap">Відділення (НП)<input type="text" id="bp-br" value="<?php echo esc_attr( $br ); ?>" autocomplete="off" placeholder="Спершу обери місто"><ul class="blk-np-dd" id="bp-br-dd"></ul></label>
				</div>
				<?php if ( $pclean ) : ?>
				<div class="blk-p-contact" style="margin-top:12px">
					<a href="tel:<?php echo esc_attr( $ph ); ?>" class="blk-p-btn blk-p-call">📞 Дзвінок</a>
					<a href="https://t.me/+38<?php echo esc_attr( substr( $pclean, -9 ) ); ?>" target="_blank" rel="noopener" class="blk-p-btn blk-p-tg">Telegram</a>
					<a href="viber://chat?number=%2B38<?php echo esc_attr( substr( $pclean, -9 ) ); ?>" class="blk-p-btn blk-p-vb">Viber</a>
				</div>
				<?php endif; ?>
			</div>

			<div class="blk-p-box">
				<h3>Товари <span class="blk-p-hint">вага в кг, до грама (напр. 1.005 = 1 кг 5 г)</span></h3>
				<div id="bp-items" class="blk-p-items-edit"></div>
				<div class="blk-p-additem">
					<select id="bp-add-sel"><option value="">+ Обрати товар…</option><?php foreach ( $catalog as $c ) : ?><option value="<?php echo esc_attr( $c['id'] ); ?>" data-name="<?php echo esc_attr( $c['name'] ); ?>" data-price="<?php echo esc_attr( $c['price'] ); ?>"><?php echo esc_html( $c['name'] ); ?> — <?php echo (int) $c['price']; ?> ₴/кг</option><?php endforeach; ?></select>
					<input type="number" id="bp-add-qty" value="1" step="0.001" min="0.001" style="width:90px">
					<button type="button" class="button" id="bp-add-btn">Додати</button>
				</div>
				<div class="blk-p-totalrow">Орієнтовна сума: <strong id="bp-total">0 ₴</strong></div>
			</div>
		</div>

		<div class="blk-p-col blk-p-side">
			<div class="blk-p-box">
				<h3>Статус</h3>
				<select id="bp-status" class="blk-p-st-big">
					<?php foreach ( $statuses as $slug => $label ) : ?><option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $cur_st ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
				</select>
				<button type="button" class="button button-primary blk-p-save" id="bp-save">💾 Зберегти замовлення</button>
				<div id="bp-msg" class="blk-p-msg2"></div>
			</div>
			<?php if ( ! $is_new && $hist ) : ?>
			<div class="blk-p-box">
				<h3>Історія клієнта</h3>
				<ul class="blk-p-hist">
					<?php foreach ( $hist as $h ) : ?><li><a href="<?php echo esc_url( admin_url( 'admin.php?page=buliak-panel&order=' . $h['id'] ) ); ?>">№<?php echo esc_html( $h['num'] ); ?></a> <span><?php echo esc_html( $h['date'] ); ?></span> <strong><?php echo esc_html( $h['total'] ); ?></strong> <em><?php echo esc_html( $h['status'] ); ?></em></li><?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<script>
	(function(){
		var AJAX='<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', N='<?php echo esc_js( $nonce ); ?>';
		var ID='<?php echo esc_js( $is_new ? 'new' : $order->get_id() ); ?>';
		var items=<?php echo wp_json_encode( $cur_items ); ?>;
		var wrap=document.getElementById('bp-items'), totalEl=document.getElementById('bp-total');
		function fmt(n){ return Math.round(n)+' ₴'; }
		function render(){
			wrap.innerHTML=''; var sum=0;
			items.forEach(function(it,i){ var line=Math.round(it.price*it.qty); sum+=line;
				var row=document.createElement('div'); row.className='blk-p-irow';
				row.innerHTML='<span class="blk-p-iname">'+it.name+'</span><span class="blk-p-iprice">'+Math.round(it.price)+' ₴/кг</span><span class="blk-p-iqty"><button type="button" class="bp-dec">−</button><input type="number" value="'+it.qty+'" step="0.001" min="0.001"><span>кг</span><button type="button" class="bp-inc">+</button></span><span class="blk-p-iline">'+fmt(line)+'</span><button type="button" class="bp-rm" title="Прибрати">✕</button>';
				row.querySelector('.bp-dec').onclick=function(){ it.qty=Math.max(0.001,Math.round((it.qty-0.25)*1000)/1000); render(); };
				row.querySelector('.bp-inc').onclick=function(){ it.qty=Math.round((it.qty+0.25)*1000)/1000; render(); };
				row.querySelector('input').onchange=function(e){ var v=parseFloat(String(e.target.value).replace(',','.'))||0.001; it.qty=Math.round(v*1000)/1000; render(); };
				row.querySelector('.bp-rm').onclick=function(){ items.splice(i,1); render(); };
				wrap.appendChild(row);
			});
			if(!items.length) wrap.innerHTML='<div class="blk-p-noitems">Товарів ще немає — додай нижче.</div>';
			totalEl.textContent=fmt(sum);
		}
		render();
		document.getElementById('bp-add-btn').onclick=function(){ var sel=document.getElementById('bp-add-sel'), o=sel.selectedOptions[0]; if(!o||!o.value)return; var q=Math.round((parseFloat(String(document.getElementById('bp-add-qty').value).replace(',','.'))||1)*1000)/1000; items.push({id:parseInt(o.value),name:o.dataset.name,qty:q,price:parseFloat(o.dataset.price)}); sel.value=''; render(); };

		/* ---- Нова Пошта автопідбір (ті ж AJAX, що checkout) ---- */
		var cityRef='', branchList=[];
		var ctI=document.getElementById('bp-ct'), ctDD=document.getElementById('bp-ct-dd'), brI=document.getElementById('bp-br'), brDD=document.getElementById('bp-br-dd');
		function showDD(ul, items, input, onPick){
			ul.innerHTML='';
			items.slice(0,40).forEach(function(it){ var li=document.createElement('li'); li.textContent=it.label; li.onclick=function(){ input.value=it.value; ul.classList.remove('show'); onPick&&onPick(it); }; ul.appendChild(li); });
			ul.classList.toggle('show', items.length>0);
		}
		var ct_t;
		ctI.addEventListener('input', function(){ clearTimeout(ct_t); var q=ctI.value.trim(); if(q.length<2){ ctDD.classList.remove('show'); return; } ct_t=setTimeout(function(){ fetch(AJAX+'?action=blk_np_city&q='+encodeURIComponent(q),{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(a){ showDD(ctDD,(a||[]).map(function(x){return {label:x.name,value:x.name,ref:x.ref};}), ctI, function(it){ cityRef=it.ref; brI.value=''; branchList=[]; brI.placeholder='Завантаження…'; loadBranches(); }); }); },250); });
		function loadBranches(){ if(!cityRef)return; fetch(AJAX+'?action=blk_np_wh&ref='+encodeURIComponent(cityRef),{credentials:'same-origin'}).then(function(r){return r.json();}).then(function(a){ branchList=(a||[]).map(function(x){return {label:x.desc,value:x.desc};}); brI.placeholder='Почни вводити відділення…'; brI.focus(); filterBranches(); }); }
		function filterBranches(){ var q=brI.value.trim().toLowerCase(); var items=q?branchList.filter(function(x){return x.label.toLowerCase().indexOf(q)>-1;}):branchList; showDD(brDD, items, brI); }
		brI.addEventListener('input', filterBranches);
		brI.addEventListener('focus', function(){ if(branchList.length) filterBranches(); });
		document.addEventListener('click', function(e){ if(!e.target.closest('.blk-np-wrap')){ ctDD.classList.remove('show'); brDD.classList.remove('show'); } });
		document.getElementById('bp-save').onclick=function(){
			var btn=this; btn.disabled=true; var msg=document.getElementById('bp-msg'); msg.textContent='Збереження…';
			var b=new URLSearchParams(); b.append('action','blk_panel_save'); b.append('_n',N); b.append('id',ID); b.append('status',document.getElementById('bp-status').value.replace(/^wc-/,''));
			b.append('last_name',document.getElementById('bp-ln').value); b.append('first_name',document.getElementById('bp-fn').value); b.append('phone',document.getElementById('bp-ph').value);
			b.append('messenger',document.getElementById('bp-mg').value); b.append('np_city',document.getElementById('bp-ct').value); b.append('np_branch',document.getElementById('bp-br').value);
			items.forEach(function(it,i){ b.append('items['+i+'][id]',it.id); b.append('items['+i+'][qty]',it.qty); });
			fetch(AJAX,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:b.toString(),credentials:'same-origin'}).then(function(r){return r.json();}).then(function(res){
				if(res&&res.success){ msg.textContent='✓ Збережено'; location=res.data.redirect; } else { btn.disabled=false; msg.textContent='Помилка: '+((res&&res.data&&res.data.msg)||'спробуй ще'); }
			}).catch(function(){ btn.disabled=false; msg.textContent='Помилка мережі'; });
		};
	})();
	</script>
	<?php
}

/* ====================== СТИЛІ ====================== */
function blk_panel_styles() { ?>
	<style>
		.blk-panel { --g:#b8860b; --gl:#E0B557; --p:#B81F33; max-width:1240px; }
		.blk-panel input, .blk-panel select { box-sizing:border-box; max-width:100%; }
		.blk-p-head { display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap; margin:16px 20px 0; }
		.blk-p-head h1 { font-size:1.6rem; margin:0; display:flex; align-items:center; gap:12px; }
		.blk-p-back { text-decoration:none; color:var(--p); font-size:1.4rem; }
		.blk-p-tabs { display:flex; gap:6px; }
		.blk-p-tab { padding:8px 15px; border-radius:99px; text-decoration:none; font-weight:600; color:#50575e; background:#f0f0f1; }
		.blk-p-tab.is-active { background:var(--p); color:#fff; } .blk-p-tab span { background:rgba(255,255,255,.25); padding:1px 8px; border-radius:99px; margin-left:4px; font-size:.85em; }
		.blk-p-tab.is-soon { opacity:.55; } .blk-p-tab em { font-style:normal; font-size:.7em; margin-left:5px; text-transform:uppercase; }
		/* бар */
		.blk-p-bar { display:flex; align-items:center; gap:14px; flex-wrap:wrap; margin:16px 20px 0; }
		.blk-p-add { background:var(--p); color:#fff; text-decoration:none; font-weight:700; padding:11px 20px; border-radius:10px; white-space:nowrap; box-shadow:0 4px 14px rgba(184,31,51,.25); }
		.blk-p-add:hover { background:#9e1728; color:#fff; }
		.blk-p-filters { display:flex; align-items:center; gap:16px; flex-wrap:wrap; background:#fff; border:1px solid #e6ddc9; border-radius:12px; padding:10px 16px; flex:1; }
		.blk-p-seg { display:flex; gap:4px; background:#f4efe3; padding:3px; border-radius:99px; }
		.blk-p-q { padding:6px 16px; border-radius:99px; text-decoration:none; font-weight:600; font-size:.85rem; color:#6b6256; }
		.blk-p-q.on { background:var(--gl); color:#3a2c00; box-shadow:0 1px 4px rgba(0,0,0,.12); }
		.blk-p-dates { display:flex; align-items:center; gap:6px; color:#9a8f7a; }
		.blk-p-filters input[type=date], .blk-p-filters select { padding:6px 10px; border-radius:8px; border:1px solid #d8c89e; background:#fff; }
		.blk-p-view { margin-left:auto; display:flex; gap:4px; }
		.blk-p-vw { width:38px; height:36px; border:1px solid #d8c89e; background:#fff; border-radius:8px; cursor:pointer; font-size:1.1rem; }
		.blk-p-vw.on { background:var(--p); color:#fff; border-color:var(--p); }
		/* список карток */
		.blk-p-orders { display:grid; grid-template-columns:repeat(auto-fill,minmax(330px,1fr)); gap:16px; margin:18px 20px; }
		.blk-p-card { background:#fff; border:1px solid #e6ddc9; border-left:4px solid var(--gl); border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,.04); cursor:pointer; transition:.15s; }
		.blk-p-card:hover { box-shadow:0 6px 22px rgba(0,0,0,.1); transform:translateY(-2px); }
		.blk-p-c-top { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px; }
		.blk-p-num { font-weight:800; font-size:1.05rem; color:var(--p); text-decoration:none; }
		.blk-p-date { color:#9a8f7a; font-size:.82rem; margin-right:auto; }
		.blk-p-badge { padding:3px 12px; border-radius:99px; font-size:.76rem; font-weight:700; background:#eee; color:#333; }
		.blk-st-processing { background:#fff3cd; color:#7a5a00; } .blk-st-completed { background:#d4edda; color:#155724; } .blk-st-cancelled,.blk-st-failed,.blk-st-refunded { background:#f8d7da; color:#721c24; }
		.blk-p-cust { margin-bottom:8px; line-height:1.5; } .blk-p-cust strong { font-size:1.02rem; }
		.blk-p-phone { margin-left:8px; color:var(--p); font-weight:700; } .blk-p-msg { margin-left:8px; font-size:.78rem; color:#787c82; background:#f0f0f1; padding:1px 8px; border-radius:6px; }
		.blk-p-contact { display:flex; gap:7px; flex-wrap:wrap; margin-bottom:8px; }
		.blk-p-btn { padding:7px 12px; border-radius:8px; text-decoration:none; font-weight:700; font-size:.8rem; color:#fff; }
		.blk-p-call { background:#2c7; } .blk-p-tg { background:#229ED9; } .blk-p-vb { background:#7360f2; } .blk-p-open { background:#3a3a3a; }
		.blk-p-np { font-size:.86rem; color:#3c434a; margin-bottom:8px; }
		.blk-p-items { list-style:none; margin:0 0 10px; padding:9px 0; border-top:1px dashed #e6ddc9; border-bottom:1px dashed #e6ddc9; }
		.blk-p-items li { display:flex; gap:8px; padding:3px 0; font-size:.88rem; } .blk-p-items li span:first-child { flex:1; } .blk-p-qty { color:#9a8f7a; } .blk-p-sum { font-weight:700; color:var(--g); }
		.blk-p-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
		.blk-p-total strong { font-size:1.1rem; color:var(--g); }
		.blk-p-st-sel { min-width:130px; padding:6px 10px; border-radius:8px; border:1px solid #d8c89e; font-weight:600; }
		.blk-p-empty { margin:40px 20px; color:#787c82; font-size:1.1rem; }
		.blk-p-saved { outline:2px solid #2c7 !important; }
		.blk-p-orders.is-list { display:block; }
		.blk-p-orders.is-list .blk-p-card { display:grid; grid-template-columns:auto 1.4fr 1fr auto auto; align-items:center; gap:14px; padding:10px 16px; margin-bottom:8px; }
		.blk-p-orders.is-list .blk-p-c-top,.blk-p-orders.is-list .blk-p-cust,.blk-p-orders.is-list .blk-p-foot { margin:0; }
		.blk-p-orders.is-list .blk-p-contact,.blk-p-orders.is-list .blk-p-np,.blk-p-orders.is-list .blk-p-items { display:none; }
		/* редактор */
		.blk-p-ok,.blk-p-edit .blk-p-ok { margin:14px 20px 0; background:#d4edda; color:#155724; padding:10px 16px; border-radius:10px; font-weight:600; }
		.blk-p-edit { display:grid; grid-template-columns:1fr 360px; gap:18px; margin:18px 20px; align-items:start; }
		.blk-p-box { background:#fff; border:1px solid #e6ddc9; border-radius:12px; padding:18px 20px; margin-bottom:16px; }
		.blk-p-box h3 { margin:0 0 14px; font-size:1.1rem; display:flex; align-items:baseline; gap:10px; }
		.blk-p-hint { font-size:.75rem; font-weight:400; color:#9a8f7a; }
		.blk-p-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
		.blk-p-grid2 label { display:flex; flex-direction:column; gap:4px; font-size:.8rem; color:#6b6256; font-weight:600; }
		.blk-p-grid2 input, .blk-p-grid2 select { padding:9px 11px; border-radius:8px; border:1px solid #d8c89e; font-size:.95rem; }
		.blk-p-items-edit { display:flex; flex-direction:column; gap:8px; }
		.blk-p-irow { display:grid; grid-template-columns:1fr auto auto auto auto; align-items:center; gap:12px; padding:9px 12px; background:#faf7f0; border:1px solid #ece3cf; border-radius:10px; }
		.blk-p-iname { font-weight:600; } .blk-p-iprice { color:#9a8f7a; font-size:.85rem; }
		.blk-p-iqty { display:inline-flex; align-items:center; gap:6px; }
		.blk-p-iqty button { width:28px; height:28px; border:1px solid #d8c89e; background:#fff; border-radius:6px; cursor:pointer; font-size:1rem; }
		.blk-p-iqty input { width:60px; text-align:center; padding:5px; border:1px solid #d8c89e; border-radius:6px; }
		.blk-p-iline { font-weight:800; color:var(--g); min-width:64px; text-align:right; }
		.bp-rm { background:none; border:0; color:#c0392b; cursor:pointer; font-size:1rem; }
		.blk-p-noitems { color:#9a8f7a; padding:10px 0; }
		.blk-p-additem { display:flex; gap:10px; margin-top:14px; flex-wrap:wrap; }
		.blk-p-additem select { flex:1; min-width:180px; padding:9px 11px; border-radius:8px; border:1px solid #d8c89e; }
		.blk-p-additem input { padding:9px; border-radius:8px; border:1px solid #d8c89e; }
		.blk-p-totalrow { margin-top:16px; padding-top:14px; border-top:1px dashed #e6ddc9; text-align:right; font-size:1.05rem; } .blk-p-totalrow strong { font-size:1.4rem; color:var(--g); }
		.blk-p-side .blk-p-box { position:sticky; top:46px; }
		.blk-p-st-big { width:100%; padding:11px; border-radius:8px; border:1px solid #d8c89e; font-size:1rem; font-weight:600; margin-bottom:14px; }
		.blk-p-save { width:100%; justify-content:center; padding:12px !important; height:auto !important; font-size:1rem !important; }
		.blk-p-msg2 { margin-top:10px; text-align:center; color:#6b6256; font-size:.9rem; }
		.blk-p-hist { list-style:none; margin:0; padding:0; }
		.blk-p-hist li { display:flex; align-items:center; gap:8px; padding:7px 0; border-bottom:1px solid #f0ead9; font-size:.85rem; }
		.blk-p-hist a { font-weight:700; text-decoration:none; } .blk-p-hist span { color:#9a8f7a; } .blk-p-hist em { font-style:normal; margin-left:auto; color:#6b6256; }
		/* Нова Пошта автопідбір */
		.blk-np-wrap { position:relative; }
		.blk-np-dd { position:absolute; left:0; right:0; top:100%; z-index:50; background:#fff; border:1px solid #d8c89e; border-radius:8px; max-height:240px; overflow:auto; list-style:none; margin:2px 0 0; padding:4px 0; display:none; box-shadow:0 10px 28px rgba(0,0,0,.15); }
		.blk-np-dd.show { display:block; }
		.blk-np-dd li { padding:8px 12px; cursor:pointer; font-size:.9rem; font-weight:400; color:#3c434a; }
		.blk-np-dd li:hover { background:#faf3e0; }
		/* флекс/мобільна адаптація */
		@media (max-width:900px) { .blk-p-edit { grid-template-columns:1fr; } .blk-p-side .blk-p-box { position:static; } }
		@media (max-width:700px) {
			.blk-p-grid2 { grid-template-columns:1fr; }
			.blk-p-bar { flex-direction:column; align-items:stretch; } .blk-p-add { text-align:center; }
			.blk-p-filters { flex-direction:column; align-items:stretch; } .blk-p-view { margin:0; }
			.blk-p-irow { display:flex; flex-wrap:wrap; align-items:center; gap:8px 10px; }
			.blk-p-iname { flex:1 1 100%; } .bp-rm { order:2; } .blk-p-iprice { order:3; } .blk-p-iqty { order:4; } .blk-p-iline { order:5; margin-left:auto; min-width:0; }
			.blk-p-additem { flex-direction:column; align-items:stretch; }
			.blk-p-orders { grid-template-columns:1fr; margin:14px; }
			.blk-p-head, .blk-p-bar, .blk-p-edit { margin-left:12px; margin-right:12px; }
		}
	</style>
<?php }
