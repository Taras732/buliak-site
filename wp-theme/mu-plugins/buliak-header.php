<?php
/* Plugin Name: Буляк Header (новий преміум-дизайн)
 * Description: Floating-хедер (топбар + скляна пігулка), порт з buliak-modern. Заміняє дефолтний хедер Astra.
 * Навігація: Продукція · Про нас · Контакти(dropdown). Праворуч: Пошук + Кошик(drawer). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* прибрати адмін-бар WP на фронті (чистий вигляд + без 32px зсуву, що ламав fixed-хедер) */
add_filter( 'show_admin_bar', '__return_false' );

/* лічильник кошика (к-сть позицій) */
function blk_hdr_cart_count() {
	return ( function_exists( 'WC' ) && WC()->cart ) ? count( WC()->cart->get_cart() ) : 0;
}
function blk_hdr_cart_badge() {
	$c = blk_hdr_cart_count();
	return '<span class="blk-cart-count"' . ( $c > 0 ? '' : ' style="display:none"' ) . '>' . esc_html( $c ) . '</span>';
}
add_filter( 'woocommerce_add_to_cart_fragments', function ( $f ) {
	$f['span.blk-cart-count'] = blk_hdr_cart_badge();
	return $f;
} );

/* рендер кастомного хедера на самому верху body */
add_action( 'wp_body_open', function () {
	if ( is_admin() ) { return; }
	$logo  = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
	if ( ! $logo ) { $logo = 'https://buliak.com/wp-content/uploads/2026/06/logow.png'; }
	$shop  = esc_url( home_url( '/shop/' ) );
	$cart  = esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ) );
	?>
<div class="blk-hdr-wrap">
  <header class="blk-header" id="blk-masthead"><div class="blk-hdr-container blk-hdr-flex">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="blk-brand"><img src="<?php echo esc_url( $logo ); ?>" alt="БУЛЯК" class="blk-logo-img"></a>

    <nav class="blk-nav" aria-label="Головна навігація"><ul class="blk-nav-list">
      <li><a href="<?php echo $shop; ?>" class="blk-nav-link">Продукція</a></li>
      <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="blk-nav-link">Про нас</a></li>
      <li><details class="blk-contacts">
        <summary class="blk-nav-link blk-contacts-trigger">Контакти</summary>
        <div class="blk-contacts-card"><div class="blk-card-arrow"></div>
          <div class="blk-card-sec"><h5>Роздріб</h5><p class="blk-card-hint">Телефонуйте для замовлення:</p>
            <a href="tel:0731117670" class="blk-card-phone"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> +38 (073) 111 76 70</a>
          </div>
          <div class="blk-card-divider"></div>
          <div class="blk-card-sec"><h5>Опт / Гурт</h5>
            <a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener" class="blk-card-tg"><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M21.94 4.6 18.9 19.2c-.23 1.02-.84 1.27-1.7.79l-4.7-3.46-2.27 2.18c-.25.25-.46.46-.95.46l.34-4.78L18.5 6.3c.38-.34-.08-.53-.6-.19L6.9 13.18l-4.65-1.45c-1.01-.32-1.03-1.01.21-1.5L20.63 3.2c.84-.32 1.58.2 1.31 1.4z"/></svg> Написати в Telegram</a>
          </div>
        </div>
      </details></li>
    </ul></nav>

    <div class="blk-actions">
      <a href="<?php echo $shop; ?>?blk-search=1" class="blk-act-icon blk-search-btn" aria-label="Пошук"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></a>
      <a href="<?php echo $cart; ?>" id="blk-cart-trigger" class="blk-cart-btn" aria-label="Кошик"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg><?php echo blk_hdr_cart_badge(); ?></a>
      <button class="blk-burger" aria-label="Меню" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
  </div></header>

  <div class="blk-mobile-nav" hidden>
    <a href="<?php echo $shop; ?>">Продукція</a>
    <a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">Про нас</a>
    <a href="<?php echo esc_url( home_url( '/#contacts' ) ); ?>">Контакти</a>
    <a href="tel:0731117670" class="blk-mn-phone">📞 +38 (073) 111 76 70</a>
    <a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener" class="blk-mn-tg">Опт / Гурт — Telegram</a>
  </div>
</div>
	<?php
}, 5 );

/* CSS */
add_action( 'wp_head', function () { if ( is_admin() ) return; ?>
<style id="blk-header-css">
  /* приховати дефолтний хедер Astra */
  #masthead.site-header, .site-header, #ast-mobile-header, .ast-mobile-header-wrap, header.site-header { display: none !important; }
  /* прибрати старі кошик-елементи від хедера Astra (дублювали кошик на мобільному — синій) */
  body .blk-mobile-cart, body .buliak-cart-item, body .buliak-contacts-item { display: none !important; }
  /* без синього підсвічування при тапі + бренд-колір виділення тексту */
  html { -webkit-tap-highlight-color: transparent; }
  ::selection { background: rgba(224,181,87,.3); color: #fff; }
  /* === ПРИБРАТИ СИНІЙ ASTRA: посилання → золоті, кнопки → білий текст === */
  a { color: #E0B557; }
  a:hover, a:focus, a:focus-visible { color: #F0D391; }
  .entry-content a, .ast-container a { color: #E0B557; }
  .entry-content a:hover, .ast-container a:hover { color: #F0D391; }
  /* усі кнопки — білий текст (не синій), незалежно від контексту */
  .btn-primary, .button, .wp-block-button__link, #place_order,
  .woocommerce a.button, .woocommerce button.button, .woocommerce a.button.alt, .woocommerce button.button.alt,
  .blk-ab2-btn, .blk-cd-checkout, .blk-ig-btn, .blk-card-add, .blk-card-tg, .blk-search-submit,
  .buliak-cta-link, .blk-cta-link, .add_to_cart_button, .single_add_to_cart_button {
    color: #fff !important;
  }
  .btn-ghost, .btn-ghost:hover { color: #E0B557 !important; }
  /* HOVER усіх кнопок = як у хіро (підсвічування ember + свічення + підйом) */
  .blk-card-add:hover, .blk-cd-checkout:hover:not(.blk-disabled), .blk-ig-btn:hover, .blk-ab2-btn:hover,
  .blk-card-tg:hover, .blk-search-submit:hover, .blk-cart-btn:hover, .buliak-cta-link:hover, .blk-cta-link:hover,
  .woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce a.button.alt:hover {
    background: var(--ember, #ff6a2b) !important; color: #fff !important;
    box-shadow: 0 12px 34px rgba(255,106,43,.42) !important; transform: translateY(-2px) !important; }
  /* ghost-кнопки (контур) → золотий контур+текст на hover (як «Написати в Telegram») */
  .blk-search-close:hover, .blk-sp-close:hover { color: #E0B557 !important; }
  /* СТАНИ — жодного синього: visited-посилання золоті, кнопки-лінки білі, focus-ring золотий */
  a:visited { color: #E0B557; }
  .blk-cd-checkout:visited, a.button:visited, .woocommerce a.button:visited, .blk-card-tg:visited, .blk-ig-btn:visited,
  .blk-ab2-btn:visited, .buliak-cta-link:visited, .blk-cta-link:visited, .checkout-button:visited, .blk-continue-btn:visited { color: #fff !important; }
  .blk-nav-link:visited, .blk-fc-col a:visited, .blk-fc-social a:visited { color: inherit !important; }
  a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible,
  .button:focus-visible, summary:focus-visible, [tabindex]:focus-visible {
    outline: 2px solid #E0B557 !important; outline-offset: 2px; box-shadow: none !important; }
  /* прибрати дефолтний синій фон/тінь у focus/active кнопок */
  .button:focus, .button:active, a.button:focus, a.button:active, #place_order:focus, #place_order:active,
  .checkout-button:focus, .checkout-button:active, .blk-cd-checkout:focus, .blk-cd-checkout:active,
  .blk-card-add:focus, .blk-card-add:active, .blk-cart-btn:focus, .blk-cart-btn:active { color: #fff !important; box-shadow: none; }
  /* активний пункт навігації / поточна сторінка — золотий, не синій */
  .blk-nav-link.current, .blk-nav-link[aria-current], .current-menu-item > a { color: #E0B557 !important; }
  body .blk-hdr-wrap { --bx-primary:#B81F33; --bx-primary-h:#9e1728; --bx-gold:#E0B557; --bx-text:#F7EFE4; --bx-muted:rgba(247,239,228,.65); --bx-border:rgba(224,181,87,.14); }
  /* хедер плаває ПОВЕРХ контенту (картинка hero йде до верху, без темного band) */
  .blk-hdr-wrap { position: fixed; top: 0; left: 0; right: 0; z-index: 9999; width: 100%; font-family: 'Manrope', sans-serif; }
  /* на НЕ-головних сторінках контент не ховається під фіксований хедер */
  body:not(.home):not(.front-page) #page { padding-top: 96px; }
  /* топбар */
  .blk-topbar { background: rgba(15,13,12,.95); border-bottom: 1px solid rgba(255,255,255,.04); padding: 9px 0; font-size: .75rem; color: var(--bx-muted); }
  .blk-hdr-container { width: 100%; max-width: 1240px; margin: 0 auto; padding: 0 26px; box-sizing: border-box; }
  .blk-topbar-flex { display: flex; justify-content: space-between; align-items: center; }
  .blk-topbar-left, .blk-topbar-right { display: flex; align-items: center; gap: 10px; }
  .blk-tb-item { display: inline-flex; align-items: center; gap: 6px; color: var(--bx-muted); text-decoration: none; }
  a.blk-tb-item:hover { color: var(--bx-gold); }
  .blk-tb-sep { color: rgba(224,181,87,.25); }
  /* floating хедер-пігулка */
  .blk-header { width: calc(100% - 40px); max-width: 1200px; margin: 10px auto; height: 74px;
    background: rgba(23,20,19,.55); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--bx-border); border-radius: 16px; display: flex; align-items: center;
    transition: margin .35s ease, height .35s ease, background .35s ease, box-shadow .35s ease; }
  body.blk-scrolled .blk-header { margin: 7px auto; height: 64px; background: rgba(15,13,12,.88); border-color: rgba(224,181,87,.28); box-shadow: 0 10px 35px rgba(0,0,0,.6); }
  .blk-hdr-flex { display: flex; justify-content: space-between; align-items: center; width: 100%; }
  .blk-brand { display: flex; align-items: center; }
  /* !important + специфічність — інакше .woocommerce img{height:auto} роздуває лого на /shop/ */
  .blk-header .blk-logo-img { height: 46px !important; max-height: 46px !important; width: auto !important; display: block; }
  /* навігація */
  .blk-nav-list { display: flex; list-style: none; gap: 40px; align-items: center; margin: 0; padding: 0; }
  .blk-nav-link { font-size: .85rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
    color: var(--bx-text); opacity: .85; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;
    padding: 26px 0; position: relative; text-decoration: none; transition: color .2s, opacity .2s; }
  .blk-nav-link:hover, .blk-nav-link:focus { color: var(--bx-gold); opacity: 1; }
  .blk-nav-link::after { content: ''; position: absolute; bottom: 18px; left: 0; right: 0; height: 2px;
    background: var(--bx-gold); transform: scaleX(0); transition: transform .25s ease; }
  .blk-nav-link:hover::after { transform: scaleX(1); }
  /* дії */
  .blk-actions { display: flex; align-items: center; gap: 22px; }
  .blk-act-icon { color: var(--bx-text); opacity: .8; display: inline-flex; align-items: center; transition: color .2s, transform .2s, opacity .2s; }
  .blk-act-icon:hover { color: var(--bx-gold); opacity: 1; transform: translateY(-1px); }
  .blk-cart-btn { position: relative; width: 42px; height: 42px; border-radius: 50%; background: var(--bx-primary);
    border: 1px solid rgba(255,255,255,.1); color: #fff; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 15px rgba(184,31,51,.3); transition: background .2s, transform .15s, box-shadow .2s; text-decoration: none; }
  .blk-cart-btn:hover { background: var(--bx-primary-h); transform: scale(1.06); box-shadow: 0 6px 20px rgba(184,31,51,.4); }
  .blk-cart-count { position: absolute; top: -3px; right: -3px; background: var(--bx-gold); color: #000;
    font-size: .72rem; font-weight: 800; min-width: 19px; height: 19px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; border: 1px solid var(--bx-primary); }
  /* dropdown контактів */
  .blk-contacts { position: relative; }
  .blk-contacts summary { list-style: none; outline: none; }
  .blk-contacts summary::-webkit-details-marker { display: none; }
  .blk-contacts[open] summary { color: var(--bx-gold); opacity: 1; }
  .blk-contacts-card { position: absolute; top: 100%; right: 50%; transform: translateX(50%); margin-top: 14px;
    width: 280px; background: rgba(23,20,19,.96); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px);
    border: 1.5px solid var(--bx-gold); border-radius: 16px; box-shadow: 0 16px 50px rgba(0,0,0,.75);
    padding: 22px; display: flex; flex-direction: column; gap: 16px; text-align: left;
    animation: blkDrop .25s cubic-bezier(.16,1,.3,1); }
  .blk-card-arrow { position: absolute; top: -9px; left: 50%; transform: translateX(-50%) rotate(45deg);
    width: 15px; height: 15px; background: rgba(23,20,19,.96); border-top: 1.5px solid var(--bx-gold); border-left: 1.5px solid var(--bx-gold); }
  @keyframes blkDrop { from { opacity: 0; transform: translate(50%,12px); } to { opacity: 1; transform: translate(50%,0); } }
  .blk-card-sec h5 { font-family: 'Unbounded',sans-serif; font-size: .78rem; font-weight: 700; text-transform: uppercase;
    color: var(--bx-gold); margin: 0 0 6px; letter-spacing: .05em; }
  .blk-card-hint { font-size: .78rem; color: var(--bx-muted); margin: 0 0 8px; }
  .blk-card-phone { font-size: 1.15rem; font-weight: 800; color: var(--bx-gold); display: flex; align-items: center; gap: 8px; text-decoration: none; }
  .blk-card-phone:hover { color: var(--bx-text); }
  .blk-card-divider { height: 1px; background: var(--bx-border); }
  .blk-card-tg { display: flex; align-items: center; justify-content: center; gap: 8px; background: var(--bx-primary);
    border: 1px solid rgba(255,255,255,.1); border-radius: 10px; padding: 12px; font-size: .85rem; font-weight: 700;
    color: #fff; text-decoration: none; transition: background .2s, transform .15s; }
  .blk-card-tg:hover { background: var(--bx-primary-h); transform: translateY(-1px); }
  /* бургер (мобільний) */
  .blk-burger { display: none; flex-direction: column; gap: 5px; background: none; border: 0; cursor: pointer; padding: 6px; }
  .blk-burger span { width: 24px; height: 2px; background: var(--bx-text); border-radius: 2px; transition: .25s; }
  .blk-burger[aria-expanded="true"] span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
  .blk-burger[aria-expanded="true"] span:nth-child(2) { opacity: 0; }
  .blk-burger[aria-expanded="true"] span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
  .blk-mobile-nav { display: flex; flex-direction: column; gap: 2px; margin: 0 10px; padding: 10px;
    background: rgba(15,13,12,.97); border: 1px solid var(--bx-border); border-radius: 14px; }
  .blk-mobile-nav[hidden] { display: none !important; }
  .blk-mobile-nav a { color: var(--bx-text); text-decoration: none; padding: 14px 16px; font-weight: 700;
    text-transform: uppercase; font-size: .9rem; letter-spacing: .04em; border-bottom: 1px solid rgba(224,181,87,.1); }
  .blk-mobile-nav a:last-child { border-bottom: 0; }
  .blk-mobile-nav a:hover { color: var(--bx-gold); }
  .blk-mobile-nav .blk-mn-phone { color: var(--bx-gold); }
  .blk-mobile-nav .blk-mn-tg { background: var(--bx-primary); color: #fff; border-radius: 10px; text-align: center; margin-top: 6px; }
  /* респонсив */
  @media (max-width: 900px) {
    .blk-topbar { display: none; }
    .blk-nav { display: none; }
    .blk-header { width: calc(100% - 20px); margin: 10px auto; height: 62px; }
    .blk-header .blk-logo-img { height: 40px !important; max-height: 40px !important; }
    .blk-burger { display: flex; }
  }
  @media (min-width: 901px) { .blk-mobile-nav { display: none !important; } }
</style>
<?php }, 4 );

/* JS: scrolled-стан + dropdown click-outside + бургер */
add_action( 'wp_footer', function () { if ( is_admin() ) return; ?>
<script>
(function(){
  function onScroll(){ document.body.classList.toggle('blk-scrolled', (window.scrollY||0) > 8); }
  window.addEventListener('scroll', onScroll, {passive:true}); onScroll();
  document.addEventListener('click', function(e){
    var d = document.querySelector('.blk-contacts');
    if (d && d.open && !d.contains(e.target)) { d.open = false; }
  });
  var burger = document.querySelector('.blk-burger'), mn = document.querySelector('.blk-mobile-nav');
  if (burger && mn) {
    burger.addEventListener('click', function(){
      var open = burger.getAttribute('aria-expanded') === 'true';
      burger.setAttribute('aria-expanded', String(!open));
      mn.hidden = open;
    });
  }
})();
</script>
<?php }, 20 );
