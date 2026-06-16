<?php
/**
 * Буляк — UX магазину: показ товарів напряму, грід 4 кол, головна-вітрина.
 * Ідемпотентно. Запуск: wp eval-file - (wordpress:cli).
 */

/* 1. Магазин показує ТОВАРИ, а не підкатегорії */
update_option( 'woocommerce_shop_page_display', '' );          // '' = products
update_option( 'woocommerce_category_archive_display', '' );    // у категорії теж товари
update_option( 'woocommerce_catalog_columns', 4 );
update_option( 'woocommerce_catalog_rows', 6 );
WP_CLI::log( 'Shop display -> товари, 4 колонки' );

/* 2. mu-plugin: 4 колонки + усі товари на одній сторінці (без пагінації) */
if ( ! is_dir( WPMU_PLUGIN_DIR ) ) { @mkdir( WPMU_PLUGIN_DIR, 0755, true ); }
$mu = <<<'PHP'
<?php
/* Plugin Name: Буляк Shop Layout */
add_filter( 'loop_shop_per_page', function () { return 48; }, 99 );
add_filter( 'loop_shop_columns', function () { return 4; }, 99 );
PHP;
file_put_contents( WPMU_PLUGIN_DIR . '/buliak-shop.php', $mu );
WP_CLI::log( 'mu-plugin buliak-shop.php записано' );

/* 3. Головна-вітрина: хіти + кнопка в магазин */
$shop_url = wc_get_page_permalink( 'shop' );
$best     = get_term_by( 'name', 'Бестселер', 'product_tag' );
$best_slug= $best ? $best->slug : '';

$content  = '<!-- wp:html --><div class="blk-hero" style="text-align:center;padding:48px 16px;">';
$content .= '<h1>Буляк</h1>';
$content .= '<p style="font-size:1.15rem;max-width:640px;margin:0 auto 24px;">Копченості, домашні ковбаси та готова BBQ-продукція власного виробництва. Зимна Вода, Львівщина.</p>';
$content .= '<a class="button wp-element-button" href="' . esc_url( $shop_url ) . '">Перейти в магазин</a>';
$content .= '</div><!-- /wp:html -->';
$content .= '<!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">Хіти продажів</h2><!-- /wp:heading -->';
if ( $best_slug ) {
	$content .= '<!-- wp:shortcode -->[products tag="' . $best_slug . '" columns="4" limit="8" orderby="menu_order"]<!-- /wp:shortcode -->';
}
$content .= '<!-- wp:html --><p style="text-align:center;margin-top:24px;"><a class="button wp-element-button" href="' . esc_url( $shop_url ) . '">Усі товари →</a></p><!-- /wp:html -->';

$home = get_page_by_path( 'golovna' );
$home_id = $home ? $home->ID : 0;
$args = array(
	'post_title'   => 'Головна',
	'post_name'    => 'golovna',
	'post_content' => $content,
	'post_status'  => 'publish',
	'post_type'    => 'page',
);
if ( $home_id ) { $args['ID'] = $home_id; $home_id = wp_update_post( $args ); WP_CLI::log( "Головна оновлена #$home_id" ); }
else { $home_id = wp_insert_post( $args ); WP_CLI::log( "Головна створена #$home_id" ); }

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', (int) $home_id );
WP_CLI::log( 'Статична головна встановлена' );

WP_CLI::success( "UX магазину оновлено. Shop: $shop_url" );
