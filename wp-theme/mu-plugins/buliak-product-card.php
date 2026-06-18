<?php
/* Plugin Name: Буляк Product Card
 * Description: Нова картка товару (дизайн buliak-modern) скрізь: фото·назва·ціна/кг·порція·к-сть·В кошик.
 *              Вигляд їхній — логіка наша (WooCommerce add-to-cart з к-стю). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* орієнтовна вага порції з short_description ("Орієнтовна вага: 1–1,5 кг") */
function blk_product_portion( $product ) {
	$sd = $product->get_short_description();
	if ( ! $sd ) { return ''; }
	$txt = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $sd ) ) );
	if ( preg_match( '/Орієнтовна вага:\s*(.+?)(?:\s*Склад|$)/u', $txt, $m ) ) {
		return trim( $m[1] );
	}
	return '';
}

/* орієнтовна ціна за порцію = ₴/кг × вага порції (діапазон) */
function blk_portion_price_range( $product ) {
	$portion = blk_product_portion( $product );
	if ( ! $portion ) { return ''; }
	$factor = ( mb_stripos( $portion, 'кг' ) !== false ) ? 1 : ( ( mb_stripos( $portion, 'г' ) !== false ) ? 0.001 : 0 );
	if ( ! $factor ) { return ''; }
	if ( ! preg_match_all( '/\d+(?:[.,]\d+)?/u', $portion, $m ) || empty( $m[0] ) ) { return ''; }
	$price = (float) $product->get_price();
	if ( $price <= 0 ) { return ''; }
	$vals = array();
	foreach ( $m[0] as $w ) { $vals[] = floatval( str_replace( ',', '.', $w ) ) * $factor * $price; }
	$min = round( min( $vals ) ); $max = round( max( $vals ) );
	$f = function ( $v ) { return number_format( $v, 0, '', "\u{00A0}" ); };
	return ( $max - $min < 1 ) ? '≈ ' . $f( $max ) . ' ₴' : '≈ ' . $f( $min ) . '–' . $f( $max ) . ' ₴';
}

/* лейбл порції: «≈225 г / порція» (середнє з діапазону) */
function blk_portion_label( $product ) {
	$portion = blk_product_portion( $product );
	if ( ! $portion ) { return ''; }
	$unit = ( mb_stripos( $portion, 'кг' ) !== false ) ? 'кг' : ( ( mb_stripos( $portion, 'г' ) !== false ) ? 'г' : '' );
	if ( ! $unit ) { return ''; }
	if ( ! preg_match_all( '/\d+(?:[.,]\d+)?/u', $portion, $m ) || empty( $m[0] ) ) { return ''; }
	$nums = array();
	foreach ( $m[0] as $x ) { $nums[] = floatval( str_replace( ',', '.', $x ) ); }
	$avg = array_sum( $nums ) / count( $nums );
	$val = ( $unit === 'г' ) ? (string) round( $avg ) : rtrim( rtrim( number_format( $avg, 2, '.', '' ), '0' ), '.' );
	return '≈' . $val . ' ' . $unit . ' / порція';
}

/* 3 колонки (більші картки) */
add_filter( 'loop_shop_columns', function () { return 3; }, 200 );

/* CSS */
add_action( 'wp_head', function () { if ( is_admin() ) return; ?>
<style id="blk-product-card-css">
  /* грід: 3 колонки, більші картки */
  .woocommerce ul.products:not(.blk-carousel-track):not(.buliak-best-grid),
  .woocommerce-page ul.products:not(.blk-carousel-track):not(.buliak-best-grid) {
    display: grid !important; grid-template-columns: repeat(3, 1fr) !important; gap: 26px !important; }
  @media (max-width: 1024px) { .woocommerce ul.products:not(.blk-carousel-track):not(.buliak-best-grid) { grid-template-columns: repeat(2, 1fr) !important; } }
  @media (max-width: 600px) { .woocommerce ul.products:not(.blk-carousel-track):not(.buliak-best-grid) { grid-template-columns: 1fr !important; gap: 18px !important; } }
  /* «з цим купують» (related / upsells) на мобільному → горизонтальний скрол, як бестселери на головній */
  @media (max-width: 768px) {
    .woocommerce .related ul.products:not(.blk-carousel-track):not(.buliak-best-grid),
    .woocommerce .up-sells ul.products:not(.blk-carousel-track):not(.buliak-best-grid),
    .woocommerce .upsells ul.products:not(.blk-carousel-track):not(.buliak-best-grid) {
      display: flex !important; grid-template-columns: none !important; overflow-x: auto !important; overflow-y: hidden !important;
      touch-action: pan-x !important; overscroll-behavior-x: contain; gap: 14px !important;
      scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; scrollbar-width: none; padding-bottom: 8px; margin-top: 16px !important; }
    .woocommerce .related ul.products::-webkit-scrollbar, .woocommerce .up-sells ul.products::-webkit-scrollbar, .woocommerce .upsells ul.products::-webkit-scrollbar { display: none; }
    .woocommerce .related ul.products li.product, .woocommerce .up-sells ul.products li.product, .woocommerce .upsells ul.products li.product {
      flex: 0 0 80% !important; max-width: 80% !important; min-width: 0 !important; scroll-snap-align: start; margin: 0 !important; }
  }
  .buliak-best-grid { grid-template-columns: repeat(3, 1fr) !important; }
  /* карусель: ширші картки під новий дизайн */
  .blk-carousel-track li.product.blk-card { flex: 0 0 300px !important; max-width: 300px !important; }

  /* КАРТКА */
  .woocommerce ul.products li.product.blk-card, ul.products li.product.blk-card {
    --bx-gold:#E0B557; --bx-primary:#B81F33; --bx-text:#F7EFE4; --bx-muted:rgba(247,239,228,.6); --bx-border:rgba(224,181,87,.16);
    position: relative !important; background: rgba(23,20,19,.5) !important; backdrop-filter: blur(14px);
    border: 1px solid var(--bx-border) !important; border-radius: 20px !important; overflow: hidden !important;
    display: flex !important; flex-direction: column !important; height: 100% !important; padding: 0 !important;
    margin: 0 !important; text-align: left !important; list-style: none !important;
    box-shadow: 0 10px 40px rgba(0,0,0,.45); transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease; }
  .blk-card:hover { transform: translateY(-6px); border-color: rgba(224,181,87,.45) !important; box-shadow: 0 16px 46px rgba(224,181,87,.14) !important; }
  .blk-card .blk-hit { position: absolute; top: 14px; left: 14px; z-index: 3; background: var(--bx-primary); color: #fff;
    font-family: 'Unbounded',sans-serif; font-size: .68rem; font-weight: 800; letter-spacing: .04em;
    padding: 6px 12px; border-radius: 99px; border: 1px solid rgba(255,255,255,.15); box-shadow: 0 4px 10px rgba(0,0,0,.3); }
  .blk-card .blk-card-media { display: block; aspect-ratio: 4/3; width: 100%; background: #1a1614; overflow: hidden; }
  .blk-card .blk-card-media img { width: 100% !important; height: 100% !important; object-fit: cover; display: block;
    margin: 0 !important; border-radius: 0 !important; transition: transform .4s cubic-bezier(.4,0,.2,1); }
  .blk-card:hover .blk-card-media img { transform: scale(1.06); }
  /* безшовний плейсхолдер (без рамки картинки) — заповнює медіа кольором картки */
  .blk-card .blk-card-ph { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 100%; background: #1d1917; }
  .blk-card .blk-card-ph-em { width: 72px; height: auto; opacity: .92; }
  .blk-card .blk-card-ph-t { font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: rgba(247,239,228,.36); }
  .blk-card .blk-card-body { padding: 20px 22px 22px; display: flex; flex-direction: column; flex: 1; }
  .blk-card .blk-card-title { font-size: 1.12rem; font-weight: 800; color: var(--bx-text); margin: 0 0 12px; line-height: 1.3; }
  .blk-card .blk-card-title a { color: inherit; text-decoration: none; }
  .blk-card .blk-card-title a:hover { color: var(--bx-gold); }
  .blk-card .blk-card-meta { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
    margin-top: auto; margin-bottom: 16px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.05); }
  .blk-card .blk-card-price { font-family: 'Unbounded',sans-serif; font-size: 1.2rem; font-weight: 800; color: var(--bx-gold) !important; white-space: nowrap; }
  .blk-card .blk-card-price .blk-card-unit { font-family: 'Manrope',sans-serif; font-size: .8rem; font-weight: 500; color: var(--bx-muted) !important; }
  .blk-card .blk-card-price .amount, .blk-card .blk-card-price bdi { color: var(--bx-gold) !important; font-family: 'Unbounded',sans-serif !important; }
  .blk-card .blk-card-portion { font-size: .74rem; font-weight: 600; color: var(--bx-muted); background: rgba(255,255,255,.03);
    border: 1px solid var(--bx-border); padding: 4px 9px; border-radius: 6px; white-space: nowrap; }
  .blk-card .blk-card-pprice { font-size: .9rem; font-weight: 700; color: var(--bx-text); margin: -6px 0 14px; }
  .blk-card .blk-card-pprice span { font-weight: 500; font-size: .8rem; color: var(--bx-muted); }
  .blk-card .blk-card-actions { display: flex; gap: 10px; align-items: center; }
  .blk-card .blk-card-qty { display: flex; align-items: center; border: 1px solid var(--bx-border); border-radius: 99px; background: rgba(255,255,255,.03); overflow: hidden; flex-shrink: 0; }
  .blk-card .blk-card-qty button { width: 34px; height: 38px; background: none; border: 0; cursor: pointer; color: var(--bx-text); font-size: 1.1rem; font-weight: 600; line-height: 1; transition: .15s; padding: 0; }
  .blk-card .blk-card-qty button:hover { background: rgba(255,255,255,.06); color: var(--bx-gold); }
  .blk-card .blk-qv { min-width: 56px; text-align: center; font-size: .88rem; font-weight: 700; color: var(--bx-text); white-space: nowrap; }
  .blk-card .blk-card-add { flex: 1; background: var(--bx-primary); color: #fff; border: 0; border-radius: 99px; cursor: pointer;
    padding: 12px 14px; font-weight: 700; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap;
    transition: background .2s, transform .15s; box-shadow: 0 4px 15px rgba(184,31,51,.25); margin: 0 !important; }
  .blk-card .blk-card-add:hover { background: #9e1728; transform: translateY(-1px); }
  .blk-card .blk-card-add.blk-added { background: var(--bx-gold); color: #000; }
  /* прибрати старий бейдж-вогник (тепер 🔥ХІТ пігулка) */
  .blk-card .blk-best-badge { display: none !important; }
</style>
<?php }, 999 );

/* JS: степер к-сті + В кошик (наш WooCommerce AJAX) */
add_action( 'wp_footer', function () { if ( is_admin() ) return; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.blk-card').forEach(function (card) {
    var qv = card.querySelector('.blk-qv'); if (!qv) return;
    var q = 1, STEP = 0.25, MIN = 0.25;
    function fmt() { return (Math.round(q * 100) / 100) + ' кг'; }
    qv.textContent = fmt();
    var dec = card.querySelector('.blk-qd'), inc = card.querySelector('.blk-qi'), add = card.querySelector('.blk-card-add');
    if (dec) dec.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); if (q > MIN) { q = Math.round((q - STEP) * 100) / 100; qv.textContent = fmt(); } });
    if (inc) inc.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); q = Math.round((q + STEP) * 100) / 100; qv.textContent = fmt(); });
    if (add) add.addEventListener('click', function (e) {
      e.preventDefault(); e.stopPropagation();
      var id = add.dataset.id; if (!id) return; add.disabled = true;
      var body = new URLSearchParams(); body.append('product_id', id); body.append('quantity', q);
      fetch('/?wc-ajax=add_to_cart', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString(), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          add.disabled = false;
          if (res && res.fragments) {
            Object.keys(res.fragments).forEach(function (sel) {
              document.querySelectorAll(sel).forEach(function (el) { var t = document.createElement('div'); t.innerHTML = res.fragments[sel]; if (t.firstElementChild) { el.replaceWith(t.firstElementChild); } });
            });
          }
          if (window.jQuery) { jQuery(document.body).trigger('added_to_cart'); }
          var orig = add.textContent; add.textContent = 'Додано ✓'; add.classList.add('blk-added');
          setTimeout(function () { add.textContent = orig; add.classList.remove('blk-added'); }, 1100);
        })
        .catch(function () { add.disabled = false; });
    });
  });
});
</script>
<?php }, 33 );
