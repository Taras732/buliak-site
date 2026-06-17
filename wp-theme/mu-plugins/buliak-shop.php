<?php
/* Plugin Name: Буляк Shop Layout
 * Description: Грід, клікабельні картки, чистка кнопок, іконка-кошик з лічильником, гумор-заголовки. */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* 404 на старих URL товарів/категорій (приховані / перейменовані slug) -> 301 на магазин */
add_action( 'template_redirect', function () {
	$uri = $_SERVER['REQUEST_URI'] ?? '';
	if ( is_404() && ( strpos( $uri, '/product/' ) !== false || strpos( $uri, '/product-category/' ) !== false ) ) {
		wp_safe_redirect( home_url( '/shop/' ), 301 );
		exit;
	}
}, 1 );

/* 4 колонки + усі товари на одній сторінці (без пагінації) */
add_filter( 'loop_shop_per_page', function () { return 48; }, 99 );
add_filter( 'loop_shop_columns', function () { return 4; }, 99 );

/* Гумор-заголовки на сторінці товару (бренд-voice Буляк) */
add_filter( 'woocommerce_product_related_products_heading', function () { return 'З цим зазвичай беруть 🍻'; } );
add_filter( 'woocommerce_product_upsells_heading', function () { return 'Спробуй ще й це 😋'; } );

/* прибрати таби «Опис / Відгуки» на сторінці товару (опис є у summary справа) */
add_filter( 'woocommerce_product_tabs', '__return_empty_array', 98 );

/* картки бестселерів на головній -> ТІ Ж класи Astra, що в магазині (ідентичний вигляд) */
function buliak_shop_card_classes( $classes ) {
	if ( ! empty( $GLOBALS['buliak_shop_cards'] ) && in_array( 'product', $classes, true ) ) {
		$classes = array_diff( $classes, array( 'ast-article-single' ) );
		foreach ( array( 'ast-grid-common-col', 'ast-full-width', 'ast-article-post', 'remove-featured-img-padding' ) as $c ) {
			if ( ! in_array( $c, $classes, true ) ) { $classes[] = $c; }
		}
	}
	return $classes;
}
add_filter( 'post_class', 'buliak_shop_card_classes', 20 );
add_filter( 'woocommerce_post_class', 'buliak_shop_card_classes', 20 );

/* прибрати хлібні крихти + блок мета (SKU/категорія) на сторінці товару */
add_action( 'template_redirect', function () {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
} );

/* ціна вагового товару — з суфіксом «/ кг», щоб не плутати зі штукою */
add_filter( 'woocommerce_get_price_html', function ( $html, $product ) {
	if ( is_admin() ) { return $html; }
	return $html . ' <span class="blk-perkg">/ кг</span>';
}, 10, 2 );

/* «Бестселер» — бейдж на фото (замість текстового рядка позначки) */
function buliak_is_bestseller( $product ) {
	return $product && has_term( 'bestseller', 'product_tag', $product->get_id() );
}
add_action( 'woocommerce_before_shop_loop_item_title', function () {
	global $product;
	if ( buliak_is_bestseller( $product ) ) { echo '<span class="blk-best-badge" role="img" aria-label="Хіт продажів" title="Хіт продажів">🔥</span>'; }
}, 9 );
add_action( 'woocommerce_product_thumbnails', function () {
	global $product;
	if ( buliak_is_bestseller( $product ) ) { echo '<span class="blk-best-badge blk-best-badge--single" role="img" aria-label="Хіт продажів" title="Хіт продажів">🔥</span>'; }
}, 5 );

/* ---- Помітний нотіс: усе за передзамовленням 1–2 дні + BBQ охолоджена у вакуумі ---- */
add_action( 'woocommerce_before_main_content', function () {
	if ( is_cart() || is_checkout() ) { return; }
	if ( ! ( is_shop() || is_product_category() || is_product() ) ) { return; }
	echo '<div class="blk-notice">'
		. '<span class="blk-notice-row"><b>⏱ Усе за передзамовленням</b> — готуємо 1–2 робочі дні, відправляємо Новою Поштою по Україні.</span>'
		. '<span class="blk-notice-row">🥩 <b>«Готова смажена BBQ»</b> їде охолодженою у вакуумі (не гаряча) — удома достатньо розігріти.</span>'
		. '</div>';
}, 15 );

/* ---- Шорткод сітки бестселерів для головної: 5 товарів + 6-й кахель CTA ---- */
add_shortcode( 'buliak_bestsellers', function () {
	if ( ! function_exists( 'wc_get_products' ) ) { return ''; }
	$ids = wc_get_products( array(
		'status'  => 'publish',
		'limit'   => 20,
		'return'  => 'ids',
		'tag'     => array( 'bestseller' ),
		'orderby' => 'title',
		'order'   => 'ASC',
	) );
	if ( empty( $ids ) ) { return ''; }

	global $post, $product;
	$prev_post = $post;
	$GLOBALS['buliak_shop_cards'] = true;
	ob_start();
	echo '<div class="woocommerce blk-carousel">';
	echo '<div class="blk-carousel-head">'
		. '<button type="button" class="blk-carousel-nav blk-carousel-prev" aria-label="Назад">‹</button>'
		. '<button type="button" class="blk-carousel-nav blk-carousel-next" aria-label="Далі">›</button>'
		. '</div>';
	echo '<ul class="products blk-carousel-track">';
	foreach ( $ids as $id ) {
		$post    = get_post( $id );
		setup_postdata( $post );
		$product = wc_get_product( $id );
		wc_get_template_part( 'content', 'product' );
	}
	echo '</ul>';
	echo '</div>';
	$GLOBALS['buliak_shop_cards'] = false;
	$post = $prev_post;
	wp_reset_postdata();
	return ob_get_clean();
} );

/* ---- Карусель бестселерів (для порожнього кошика) ---- */
function buliak_bestsellers_carousel() {
	if ( ! function_exists( 'wc_get_products' ) ) { return ''; }
	$ids = wc_get_products( array(
		'status' => 'publish', 'limit' => 8, 'return' => 'ids',
		'tag' => array( 'bestseller' ), 'orderby' => 'title', 'order' => 'ASC',
	) );
	if ( empty( $ids ) ) { return ''; }
	global $post, $product; $prev = $post;
	ob_start();
	echo '<div class="blk-carousel">';
	echo '<button type="button" class="blk-carousel-nav blk-carousel-prev" aria-label="Назад">‹</button>';
	echo '<ul class="products blk-carousel-track">';
	foreach ( $ids as $id ) {
		$post = get_post( $id ); setup_postdata( $post );
		$product = wc_get_product( $id );
		wc_get_template_part( 'content', 'product' );
	}
	echo '</ul>';
	echo '<button type="button" class="blk-carousel-nav blk-carousel-next" aria-label="Далі">›</button>';
	echo '</div>';
	$post = $prev; wp_reset_postdata();
	return ob_get_clean();
}

/* ---- Іконка кошика з лічильником у головному меню ---- */
function buliak_cart_icon_html() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? count( WC()->cart->get_cart() ) : 0;
	$cls   = $count > 0 ? 'buliak-cart-count' : 'buliak-cart-count is-empty';
	return '<span class="' . $cls . '">' . esc_html( $count ) . '</span>';
}
add_filter( 'wp_nav_menu_items', function ( $items, $args ) {
	if ( empty( $args->theme_location ) || $args->theme_location !== 'primary' ) { return $items; }
	$url  = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	$svg  = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.3"/><circle cx="18" cy="20" r="1.3"/><path d="M2 3h2.2l2.3 12.2a1.6 1.6 0 0 0 1.6 1.3h8.7a1.6 1.6 0 0 0 1.6-1.2l1.7-7.5H5.6"/></svg>';
	$li   = '<li class="menu-item buliak-cart-item"><a href="' . esc_url( $url ) . '" class="buliak-cart-link" aria-label="Кошик">'
		. $svg . buliak_cart_icon_html() . '</a></li>';
	$contacts = '<li class="menu-item buliak-contacts-item">'
		. '<span class="blk-hc"><span class="blk-hc-l">Роздріб</span><span class="blk-hc-v">073 111 76 70</span></span>'
		. '<a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener" class="blk-hc"><span class="blk-hc-l">Опт / гурт</span><span class="blk-hc-v">Telegram</span></a>'
		. '</li>';
	return $items . $li . $contacts;
}, 10, 2 );

/* Живе оновлення лічильника без перезавантаження */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['span.buliak-cart-count'] = buliak_cart_icon_html();
	return $fragments;
} );

/* Клон іконки кошика у видимий мобільний хедер (поряд із бургером) */
add_action( 'wp_footer', function () {
	if ( is_admin() ) { return; }
	?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (document.querySelector('.blk-mobile-cart')) { return; }
  var src = document.querySelector('.buliak-cart-item .buliak-cart-link');
  var hdr = document.getElementById('ast-mobile-header');
  if (!src || !hdr) { return; }
  var c = src.cloneNode(true);
  c.classList.add('blk-mobile-cart');
  document.body.appendChild(c);
});
</script>
	<?php
} );

/* ---- Мобільне меню (inline, свіже, перебиває Astra) + карусель ---- */
add_action( 'wp_head', function () { ?>
<style id="buliak-mobile-menu-css">
@media (max-width: 921px) {
  /* суцільний темний фон усього off-canvas */
  #ast-mobile-header, #ast-mobile-header .ast-mobile-header-content, #ast-mobile-site-navigation,
  #ast-hf-mobile-menu, .ast-builder-menu-mobile, .main-header-bar-navigation,
  .ast-main-header-bar-alignment, #ast-mobile-site-navigation .main-navigation { background: #0F0D0C !important; }
  /* пункти: кремові, без fade-анімації */
  #ast-hf-mobile-menu .menu-link, #ast-hf-mobile-menu a, #ast-mobile-site-navigation a,
  #ast-hf-mobile-menu .menu-item, #ast-hf-mobile-menu li {
    background: #0F0D0C !important; background-color: #0F0D0C !important;
    color: #f3e9d6 !important; opacity: 1 !important; animation: none !important; transition: none !important; }
  #ast-hf-mobile-menu .menu-link, #ast-hf-mobile-menu a, #ast-mobile-site-navigation a {
    font-family: 'Unbounded', sans-serif; font-weight: 600; text-transform: uppercase;
    font-size: 1.05rem; letter-spacing: .04em; padding: 16px 22px !important; display: block; }
  #ast-hf-mobile-menu .menu-item { border-bottom: 1px solid rgba(224,181,87,.18) !important; opacity: 1 !important; }
  #ast-hf-mobile-menu .menu-item > a:hover { color: #e0b557 !important; }
  /* гамбургер/хрестик кремові */
  .mobile-menu-toggle-icon .ast-mobile-svg, .menu-toggle svg, .ast-close-svg, .ast-close-svg path { fill: #f3e9d6 !important; color: #f3e9d6 !important; }
  /* карусель бестселерів: тільки горизонтальний свайп */
  .blk-carousel-track { touch-action: pan-x !important; overscroll-behavior-x: contain; overflow-y: hidden !important; }
}
@media (max-width: 600px) {
  .hero-inner { text-align: center !important; padding-left: 14px !important; padding-right: 14px !important; }
  .hero h1 { font-size: clamp(1.8rem, 7.6vw, 6rem) !important; white-space: nowrap; letter-spacing: -.02em; }
  .hero .eyebrow { justify-content: center !important; }
  .hero-actions { justify-content: center !important; }
  .hero-sub { margin-left: auto !important; margin-right: auto !important; }
}
@media (max-width: 768px) {
  /* карта контактів: фіксована висота, без пустого простору */
  .map-frame { height: 260px !important; min-height: 0 !important; }
  .map-frame iframe { height: 260px !important; min-height: 0 !important; }
  /* центрування заголовків секцій + контактів на мобільному */
  .contact { text-align: center; }
  .cline { text-align: center; }
}
</style>
<?php }, 999 );

/* ---- CSS ---- */
add_action( 'wp_head', function () { ?>
<style id="buliak-shop-css">
  /* іконка-кошик Astra поверх фото — геть, лишаємо червону кнопку */
  .ast-on-card-button { display: none !important; }
  /* вся картка клікабельна */
  ul.products li.product { cursor: pointer; position: relative; }
  ul.products li.product a.woocommerce-loop-product__link { display: block; }
  /* кошик-іконка */
  .buliak-cart-item .buliak-cart-link { position: relative; display: inline-flex; align-items: center; padding: 6px 8px; }
  .buliak-cart-count {
    position: absolute; top: -2px; right: -4px;
    min-width: 18px; height: 18px; padding: 0 4px;
    background: #b00020; color: #fff; border-radius: 999px;
    font-size: 11px; line-height: 18px; text-align: center; font-weight: 700;
  }
  .buliak-cart-count.is-empty { display: none; }
  /* кошик у мобільному хедері — видимий поряд із бургером */
  .blk-mobile-cart { display: none; }
  @media (max-width: 921px) {
    .blk-mobile-cart { display: flex !important; position: fixed; top: 0; right: 56px; height: 72px; width: 40px;
      align-items: center; justify-content: center; z-index: 99999; color: var(--cream,#f3e9d6) !important; }
    .blk-mobile-cart svg { width: 25px; height: 25px; }
    .blk-mobile-cart .buliak-cart-count { top: 15px; right: 2px; }
  }
  /* роздріб + опт у хедері (справа за кошиком) */
  .buliak-contacts-item { display: inline-flex; align-items: center; gap: 18px; margin-left: 16px; padding: 7px 16px; background: rgba(224,181,87,.07); border: 1px solid rgba(224,181,87,.28); border-radius: 12px; }
  .buliak-contacts-item .blk-hc { cursor: default; }
  .buliak-contacts-item a.blk-hc { cursor: pointer; }
  .buliak-contacts-item .blk-hc { display: inline-flex !important; flex-direction: column; line-height: 1.15; }
  .blk-hc-l { font-size: .6rem; text-transform: uppercase; letter-spacing: .09em; opacity: .55; }
  .blk-hc-v { font-weight: 700; font-size: .9rem; color: #e0b557 !important; white-space: nowrap; }
  @media (max-width: 992px) { .buliak-contacts-item { display: none; } }
  /* прибрати бейдж "Новинка" (на всьому = шум) */
  .buliak-badge.is-new { display: none !important; }
  /* прибрати категорію над назвою (дублюється) + хлібні крихти */
  .ast-woo-product-category, .ast-woocommerce-product-category { display: none !important; }
  .woocommerce-breadcrumb { display: none !important; }
  .blk-perkg { font-size: .62em; opacity: .65; font-weight: 600; }
  /* прибрати мета-рядок (Артикул / Категорія / Позначка) на сторінці товару */
  .product_meta { display: none !important; }
  /* бейдж «Бестселер» на фото */
  ul.products li.product { position: relative; }
  .woocommerce-product-gallery { position: relative; }
  .blk-best-badge {
    position: absolute; top: 10px; left: 10px; z-index: 4;
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; padding: 0;
    background: #b00020; border: 1.5px solid rgba(224,181,87,.85);
    border-radius: 50%; font-size: 1rem; line-height: 1;
    box-shadow: 0 3px 10px rgba(0,0,0,.35); pointer-events: none;
  }
  .blk-best-badge--single { top: 14px; left: 14px; width: 42px; height: 42px; font-size: 1.25rem; }
  /* лого в хедері: біла версія (нормальний видимий розмір), без текстового дубля "БУЛЯК" */
  .custom-logo, .main-header-bar .site-branding img, .site-logo-img img { max-height: 54px !important; width: auto !important; }
  .site-logo-img .custom-logo-link { padding: 0 !important; line-height: 0; }
  .site-header .site-title, .ast-site-identity .site-title, .site-title,
  .ahfb-site-identity .site-title { display: none !important; }
  /* ХЕДЕР: обнуляємо min-height на всіх дітях #masthead (desktop і mobile бар) —
     висоту дає лише лого + невеликий паддінг. Перебиваємо Astra напевно. */
  #masthead * { min-height: 0 !important; padding-top: 0 !important; padding-bottom: 0 !important; }
  #masthead .ast-primary-header-bar, #masthead .main-header-bar { padding-top: 9px !important; padding-bottom: 9px !important; }
  #masthead .ast-builder-grid-row { align-items: center !important; }
  /* прибрати backdrop blur — лаги скролу на мобайлі */
  .site-header, #masthead { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
  /* DESKTOP (≥922px = коли Astra показує горизонтальний хедер): навіг-пункти ТОЧНО по центру
     бару (overlay), кошик+контакти праворуч. Мобільне (≤921) НЕ чіпаємо — off-canvas як є. */
  @media (min-width: 922px) {
    #masthead .ast-primary-header-bar .ast-builder-grid-row { position: relative; }
    /* контейнер навігації -> overlay на весь бар, центрує свою дитину */
    #masthead .main-header-bar-navigation {
      position: absolute !important; left: 0; right: 0; top: 0; bottom: 0;
      margin: 0 !important; display: flex !important; align-items: center; justify-content: center !important;
      pointer-events: none; z-index: 5;
    }
    /* діти НЕ розтягуються (без цього ast-flex-grow-1 зʼїдає justify-center) */
    #masthead .main-header-bar-navigation .site-navigation,
    #masthead .main-header-bar-navigation nav.site-navigation,
    #masthead .main-header-bar-navigation .main-navigation {
      flex: 0 0 auto !important; width: auto !important;
    }
    /* реальний desktop-ul має клас main-header-menu / ast-nav-menu, НЕ "menu" */
    #masthead .main-header-bar-navigation .main-header-menu {
      display: flex !important; justify-content: center; align-items: center;
      margin: 0 !important; width: auto !important; position: static !important;
    }
    #masthead .main-header-bar-navigation .main-header-menu > li { pointer-events: auto; }
    /* кошик + контакти -> у правий край бару (anchor = .main-header-bar-navigation, бо ul static) */
    #masthead .main-header-bar-navigation .main-header-menu > li.buliak-cart-item {
      position: absolute; right: 150px; top: 50%; transform: translateY(-50%); margin: 0 !important;
    }
    #masthead .main-header-bar-navigation .main-header-menu > li.buliak-contacts-item {
      position: absolute; right: 0; top: 50%; transform: translateY(-50%); margin: 0 !important;
    }
  }
  /* іконки соцмереж у контактах */
  .social-row { display: flex; gap: 14px; margin-top: 24px; }
  .social-ic { display: inline-flex; align-items: center; justify-content: center; width: 46px; height: 46px; border-radius: 50%; background: #b00020; color: #fff !important; transition: .2s; }
  .social-ic:hover { background: #8a0019; transform: translateY(-2px); }
  /* хедер/hero нижчі — щоб перший екран влазив у монітор */
  .hero { min-height: 74vh !important; padding-bottom: 4vh !important; }
  /* marquee: безшовний цикл — flex + 2 однакові half-блоки -> translateX(-50%) без розривів.
     Боковий padding span прибрано: інакше на стику виходив зайвий зазор (26+26px) != ритму всередині */
  .marquee { overflow: hidden !important; }
  .mtrack { display: flex !important; width: max-content !important; flex-wrap: nowrap !important;
    animation: scroll 60s linear infinite !important; will-change: transform; }
  .mtrack span { flex: 0 0 auto; padding-left: 0 !important; padding-right: 0 !important; white-space: nowrap; }
  /* сітка бестселерів: 3 / 2 / 1 колонки замість стрічки на всю ширину */
  ul.products.buliak-best-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px; list-style: none; margin: 0 auto; padding: 0; max-width: 1100px;
  }
  ul.products.buliak-best-grid::before,
  ul.products.buliak-best-grid::after { content: none !important; display: none !important; }
  ul.products.buliak-best-grid li.product {
    width: auto !important; max-width: none !important; float: none !important;
    margin: 0 !important; clear: none !important; padding: 0 !important;
  }
  @media (max-width: 880px) { ul.products.buliak-best-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 520px) { ul.products.buliak-best-grid { grid-template-columns: 1fr; } }
  /* картки товару: кнопки «в кошик» на однаковій висоті (вирівнюємо донизу) */
  ul.products li.product { display: flex !important; flex-direction: column; }
  ul.products li.product a.button, ul.products li.product .add_to_cart_button,
  ul.products li.product .added_to_cart, ul.products li.product .button { margin-top: auto !important; }
  ul.products li.product .price { margin-bottom: 12px; }
  /* кнопка "в кошик" у бренд-колір (було синє) */
  .buliak-best-grid .button, .woocommerce a.button.add_to_cart_button {
    background: #b00020 !important; color: #fff !important; border-radius: 999px;
  }
  .buliak-best-grid .button:hover, .woocommerce a.button.add_to_cart_button:hover { background: #8a0019 !important; }
  /* картки: весь вміст по центру + кнопка однакової ширини по центру */
  ul.products li.product { text-align: center; align-items: center; }
  ul.products li.product .woocommerce-loop-product__title,
  ul.products li.product .price,
  ul.products li.product .ast-woo-product-category,
  ul.products li.product .ast-woocommerce-product-category { text-align: center; width: 100%; }
  ul.products li.product a.button.add_to_cart_button,
  ul.products li.product .added_to_cart, ul.products li.product a.button {
    display: flex !important; justify-content: center; text-align: center;
    width: 100%; max-width: 240px;
    margin-left: auto !important; margin-right: auto !important;
  }
  /* поле «кг» + кнопка на картках магазину */
  ul.products li.product .blk-loop-form { display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%; margin-top: auto; }
  ul.products li.product .blk-loop-form .button { margin-top: 0 !important; }
  .blk-loop-qty { display: inline-flex; align-items: center; gap: 6px; }
  .blk-loop-qty .blk-loop-kg { font-size: .8rem; opacity: .75; font-weight: 600; }
  .blk-loop-form .quantity .qty { width: 74px; text-align: center; padding: 6px; }
  /* ВІЗУАЛЬНЕ РОЗДІЛЕННЯ карток — щоб не зливались із фоном (магазин + головна) */
  ul.products li.product:not(.buliak-cta-tile) {
    background: #1d1917 !important; border: 1px solid rgba(224,181,87,.18) !important;
    border-radius: 14px; overflow: hidden; padding-bottom: 16px;
    transition: border-color .15s ease, transform .15s ease;
  }
  ul.products li.product:not(.buliak-cta-tile):hover { border-color: rgba(224,181,87,.42) !important; transform: translateY(-2px); }
  ul.products li.product .astra-shop-summary-wrap { padding: 6px 14px 0; }
  /* рівна висота карток: сітка-grid (рядки тягнуться однаково) + назва фіксованої висоти */
  .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track),
  .woocommerce-page ul.products:not(.buliak-best-grid):not(.blk-carousel-track) {
    display: grid !important; grid-template-columns: repeat(4, 1fr);
    gap: 24px; margin: 0; padding: 0;
  }
  .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track)::before,
  .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track)::after { content: none !important; display: none !important; }
  .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track) li.product {
    width: auto !important; max-width: none !important; float: none !important; margin: 0 !important;
  }
  /* назва завжди займає 2 рядки -> ціна й кнопка на одній висоті в усіх картках */
  ul.products li.product .woocommerce-loop-product__title {
    min-height: 3.6em; display: flex; align-items: center; justify-content: center; text-align: center;
  }
  /* картки в каруселі однакової висоти (тягнуться під найвищу) */
  .blk-carousel-track { align-items: stretch; }
  .blk-carousel-track li.product { height: auto; }
  /* кнопка — до НИЗУ всієї картки: summary-wrap тягнеться, кнопка margin-top:auto */
  ul.products li.product { height: 100%; }
  ul.products li.product .astra-shop-summary-wrap { display: flex !important; flex-direction: column; align-items: center; flex: 1 1 auto; }
  ul.products li.product .blk-loop-form, ul.products li.product .price,
  ul.products li.product .woocommerce-loop-product__title { width: 100%; text-align: center; }
  /* карусель бестселерів (порожній кошик) */
  .blk-carousel { position: relative; max-width: 1100px; margin: 22px auto 4px; }
  .blk-carousel-track { display: flex !important; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory;
    list-style: none; margin: 0; padding: 4px 2px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
  .blk-carousel-track::-webkit-scrollbar { display: none; }
  .blk-carousel-track li.product { flex: 0 0 230px; max-width: 230px; scroll-snap-align: start; margin: 0 !important; }
  .blk-carousel-head { display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 14px; padding: 0 4px; }
  .blk-carousel-nav { width: 44px; height: 44px; border-radius: 12px !important; cursor: pointer;
    background: #1d1917 !important; color: #e0b557 !important; border: 1px solid rgba(224,181,87,.3) !important;
    font-size: 1.6rem; line-height: 1; display: flex !important; align-items: center; justify-content: center;
    padding: 0 !important; min-width: 0 !important; box-shadow: none !important; transition: .15s; }
  .blk-carousel-nav:hover { background: #b00020 !important; color: #fff !important; border-color: #b00020 !important; }
  /* тост «Додано в кошик» */
  .blk-toast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%) translateY(20px); z-index: 99999;
    background: #1d1917; color: #fff; border: 1px solid rgba(224,181,87,.45); border-radius: 999px;
    padding: 13px 24px; font-weight: 700; box-shadow: 0 10px 34px rgba(0,0,0,.45); opacity: 0; transition: .3s; pointer-events: none; }
  .blk-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
  /* верхні WooCommerce-плашки (успіх/інфо) ховаємо — показуємо тостами */
  .woocommerce-message, .woocommerce-info { display: none !important; }
  /* футер: компактніший + кредит в один рядок + scroll-top у бренд-кольорі */
  .buliak-footer { padding: 28px 0 16px !important; }
  .buliak-footer .fcols { gap: 24px !important; }
  .buliak-footer p, .buliak-footer a { line-height: 1.6 !important; }
  .buliak-footer .fb img { max-height: 56px !important; }
  .buliak-footer .copyr { margin-top: 18px !important; padding-top: 14px !important; white-space: nowrap; }
  /* іконки соцмереж у футері (замість текстових Telegram/Instagram/TikTok) */
  .buliak-footer .blk-foot-social { display: flex; gap: 12px; margin-top: 14px; }
  .buliak-footer .blk-foot-social a {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(224,181,87,.10); border: 1px solid rgba(224,181,87,.30);
    color: #e0b557 !important; transition: background .18s ease, transform .18s ease, border-color .18s ease;
  }
  .buliak-footer .blk-foot-social a:hover { background: #b00020; border-color: #b00020; color: #fff !important; transform: translateY(-2px); }
  .buliak-footer .copyr a { display: inline !important; }
  /* кнопку "вгору" прибрано (зайва) */
  #ast-scroll-top, .ast-scroll-top-wrapper, #ast-scroll-top.ast-scroll-to-top-enabled { display: none !important; }
  /* мобільне: кнопка картки вписується в рамку; стрілки каруселі ховаємо (свайп лишається) */
  @media (max-width: 600px) {
    .woocommerce ul.products li.product .button,
    .woocommerce ul.products li.product a.button.add_to_cart_button,
    ul.products li.product .button {
      margin: 12px 12px 0 !important; padding: 12px 6px !important;
      font-size: .62rem !important; letter-spacing: .03em !important;
      width: calc(100% - 24px) !important; box-sizing: border-box !important;
      text-align: center !important; justify-content: center !important; white-space: nowrap !important;
    }
    .blk-carousel-nav, .blk-carousel-head { display: none !important; }
    .blk-carousel-track { padding-bottom: 6px !important; }
  }
  @media (max-width: 600px) { .buliak-footer .copyr { white-space: normal; } }
  ul.products li.product .astra-shop-summary-wrap > .button,
  ul.products li.product .astra-shop-summary-wrap > .added_to_cart { margin-top: auto !important; }
  @media (max-width: 1024px) { .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track) { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 768px)  { .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track) { grid-template-columns: repeat(2, 1fr); gap: 16px; } }
  /* 6-й кахель "Усі товари" на головній */
  .buliak-best-grid li.buliak-cta-tile { list-style: none; }
  .buliak-cta-link {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%; min-height: 220px; padding: 24px; text-align: center;
    background: #b00020; color: #fff !important; border-radius: 10px;
    text-decoration: none; transition: background .15s ease;
  }
  .buliak-cta-link:hover { background: #8a0019; }
  .buliak-cta-big { font-size: 1.35rem; font-weight: 800; line-height: 1.15; }
  .buliak-cta-arrow { font-size: 2rem; margin-top: 6px; }

  /* ============================================================
     МОБІЛЬНА АДАПТАЦІЯ (тільки ≤768px / ≤480px — десктоп не чіпаємо)
     ============================================================ */
  @media (max-width: 768px) {
    /* reveal-анімації миттєві, щоб не смикались на мобайлі */
    .reveal { opacity: 1 !important; transform: none !important; }

    /* HERO: заголовок не вилазить за екран, читабельні відступи */
    .hero { min-height: 64vh !important; padding-left: 18px !important; padding-right: 18px !important; padding-bottom: 6vh !important; }
    .hero h1 { font-size: clamp(3.2rem, 22vw, 7rem) !important; line-height: 0.95 !important; word-break: break-word; overflow-wrap: anywhere; }
    .hero-sub, .hero p { font-size: clamp(1rem, 4.4vw, 1.25rem) !important; line-height: 1.4 !important; }

    /* HERO actions: кнопки на повну ширину, зручні для тапу, не злипаються */
    .hero-actions { display: flex !important; flex-direction: column !important; align-items: stretch !important; gap: 12px !important; width: 100%; max-width: 360px; margin-left: auto; margin-right: auto; }
    .hero-actions a, .hero-actions .button, .hero-actions .btn { display: flex !important; align-items: center; justify-content: center; width: 100% !important; min-height: 50px !important; font-size: 1.05rem !important; }

    /* MARQUEE: шрифт менший, щоб не домінував на маленькому екрані */
    .mtrack { font-size: clamp(1.6rem, 9vw, 3rem) !important; }

    /* СЕКЦІЇ: внутрішні відступи трохи менші на телефоні */
    .why, .about, .contact, .final { padding-left: 18px !important; padding-right: 18px !important; }
    /* заголовки секцій + карусель бестселерів — однаковий лівий відступ 18px (як About), не тиснути до краю */
    .sec-head { padding-left: 18px !important; padding-right: 18px !important; }
    .blk-carousel { padding-left: 18px !important; padding-right: 18px !important; }

    /* КАРТА у контактах: нормальна висота на мобайлі */
    .contact iframe, .contact .map, .contact-map iframe { width: 100% !important; min-height: 240px !important; height: 240px !important; border-radius: 10px; }

    /* СОЦ-іконки: великі тапабельні цілі */
    .social-row { gap: 16px !important; flex-wrap: wrap; justify-content: center; }
    .social-ic { width: 50px !important; height: 50px !important; }

    /* МАГАЗИН + бестселери: кнопка «в кошик» зручна для тапу (≥44px) */
    ul.products li.product a.button,
    ul.products li.product .add_to_cart_button,
    ul.products li.product .button,
    .buliak-best-grid .button {
      min-height: 44px !important; display: flex !important; align-items: center; justify-content: center;
      font-size: 0.95rem !important; padding-top: 10px !important; padding-bottom: 10px !important;
    }
    /* назви товарів не обрізаються */
    ul.products li.product .woocommerce-loop-product__title,
    ul.products li.product h2, ul.products li.product h3 {
      white-space: normal !important; overflow: visible !important; text-overflow: clip !important;
      font-size: 0.98rem !important; line-height: 1.25 !important;
    }

    /* CTA-кахель на повну ширину гарний на мобайлі */
    .buliak-cta-link { min-height: 160px !important; }

    /* інпути ≥16px щоб iOS не зумив */
    input[type="text"], input[type="email"], input[type="tel"], input[type="search"],
    input[type="number"], input[type="password"], textarea, select {
      font-size: 16px !important;
    }
  }

  @media (max-width: 480px) {
    /* МАГАЗИН: 2 колонки на вузькому екрані (компактні картки) */
    .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track),
    .woocommerce-page ul.products:not(.buliak-best-grid):not(.blk-carousel-track) {
      display: grid !important; grid-template-columns: repeat(2, 1fr) !important; gap: 12px !important;
    }
    .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track) li.product,
    .woocommerce-page ul.products:not(.buliak-best-grid):not(.blk-carousel-track) li.product {
      width: auto !important; max-width: none !important; float: none !important; margin: 0 !important; padding: 0 !important;
    }
    .woocommerce ul.products:not(.buliak-best-grid):not(.blk-carousel-track) li.product .button {
      font-size: 0.85rem !important; padding-left: 6px !important; padding-right: 6px !important;
    }
    /* HERO ще трохи менший на дуже вузьких */
    .hero h1 { font-size: clamp(2.8rem, 24vw, 5.5rem) !important; }
    .mtrack { font-size: clamp(1.4rem, 10vw, 2.4rem) !important; }
  }
  /* нотіс «передзамовлення + вакуум» */
  .blk-notice { max-width: 1100px; margin: 0 auto 22px; padding: 13px 18px;
    display: flex; flex-direction: column; gap: 5px;
    background: rgba(224,181,87,.08); border: 1px solid rgba(224,181,87,.30);
    border-radius: 12px; color: #f3e9d6; font-size: .9rem; line-height: 1.4; }
  .blk-notice b { color: #e0b557; }
  @media (max-width: 600px) { .blk-notice { font-size: .8rem; margin-bottom: 16px; padding: 11px 14px; } }
  /* нижній рядок футера: копірайт+Кузня зліва, правові лінки окремо справа */
  .buliak-footer .copyr { display: flex !important; flex-wrap: wrap; align-items: center;
    justify-content: space-between; gap: 8px 24px; white-space: normal !important; }
  .buliak-footer .copyr .copyr-c { opacity: .8; }
  .blk-foot-legal { display: inline-flex; align-items: center; flex: 0 0 auto; gap: 4px;
    font-size: .92em; }
  .blk-foot-legal a { color: #e0b557 !important; text-decoration: none; opacity: .9; white-space: nowrap; }
  .blk-foot-legal a:hover { text-decoration: underline; opacity: 1; }
  .blk-foot-sep { opacity: .4; }
  @media (max-width: 600px) {
    .buliak-footer .copyr { justify-content: center; text-align: center; flex-direction: column-reverse; gap: 10px; }
    .blk-foot-legal { justify-content: center; }
  }
  /* cookie-нотіс */
  .blk-cookie { position: fixed; left: 16px; right: 16px; bottom: 16px; z-index: 99998;
    max-width: 720px; margin: 0 auto; display: flex; align-items: center; gap: 14px;
    background: #1d1917; color: #f3e9d6; border: 1px solid rgba(224,181,87,.35);
    border-radius: 14px; padding: 12px 16px; box-shadow: 0 12px 40px rgba(0,0,0,.5);
    font-size: .86rem; line-height: 1.4; transform: translateY(150%); opacity: 0; transition: transform .35s ease, opacity .35s ease; }
  .blk-cookie.show { transform: translateY(0); opacity: 1; }
  .blk-cookie a { color: #e0b557; text-decoration: underline; }
  .blk-cookie-ok { flex: 0 0 auto; background: #b00020; color: #fff; border: 0; border-radius: 999px;
    padding: 9px 18px; font-weight: 700; cursor: pointer; white-space: nowrap; }
  .blk-cookie-ok:hover { background: #8a0019; }
  @media (max-width: 520px) { .blk-cookie { flex-direction: column; align-items: stretch; text-align: center; gap: 10px; bottom: 10px; } }
  /* === QA-фікси 2026-06-17 === */
  /* clip (не hidden!) — захист від горизонт. скролу БЕЗ поломки position:sticky хедера */
  html, body { overflow-x: clip; }
  /* карусель бестселерів: вирівняти з заголовком секції (був вужчий за картки) */
  .blk-carousel { max-width: none !important; }
  /* навігація: ховер золотий + прибрати пунктирну рамку фокуса (лишаємо доступний focus-visible) */
  #masthead .main-header-menu a, #masthead .ast-nav-menu a, #masthead nav a { outline: none !important; }
  #masthead .main-header-menu a:hover, #masthead .ast-nav-menu a:hover,
  #masthead .main-header-menu .current-menu-item > a, #masthead .ast-nav-menu .current-menu-item > a {
    color: var(--gold, #e0b557) !important; }
  #masthead .main-header-menu a:focus-visible, #masthead .ast-nav-menu a:focus-visible {
    outline: 1px solid rgba(224,181,87,.55) !important; outline-offset: 4px; border-radius: 4px; }
  /* контакти/футер: ховер бренд-колір, НЕ синій */
  .cline .v a, .contact a { transition: color .15s ease, border-color .15s ease; }
  .cline .v a:hover, .contact a:hover, .buliak-footer a:hover {
    color: var(--gold, #e0b557) !important; border-bottom-color: var(--gold, #e0b557) !important; }
</style>
<?php } );

/* ---- Легкий cookie-нотіс (не блокуючий, закривається, localStorage) ---- */
add_action( 'wp_footer', function () {
	if ( is_admin() ) { return; }
	$pp = ( function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url() ) ? get_privacy_policy_url() : home_url( '/privacy-policy/' );
	?>
<div id="blk-cookie" class="blk-cookie" hidden>
  <span>🍪 Ми використовуємо файли cookie для роботи кошика, карти та аналітики (Google Analytics). Деталі — у <a href="<?php echo esc_url( $pp ); ?>">Політиці конфіденційності</a>.</span>
  <button type="button" class="blk-cookie-ok">Зрозуміло</button>
</div>
<script>
(function () {
  try { if (localStorage.getItem('blk_cookie_ok')) { return; } } catch (e) {}
  var el = document.getElementById('blk-cookie'); if (!el) { return; }
  el.hidden = false; requestAnimationFrame(function () { el.classList.add('show'); });
  el.querySelector('.blk-cookie-ok').addEventListener('click', function () {
    el.classList.remove('show');
    setTimeout(function () { el.hidden = true; }, 350);
    try { localStorage.setItem('blk_cookie_ok', '1'); } catch (e) {}
  });
})();
</script>
	<?php
} );

/* ---- JS: клік по всій картці -> сторінка товару; кнопки працюють окремо ---- */
add_action( 'wp_footer', function () { ?>
<script id="buliak-shop-js">
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('ul.products li.product').forEach(function (card) {
    var link = card.querySelector('a.woocommerce-loop-product__link');
    if (!link) return;
    card.addEventListener('click', function (e) {
      if (e.target.closest('a, button, input, label, select, form, .add_to_cart_button, .button, .quantity')) return;
      window.location = link.href;
    });
  });
  document.querySelectorAll('.blk-carousel').forEach(function (c) {
    var t = c.querySelector('.blk-carousel-track'); if (!t) return;
    var step = 246;
    var p = c.querySelector('.blk-carousel-prev'), n = c.querySelector('.blk-carousel-next');
    if (p) p.addEventListener('click', function () { t.scrollBy({ left: -step, behavior: 'smooth' }); });
    if (n) n.addEventListener('click', function () { t.scrollBy({ left: step, behavior: 'smooth' }); });
  });
  /* AJAX-додавання з картки магазину/каруселі (без переходу на товар) + тост */
  function blkToast(msg) {
    var t = document.createElement('div'); t.className = 'blk-toast'; t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(function () { t.classList.add('show'); });
    setTimeout(function () { t.classList.remove('show'); setTimeout(function () { t.remove(); }, 300); }, 2200);
  }
  /* верхні WooCommerce-повідомлення (успіх/інфо) -> тости знизу */
  document.querySelectorAll('.woocommerce-message, .woocommerce-info').forEach(function (n) {
    var clone = n.cloneNode(true);
    clone.querySelectorAll('a, button, .button').forEach(function (a) { a.remove(); });
    var txt = (clone.textContent || '').replace(/\s+/g, ' ').trim();
    if (txt) { blkToast(txt); }
    n.remove();
  });
  document.querySelectorAll('.blk-loop-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = form.querySelector('button[name="add-to-cart"]'); if (!btn) { form.submit(); return; }
      var qi = form.querySelector('input.qty'); var qty = qi ? qi.value : 1;
      var data = new URLSearchParams(); data.append('product_id', btn.value); data.append('quantity', qty);
      btn.disabled = true; btn.classList.add('loading');
      fetch('/?wc-ajax=add_to_cart', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: data.toString(), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false; btn.classList.remove('loading');
          if (res && res.fragments) {
            Object.keys(res.fragments).forEach(function (sel) {
              document.querySelectorAll(sel).forEach(function (el) {
                var tmp = document.createElement('div'); tmp.innerHTML = res.fragments[sel];
                if (tmp.firstElementChild) { el.replaceWith(tmp.firstElementChild); }
              });
            });
          }
          blkToast('Додано в кошик 🛒');
        })
        .catch(function () { btn.disabled = false; btn.classList.remove('loading'); form.submit(); });
    });
  });
  /* AJAX-додавання зі сторінки ОКРЕМОГО товару (form.cart) — без перезавантаження + тост */
  document.querySelectorAll('form.cart:not(.blk-loop-form)').forEach(function (form) {
    var btn = form.querySelector('button[name="add-to-cart"], .single_add_to_cart_button');
    if (!btn) { return; }
    form.addEventListener('submit', function (e) {
      var pid = btn.value;
      if (!pid) { var hid = form.querySelector('[name="add-to-cart"]'); pid = hid ? hid.value : ''; }
      if (!pid) { return; } /* варіативний/без id -> хай іде дефолтом */
      e.preventDefault();
      var qi = form.querySelector('input.qty, input[name="quantity"]'); var qty = qi ? qi.value : 1;
      var data = new URLSearchParams(); data.append('product_id', pid); data.append('quantity', qty);
      btn.disabled = true; btn.classList.add('loading');
      fetch('/?wc-ajax=add_to_cart', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: data.toString(), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          btn.disabled = false; btn.classList.remove('loading');
          if (res && res.error && res.product_url) { window.location = res.product_url; return; }
          if (res && res.fragments) {
            Object.keys(res.fragments).forEach(function (sel) {
              document.querySelectorAll(sel).forEach(function (el) {
                var tmp = document.createElement('div'); tmp.innerHTML = res.fragments[sel];
                if (tmp.firstElementChild) { el.replaceWith(tmp.firstElementChild); }
              });
            });
          }
          blkToast('Додано в кошик 🛒');
        })
        .catch(function () { btn.disabled = false; btn.classList.remove('loading'); form.submit(); });
    });
  });
  /* кошик: авто-оновлення при зміні кількості (без кнопки "Оновити") + тост замість верхнього статусу */
  /* делегування: працює і після перемальовки таблиці кошика (видалення товару) */
  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input || !input.matches || !input.matches('.woocommerce-cart-form input.qty')) { return; }
    var m = (input.name || '').match(/cart\[(.+?)\]/); if (!m) { return; }
    var row = input.closest('tr');
    var data = new URLSearchParams();
    data.append('action', 'blk_update_cart'); data.append('key', m[1]); data.append('qty', input.value);
    fetch('/wp-admin/admin-ajax.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: data.toString(), credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.removed && row) { row.remove(); }
        else if (row && res.line) { var c = row.querySelector('.product-subtotal'); if (c) { c.innerHTML = res.line; } }
        var ot = document.querySelector('.cart_totals .order-total td'); if (ot && res.total) { ot.innerHTML = '<strong>' + res.total + '</strong>'; }
        document.querySelectorAll('span.buliak-cart-count').forEach(function (el) { el.textContent = res.count; el.classList.toggle('is-empty', res.count < 1); });
        blkToast('Кошик оновлено');
        if (res.count < 1) { location.reload(); }
      })
      .catch(function () {});
  });
  /* коли кошик спорожнів (видалили останній товар) — показати бренд-заглушку */
  if (window.jQuery) {
    jQuery(document.body).on('removed_from_cart updated_wc_div wc_cart_emptied updated_cart_totals', function () {
      if (document.body.classList.contains('woocommerce-cart') && !document.querySelector('.cart_item')) {
        location.reload();
      }
    });
    jQuery(document.body).on('checkout_error', function () {
      setTimeout(function () {
        var box = document.querySelector('form.checkout .woocommerce-error');
        if (!box) { return; }
        var msgs = []; box.querySelectorAll('li').forEach(function (li) { msgs.push(li.textContent.trim()); });
        if (!msgs.length) { msgs.push((box.textContent || '').replace(/\s+/g, ' ').trim()); }
        if (msgs[0]) { blkToast(msgs[0]); }
        box.style.display = 'none';
      }, 60);
    });
  }
});
</script>
<?php } );
