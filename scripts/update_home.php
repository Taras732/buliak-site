<?php
/* Перейменувати slug тегу Бестселер -> bestseller + оновити головну на сітку 5+1 */

$t = get_term_by( 'name', 'Бестселер', 'product_tag' );
if ( $t && $t->slug !== 'bestseller' ) {
	wp_update_term( $t->term_id, 'product_tag', array( 'slug' => 'bestseller' ) );
	WP_CLI::log( 'Slug тегу -> bestseller' );
}

$content  = '<!-- wp:html --><div class="blk-hero" style="text-align:center;padding:48px 16px;">';
$content .= '<h1>Буляк</h1>';
$content .= '<p style="font-size:1.15rem;max-width:640px;margin:0 auto 24px;">Копченості, домашні ковбаси та готова BBQ-продукція власного виробництва. Зимна Вода, Львівщина.</p>';
$content .= '<a class="button wp-element-button" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Перейти в магазин</a>';
$content .= '</div><!-- /wp:html -->';
$content .= '<!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">Наша п’ятірка бестселерів</h2><!-- /wp:heading -->';
$content .= '<!-- wp:shortcode -->[buliak_bestsellers]<!-- /wp:shortcode -->';

$home = get_page_by_path( 'golovna' );
if ( $home ) {
	wp_update_post( array( 'ID' => $home->ID, 'post_content' => $content ) );
	WP_CLI::success( 'Головна оновлена #' . $home->ID );
} else {
	WP_CLI::error( 'golovna не знайдено' );
}
