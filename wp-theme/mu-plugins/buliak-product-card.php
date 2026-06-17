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
  @media (max-width: 560px) { .woocommerce ul.products:not(.blk-carousel-track):not(.buliak-best-grid) { grid-template-columns: 1fr !important; gap: 16px !important; } }
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
  .blk-card .blk-card-actions { display: flex; gap: 10px; align-items: center; }
  .blk-card .blk-card-qty { display: flex; align-items: center; border: 1px solid var(--bx-border); border-radius: 99px; background: rgba(255,255,255,.03); overflow: hidden; flex-shrink: 0; }
  .blk-card .blk-card-qty button { width: 34px; height: 38px; background: none; border: 0; cursor: pointer; color: var(--bx-text); font-size: 1.1rem; font-weight: 600; line-height: 1; transition: .15s; padding: 0; }
  .blk-card .blk-card-qty button:hover { background: rgba(255,255,255,.06); color: var(--bx-gold); }
  .blk-card .blk-qv { min-width: 32px; text-align: center; font-size: .9rem; font-weight: 700; color: var(--bx-text); }
  .blk-card .blk-card-add { flex: 1; background: var(--bx-primary); color: #fff; border: 0; border-radius: 99px; cursor: pointer;
    padding: 12px 14px; font-weight: 700; font-size: .8rem; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap;
    transition: background .2s, transform .15s; box-shadow: 0 4px 15px rgba(184,31,51,.25); margin: 0 !important; }
  .blk-card .blk-card-add:hover { background: #9e1728; transform: translateY(-1px); }
  .blk-card .blk-card-add.blk-added { background: var(--bx-gold); color: #000; }
  /* прибрати старий бейдж-вогник (тепер 🔥ХІТ пігулка) */
  .blk-card .blk-best-badge { display: none !important; }
</style>
<?php }, 9 );

/* JS: степер к-сті + В кошик (наш WooCommerce AJAX) */
add_action( 'wp_footer', function () { if ( is_admin() ) return; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.blk-card').forEach(function (card) {
    var qv = card.querySelector('.blk-qv'); if (!qv) return; var q = 1;
    var dec = card.querySelector('.blk-qd'), inc = card.querySelector('.blk-qi'), add = card.querySelector('.blk-card-add');
    if (dec) dec.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); if (q > 1) { q--; qv.textContent = q; } });
    if (inc) inc.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); q++; qv.textContent = q; });
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
