<?php
/* Сторінка «Про нас» (slug about) — перенесено з головної. Запуск: wp eval-file - */
if ( ! function_exists( 'wp_insert_post' ) ) { WP_CLI::error( 'WP off' ); }
$hero = 'https://buliak.com/wp-content/themes/buliak-astra/assets/hero_bbq.webp';
$content = '<div class="about">'
	. '<div class="about-media" style="background-image:url(\'' . $hero . '\')"></div>'
	. '<div class="about-text">'
	. '<span class="eyebrow">Хто такий БУЛЯК</span>'
	. '<h2 style="margin-top:16px">Ми — про справжнє м\'ясо</h2>'
	. '<p>БУЛЯК почався з простого: робити м\'ясо так, як для своїх. Коптити самим, готувати на вогні, не економити на якості. Сьогодні нас обирають тисячі — бо смак не обдуриш, а м\'ясо не бреше.</p>'
	. '<p>Працюємо як передзамовлення: оформлюй на сайті, відправляємо <b>Новою Поштою</b> по всій Україні за 1–2 робочі дні. Їде охолодженим — лишається тільки розігріти.</p>'
	. '</div></div>';

$existing = get_page_by_path( 'about' );
$data = array(
	'post_title'   => 'Про нас',
	'post_name'    => 'about',
	'post_status'  => 'publish',
	'post_type'    => 'page',
	'post_content' => $content,
);
if ( $existing ) {
	$data['ID'] = $existing->ID;
	wp_update_post( $data );
	WP_CLI::log( 'updated about ID ' . $existing->ID );
} else {
	$id = wp_insert_post( $data );
	WP_CLI::log( 'created about ID ' . $id );
}
WP_CLI::success( 'Про нас готово' );
