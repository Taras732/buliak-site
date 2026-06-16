<?php
/* Plugin Name: Буляк Shop Nav
 * Description: Вкладки категорій зверху магазину, лічильник товарів донизу, без верхнього заголовка. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---- Вкладки категорій (shop + усі категорії, навіть порожні) ---- */
function buliak_category_tabs() {
	if ( ! function_exists( 'wc_get_page_permalink' ) ) { return; }
	$cats = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'orderby'    => 'name',
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
	) );
	if ( is_wp_error( $cats ) || empty( $cats ) ) { return; }
	$current = is_product_category() ? get_queried_object_id() : 0;
	echo '<nav class="buliak-cat-tabs">';
	echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '"' . ( $current ? '' : ' class="is-active"' ) . '>Всі</a>';
	foreach ( $cats as $c ) {
		$active = ( $current == $c->term_id ) ? ' class="is-active"' : '';
		echo '<a href="' . esc_url( get_term_link( $c ) ) . '"' . $active . '>' . esc_html( $c->name ) . '</a>';
	}
	echo '</nav>';
}
add_action( 'woocommerce_archive_description', 'buliak_category_tabs', 5 );

/* ---- Бренд-заглушка для порожньої категорії («скоро буде») ---- */
add_action( 'init', function () {
	remove_action( 'woocommerce_no_products_found', 'wc_no_products_found' );
}, 99 );
add_action( 'woocommerce_no_products_found', function () {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	echo '<div class="blk-empty-cat">'
		. '<div class="blk-empty-emoji">🔥</div>'
		. '<p class="blk-empty-title">Тут скоро з’являться смаколики</p>'
		. '<p class="blk-empty-sub">Цю категорію ще наповнюємо. Зазирни в інші — там уже багато смачного!</p>'
		. '<p><a class="button blk-empty-btn" href="' . esc_url( $shop ) . '">Дивитись усе меню →</a></p>'
		. '</div>';
} );

/* ---- Плашка «опт / гурт» в кінці магазину ---- */
add_action( 'woocommerce_after_main_content', function () {
	if ( ! ( is_shop() || is_product_category() ) ) { return; }
	echo '<div class="blk-wholesale">'
		. '<span class="blk-wholesale-ic">🤝</span>'
		. '<div><b>Берете оптом?</b><br>Для оптових замовлень телефонуйте <a href="tel:0731117670">073 111 76 70</a> або пишіть у <a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Telegram</a>.</div>'
		. '</div>';
}, 5 );

/* ---- Лічильник кількості -> донизу; сортування зверху прибрати ---- */
add_action( 'template_redirect', function () {
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	add_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 20 );
} );

/* ---- CSS ---- */
add_action( 'wp_head', function () { ?>
<style id="buliak-shop-nav-css">
  /* прибрати верхній заголовок "Магазин" / назву архіву (контекст дають вкладки) */
  .woocommerce-products-header__title, h1.woocommerce-products-header__title { display: none !important; }
  /* вкладки категорій */
  .buliak-cat-tabs { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin: 8px 0 30px; }
  .buliak-cat-tabs a {
    display: inline-flex; align-items: center; padding: 9px 18px; border-radius: 999px;
    font-weight: 700; font-size: .9rem; text-decoration: none; line-height: 1.1;
    color: var(--cream, #f3e9d6); background: rgba(255,255,255,.05);
    border: 1px solid rgba(224,181,87,.25); transition: .15s;
  }
  .buliak-cat-tabs a:hover { background: rgba(224,181,87,.15); }
  .buliak-cat-tabs a.is-active { background: #b00020; color: #fff; border-color: #b00020; }
  .woocommerce-result-count { float: none !important; clear: both; display: block; width: 100%; text-align: center; margin: 26px 0 0; opacity: .7; }
  /* мобайл: вкладки в один рядок з горизонтальним скролом, без переносу */
  @media (max-width: 600px) {
    .buliak-cat-tabs {
      flex-wrap: nowrap; overflow-x: auto; justify-content: flex-start;
      -webkit-overflow-scrolling: touch; padding: 2px 2px 8px; margin: 4px 0 20px;
      scrollbar-width: none;
    }
    .buliak-cat-tabs::-webkit-scrollbar { display: none; }
    .buliak-cat-tabs a { flex: 0 0 auto; padding: 8px 14px; font-size: .85rem; }
  }
  /* бренд-заглушка порожньої категорії */
  .blk-empty-cat { text-align: center; padding: 48px 16px; }
  .blk-empty-cat .blk-empty-emoji { font-size: 2.6rem; line-height: 1; }
  .blk-empty-cat .blk-empty-title { font-family: 'Unbounded', sans-serif; font-size: 1.4rem; font-weight: 800; margin: 12px 0 4px; }
  .blk-empty-cat .blk-empty-sub { opacity: .75; margin-bottom: 18px; }
  .blk-empty-cat .blk-empty-btn { background: #b00020 !important; color: #fff !important; border-radius: 999px; padding: 13px 28px; font-weight: 700; display: inline-flex; }
  /* плашка опт */
  .blk-wholesale { clear: both; display: flex; align-items: center; gap: 14px; max-width: 1100px; margin: 36px auto 0;
    padding: 16px 22px; border: 1px solid rgba(224,181,87,.3); background: rgba(224,181,87,.06);
    border-radius: 14px; font-size: .95rem; line-height: 1.45; }
  .blk-wholesale-ic { font-size: 1.8rem; line-height: 1; }
  .blk-wholesale b { color: #e0b557; }
  .blk-wholesale a { color: #e0b557; font-weight: 700; }
  @media (max-width: 520px) { .blk-wholesale { flex-direction: column; text-align: center; } }
</style>
<?php } );
