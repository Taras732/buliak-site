<?php
/* Plugin Name: Буляк Cart Drawer
 * Description: Висувний кошик (вигляд з buliak-modern) поверх НАШОЇ логіки WooCommerce.
 *              Drawer замість сторінки: open на кнопку-кошик, ± / видалити / очистити через AJAX. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---- рендер вмісту кошика (items) ---- */
function blk_drawer_items_html() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) { return ''; }
	$cart = WC()->cart->get_cart();
	if ( empty( $cart ) ) {
		return '<div class="blk-cart-empty"><div class="blk-empty-emoji">🥩</div>'
			. '<div class="blk-empty-title">Кошик порожній</div>'
			. '<div class="blk-empty-sub">Оберіть щось смачне в нашому Меню</div></div>';
	}
	$html = '';
	foreach ( $cart as $key => $item ) {
		$p = $item['data']; if ( ! $p ) { continue; }
		$qty   = $item['quantity'];
		$img   = $p->get_image( array( 56, 56 ) );
		$name  = esc_html( $p->get_name() );
		$price = WC()->cart->get_product_price( $p );                       // ціна за од. (з нашим «/ кг»)
		$sub   = WC()->cart->get_product_subtotal( $p, $qty );              // лінія = qty × ціна
		$html .= '<div class="blk-ci" data-key="' . esc_attr( $key ) . '">'
			. '<div class="blk-ci-img">' . $img . '</div>'
			. '<div class="blk-ci-body">'
			. '<div class="blk-ci-title">' . $name . '</div>'
			. '<div class="blk-ci-price">' . $price . '</div>'
			. '<div class="blk-ci-actions">'
			. '<div class="blk-step"><button type="button" class="blk-step-btn blk-dec" aria-label="Менше">−</button>'
			. '<span class="blk-step-val">' . esc_html( $qty ) . ' кг</span>'
			. '<button type="button" class="blk-step-btn blk-inc" aria-label="Більше">+</button></div>'
			. '<div class="blk-ci-sub">' . $sub . '</div>'
			. '</div></div>'
			. '<button type="button" class="blk-ci-rm" aria-label="Видалити"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>'
			. '</div>';
	}
	return $html;
}
function blk_drawer_total_html() {
	return ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_subtotal() : '0 ₴';
}
function blk_drawer_count() {
	return ( function_exists( 'WC' ) && WC()->cart ) ? count( WC()->cart->get_cart() ) : 0;
}

/* ---- AJAX: одна точка для open/refresh/set/remove/clear ---- */
function blk_cart_action() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) { wp_send_json_error(); }
	$op  = isset( $_POST['op'] ) ? sanitize_text_field( wp_unslash( $_POST['op'] ) ) : 'refresh';
	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? floatval( $_POST['qty'] ) : 0;
	if ( $op === 'set' && $key ) {
		if ( $qty <= 0 ) { WC()->cart->remove_cart_item( $key ); }
		else { WC()->cart->set_quantity( $key, $qty, true ); }
	} elseif ( $op === 'remove' && $key ) {
		WC()->cart->remove_cart_item( $key );
	} elseif ( $op === 'clear' ) {
		WC()->cart->empty_cart();
	}
	WC()->cart->calculate_totals();
	wp_send_json_success( array(
		'items' => blk_drawer_items_html(),
		'total' => blk_drawer_total_html(),
		'count' => blk_drawer_count(),
	) );
}
add_action( 'wp_ajax_blk_cart_action', 'blk_cart_action' );
add_action( 'wp_ajax_nopriv_blk_cart_action', 'blk_cart_action' );

/* ---- розмітка drawer (один раз, унизу body) ---- */
add_action( 'wp_footer', function () {
	if ( is_admin() ) { return; }
	$checkout = esc_url( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ) );
	$empty = blk_drawer_count() < 1;
	?>
<div id="blk-cart-overlay" class="blk-cart-overlay" aria-hidden="true">
  <aside class="blk-cart-drawer" role="dialog" aria-label="Кошик">
    <div class="blk-cd-head"><h4>Кошик</h4>
      <button type="button" class="blk-cd-close" aria-label="Закрити"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="blk-cd-items"><?php echo blk_drawer_items_html(); // phpcs:ignore ?></div>
    <div class="blk-cd-foot">
      <div class="blk-cd-total-row"><span>Орієнтовна вартість:</span><span class="blk-cd-total"><?php echo blk_drawer_total_html(); // phpcs:ignore ?></span></div>
      <p class="blk-cd-note">Фактична вага та ціна будуть погоджені з менеджером після зважування</p>
      <a href="<?php echo $checkout; ?>" class="blk-cd-checkout<?php echo $empty ? ' blk-disabled' : ''; ?>">Оформити замовлення</a>
      <button type="button" class="blk-cd-clear"<?php echo $empty ? ' style="display:none"' : ''; ?>>Очистити кошик</button>
    </div>
  </aside>
</div>
	<?php
}, 30 );

/* ---- CSS (вигляд з buliak-modern) ---- */
add_action( 'wp_head', function () { if ( is_admin() ) return; ?>
<style id="blk-cart-drawer-css">
  .blk-cart-overlay { --bx-gold:#E0B557; --bx-primary:#B81F33; --bx-text:#F7EFE4; --bx-muted:rgba(247,239,228,.65); --bx-border:rgba(224,181,87,.14);
    position: fixed; inset: 0; z-index: 100001; background: rgba(15,13,12,.6); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    opacity: 0; visibility: hidden; transition: opacity .3s ease, visibility .3s ease; font-family: 'Manrope', sans-serif; }
  .blk-cart-overlay.open { opacity: 1; visibility: visible; }
  .blk-cart-drawer { position: absolute; top: 0; right: 0; bottom: 0; width: 100%; max-width: 440px;
    background: rgba(13,11,10,.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
    border-left: 1px solid var(--bx-border); display: flex; flex-direction: column;
    transform: translateX(100%); transition: transform .4s cubic-bezier(.16,1,.3,1); }
  .blk-cart-overlay.open .blk-cart-drawer { transform: translateX(0); }
  .blk-cd-head { padding: 22px 24px; border-bottom: 1px solid var(--bx-border); display: flex; justify-content: space-between; align-items: center; }
  .blk-cd-head h4 { font-size: 1.2rem; color: var(--bx-gold); margin: 0; font-family: 'Unbounded',sans-serif; }
  .blk-cd-close { background: none; border: 0; cursor: pointer; color: var(--bx-muted); transition: color .2s; padding: 0; }
  .blk-cd-close:hover { color: var(--bx-text); }
  .blk-cd-items { flex: 1; overflow-y: auto; padding: 22px 24px; display: flex; flex-direction: column; gap: 18px; }
  .blk-ci { display: flex; gap: 14px; align-items: center; padding-bottom: 18px; border-bottom: 1px solid rgba(224,181,87,.07); position: relative; }
  .blk-ci-img img { width: 56px; height: 56px; object-fit: cover; border-radius: 10px; display: block; }
  .blk-ci-body { flex: 1; min-width: 0; }
  .blk-ci-title { font-size: .92rem; font-weight: 700; color: var(--bx-text); margin-bottom: 3px; padding-right: 22px; line-height: 1.3; }
  .blk-ci-price { font-size: .82rem; color: var(--bx-muted); margin-bottom: 9px; }
  .blk-ci-actions { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
  .blk-step { display: inline-flex; align-items: center; border: 1px solid var(--bx-border); border-radius: 99px; background: rgba(255,255,255,.04); overflow: hidden; }
  .blk-step-btn { width: 30px; height: 30px; background: none; border: 0; cursor: pointer; color: var(--bx-text); font-size: 1.05rem; font-weight: 600; display: flex; align-items: center; justify-content: center; transition: .15s; }
  .blk-step-btn:hover { background: rgba(255,255,255,.06); color: var(--bx-gold); }
  .blk-step-val { min-width: 60px; text-align: center; font-size: .82rem; font-weight: 700; color: var(--bx-text); white-space: nowrap; }
  .blk-ci-sub { font-family: 'Unbounded',sans-serif; font-size: .92rem; font-weight: 800; color: var(--bx-gold); white-space: nowrap; }
  .blk-ci-rm { position: absolute; top: 0; right: 0; background: none; border: 0; cursor: pointer; color: rgba(247,239,228,.4); transition: color .2s; padding: 0; }
  .blk-ci-rm:hover { color: var(--bx-primary); }
  .blk-cart-empty { text-align: center; margin: auto 0; padding: 30px 0; }
  .blk-empty-emoji { font-size: 3rem; margin-bottom: 12px; }
  .blk-empty-title { font-size: 1.1rem; font-weight: 700; color: var(--bx-text); margin-bottom: 6px; }
  .blk-empty-sub { font-size: .85rem; color: var(--bx-muted); }
  .blk-cd-foot { padding: 22px 24px; border-top: 1px solid var(--bx-border); background: rgba(15,13,12,.6); }
  .blk-cd-total-row { display: flex; justify-content: space-between; align-items: center; font-family: 'Unbounded',sans-serif; font-size: 1rem; font-weight: 800; color: var(--bx-text); margin-bottom: 8px; }
  .blk-cd-total { color: var(--bx-gold); font-size: 1.2rem; }
  .blk-cd-note { font-size: .75rem; color: var(--bx-muted); line-height: 1.4; margin: 0 0 18px; font-style: italic; }
  .blk-cd-checkout { display: block; width: 100%; text-align: center; background: var(--bx-primary); color: #fff !important; font-weight: 700;
    padding: 15px; border-radius: 12px; text-decoration: none; transition: background .2s, transform .15s; box-shadow: 0 4px 15px rgba(184,31,51,.3); }
  .blk-cd-checkout:hover { background: #9e1728; transform: translateY(-1px); }
  .blk-cd-checkout.blk-disabled { opacity: .4; pointer-events: none; }
  .blk-cd-clear { display: block; margin: 12px auto 0; background: none; border: 0; font-size: .8rem; color: var(--bx-muted); text-decoration: underline; cursor: pointer; }
  .blk-cd-clear:hover { color: var(--bx-primary); }
  .blk-cd-clear.blk-cd-clear-armed { color: var(--bx-primary) !important; font-weight: 800; text-decoration: none !important; letter-spacing: .02em; }
  @media (max-width: 480px) { .blk-cart-drawer { max-width: 100%; } }
</style>
<?php }, 8 );

/* ---- JS: open/close + ± / видалити / очистити (наша логіка) ---- */
add_action( 'wp_footer', function () { if ( is_admin() ) return; ?>
<script>
(function(){
  var overlay = document.getElementById('blk-cart-overlay'); if (!overlay) return;
  var drawer  = overlay.querySelector('.blk-cart-drawer');
  var itemsEl = overlay.querySelector('.blk-cd-items');
  var totalEl = overlay.querySelector('.blk-cd-total');
  var coBtn   = overlay.querySelector('.blk-cd-checkout');
  var clearBtn= overlay.querySelector('.blk-cd-clear');
  var AJAX = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';

  function setBadge(n){ document.querySelectorAll('.blk-cart-count').forEach(function(b){ b.textContent=n; b.style.display = n>0 ? 'flex':'none'; }); }
  function apply(res){
    if(!res||!res.success) return; var d=res.data;
    itemsEl.innerHTML = d.items; totalEl.innerHTML = d.total; setBadge(d.count);
    var empty = d.count < 1;
    coBtn.classList.toggle('blk-disabled', empty);
    clearBtn.style.display = empty ? 'none' : 'block';
  }
  function call(op, key, qty){
    var body = new URLSearchParams(); body.append('action','blk_cart_action'); body.append('op',op);
    if(key) body.append('key',key); if(qty!==undefined) body.append('qty',qty);
    return fetch(AJAX,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString(),credentials:'same-origin'})
      .then(function(r){return r.json();}).then(apply).catch(function(){});
  }
  function open(){ call('refresh'); overlay.classList.add('open'); overlay.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
  function close(){ overlay.classList.remove('open'); overlay.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }

  // тригер — кнопка-кошик у хедері (замість переходу на сторінку)
  document.addEventListener('click', function(e){
    var t = e.target.closest('#blk-cart-trigger'); if(t){ e.preventDefault(); open(); return; }
    if(e.target===overlay){ close(); }
    var c = e.target.closest('.blk-cd-close'); if(c){ close(); }
  });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape' && overlay.classList.contains('open')) close(); });

  // ± / видалити (делегування — items перемальовуються)
  itemsEl.addEventListener('click', function(e){
    var row = e.target.closest('.blk-ci'); if(!row) return; var key = row.getAttribute('data-key');
    var val = row.querySelector('.blk-step-val'); var cur = val ? (parseFloat(val.textContent)||0.25) : 0.25;
    if(e.target.closest('.blk-inc')) call('set', key, Math.round((cur+0.25)*100)/100);
    else if(e.target.closest('.blk-dec')) call('set', key, Math.round((cur-0.25)*100)/100);
    else if(e.target.closest('.blk-ci-rm')) call('remove', key);
  });
  clearBtn.addEventListener('click', function(){
    if(clearBtn.dataset.armed){ call('clear'); clearBtn.removeAttribute('data-armed'); clearBtn.textContent='Очистити кошик'; clearBtn.classList.remove('blk-cd-clear-armed'); return; }
    clearBtn.dataset.armed='1'; clearBtn.textContent='Точно очистити? Натисніть ще раз'; clearBtn.classList.add('blk-cd-clear-armed');
    setTimeout(function(){ if(clearBtn.dataset.armed){ clearBtn.removeAttribute('data-armed'); clearBtn.textContent='Очистити кошик'; clearBtn.classList.remove('blk-cd-clear-armed'); } }, 3000);
  });

  // після AJAX-додавання товару (наш існуючий flow шле fragments) — оновити drawer
  if(window.jQuery){ jQuery(document.body).on('added_to_cart wc_fragments_refreshed', function(){ call('refresh'); }); }
})();
</script>
<?php }, 31 );
